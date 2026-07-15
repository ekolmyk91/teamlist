<?php

declare(strict_types=1);

namespace App\Services\RandomCoffee;

use App\CoffeeMeeting;
use App\CoffeeSetting;
use App\Member;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class MeetingGenerator
{
    public function __construct(
        private readonly PairMatcher $matcher,
    ) {
    }

    /**
     * Should the scheduler generate meetings on this date?
     */
    public function shouldRunOn(CarbonImmutable $date): bool
    {
        $settings = CoffeeSetting::current();

        if (! $settings->enabled || $date->dayOfWeek !== $settings->matching_day) {
            return false;
        }

        // Frequency counts from the cycle date itself, so a manually
        // backfilled cycle does not postpone the next scheduled one.
        $lastCycleDate = CoffeeMeeting::query()->max('cycle_date');

        if ($lastCycleDate === null) {
            return true;
        }

        // Parse in the same timezone as $date: a naive parse lands in UTC and
        // shifts the whole-day diff below the frequency threshold.
        $daysSinceLastCycle = CarbonImmutable::parse($lastCycleDate, $date->getTimezone())
            ->startOfDay()
            ->diffInDays($date->startOfDay());

        return $daysSinceLastCycle >= $settings->frequency_weeks * 7;
    }

    public function alreadyGeneratedFor(CarbonImmutable $generationDate): bool
    {
        return CoffeeMeeting::query()
            ->whereDate('cycle_date', $generationDate->toDateString())
            ->exists();
    }

    /**
     * The cycle the given day belongs to: the most recent matching day on or
     * before it. Lets the admin button run the current cycle instead of
     * starting a new one mid-week, which would reset the frequency countdown
     * and make the scheduler skip the next matching day.
     */
    public function currentCycleDate(CarbonImmutable $day): CarbonImmutable
    {
        $matchingDay = (int) CoffeeSetting::current()->matching_day;

        return $day->startOfDay()->subDays(($day->dayOfWeek - $matchingDay + 7) % 7);
    }

    /**
     * Build the meeting plan for a generation date without persisting anything.
     *
     * $notBefore drops meeting days that have already passed: a cycle started
     * late must not schedule meetings in the past.
     *
     * @return Collection<int, array{user_one_id: int, user_two_id: int, scheduled_at: CarbonImmutable, meeting_url: string}>
     */
    public function plan(CarbonImmutable $generationDate, ?CarbonImmutable $notBefore = null): Collection
    {
        $settings = CoffeeSetting::current();
        $participants = $this->participants();

        $recentPairs = $this->recentPairKeys($generationDate, $settings->exclusion_weeks);

        $pairs = $this->matcher->match(
            $participants->keys()->map(fn ($id) => (int) $id)->all(),
            $recentPairs,
        );

        $meetingDays = $this->workingDaysAfter($generationDate, $notBefore);

        if ($meetingDays === []) {
            return new Collection();
        }

        return collect($pairs)->values()->map(function (array $pair, int $index) use ($participants, $settings, $meetingDays, $generationDate) {
            [$userOneId, $userTwoId] = $pair;
            $day = $meetingDays[$index % count($meetingDays)];

            return [
                'user_one_id' => $userOneId,
                'user_two_id' => $userTwoId,
                'cycle_date' => $generationDate->toDateString(),
                'scheduled_at' => $this->resolveMeetingTime(
                    $day,
                    $settings,
                    $participants->get($userOneId),
                    $participants->get($userTwoId),
                ),
                'meeting_url' => $this->buildMeetingUrl($generationDate),
            ];
        });
    }

    /**
     * Generate and persist meetings for the given date.
     *
     * The settings row is locked for the whole transaction, so concurrent
     * generators (scheduler + admin button) serialize and the loser sees the
     * winner's meetings in the duplicate check and returns an empty set.
     *
     * @return Collection<int, CoffeeMeeting>
     */
    public function generate(CarbonImmutable $generationDate, ?CarbonImmutable $notBefore = null): Collection
    {
        CoffeeSetting::current();

        return DB::transaction(function () use ($generationDate, $notBefore) {
            CoffeeSetting::query()->lockForUpdate()->first();

            if ($this->alreadyGeneratedFor($generationDate)) {
                return new Collection();
            }

            return $this->plan($generationDate, $notBefore)
                ->map(fn (array $attributes) => CoffeeMeeting::create($attributes));
        });
    }

    /**
     * Active employees participating in Random Coffee, keyed by user id.
     *
     * @return Collection<int, Member>
     */
    private function participants(): Collection
    {
        return Member::query()
            ->where('random_coffee', true)
            ->whereHas('user', fn ($query) => $query->where('active', true))
            ->get()
            ->keyBy(fn (Member $member) => (int) $member->user_id);
    }

    /**
     * @return array<int, string>
     */
    private function recentPairKeys(CarbonImmutable $generationDate, int $exclusionWeeks): array
    {
        return CoffeeMeeting::query()
            ->where('scheduled_at', '>=', $generationDate->subWeeks($exclusionWeeks)->startOfDay())
            ->get(['user_one_id', 'user_two_id'])
            ->map(fn (CoffeeMeeting $meeting) => PairMatcher::pairKey((int) $meeting->user_one_id, (int) $meeting->user_two_id))
            ->all();
    }

    /**
     * Next working days (Mon-Fri) strictly inside the cycle week: days +1..+6
     * after the generation date, so the next cycle's generation day is free.
     *
     * @return array<int, CarbonImmutable>
     */
    private function workingDaysAfter(CarbonImmutable $generationDate, ?CarbonImmutable $notBefore = null): array
    {
        $days = [];

        for ($offset = 1; $offset <= 6; $offset++) {
            $day = $generationDate->addDays($offset);

            if ($notBefore !== null && $day->lt($notBefore->startOfDay())) {
                continue;
            }

            if ($day->isWeekday()) {
                $days[] = $day;
            }
        }

        return $days;
    }

    /**
     * Meeting time: the default meeting_time clamped into the intersection of
     * both participants' work slots; company work time fills missing slots.
     */
    private function resolveMeetingTime(CarbonImmutable $day, CoffeeSetting $settings, ?Member $memberOne, ?Member $memberTwo): CarbonImmutable
    {
        $from = max(
            $this->slotFrom($memberOne, $settings),
            $this->slotFrom($memberTwo, $settings),
        );
        $to = min(
            $this->slotTo($memberOne, $settings),
            $this->slotTo($memberTwo, $settings),
        );

        $latestStart = $this->subtractMinutes($to, $settings->meeting_duration);
        $time = (string) $settings->meeting_time;

        // "<=" keeps an intersection that fits the meeting exactly valid.
        if ($from <= $latestStart) {
            $time = min(max($time, $from), $latestStart);
        }

        return $day->setTimeFromTimeString($time);
    }

    private function slotFrom(?Member $member, CoffeeSetting $settings): string
    {
        return (string) ($member?->work_time_from ?? $settings->work_time_from);
    }

    private function slotTo(?Member $member, CoffeeSetting $settings): string
    {
        return (string) ($member?->work_time_to ?? $settings->work_time_to);
    }

    private function subtractMinutes(string $time, int $minutes): string
    {
        return CarbonImmutable::parse($time)->subMinutes($minutes)->format('H:i:s');
    }

    public function buildMeetingUrl(CarbonImmutable $generationDate): string
    {
        return sprintf(
            '%s/w4p-coffee-%s-%s',
            rtrim((string) config('coffee.jitsi_base_url'), '/'),
            $generationDate->format('Ymd'),
            Str::lower(Str::random(8)),
        );
    }
}