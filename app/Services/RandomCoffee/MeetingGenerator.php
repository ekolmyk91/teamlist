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
        return $this->planFor($this->participants(), $generationDate, $notBefore);
    }

    /**
     * Plan meetings for a given set of participants.
     *
     * @param  Collection<int, Member>  $participants
     * @return Collection<int, array<string, mixed>>
     */
    private function planFor(Collection $participants, CarbonImmutable $generationDate, ?CarbonImmutable $notBefore = null): Collection
    {
        $settings = CoffeeSetting::current();

        $recentPairs = $this->recentPairKeys($generationDate, $settings->exclusion_weeks);

        $pairs = $this->matcher->match(
            $participants->keys()->map(fn ($id) => (int) $id)->all(),
            $recentPairs,
        );

        $meetingDays = $this->workingDaysAfter($generationDate, $settings->frequency_weeks, $notBefore);

        if ($meetingDays === []) {
            return new Collection();
        }

        $pairCount = count($pairs);

        return collect($pairs)->values()->map(function (array $pair, int $index) use ($participants, $settings, $meetingDays, $generationDate, $pairCount) {
            [$userOneId, $userTwoId] = $pair;
            // Spread the pairs across the whole window instead of filling it
            // from the front: with a 4-week frequency the last pairs would
            // otherwise still meet in week one and three weeks stay empty.
            $day = $meetingDays[intdiv($index * count($meetingDays), max(1, $pairCount))];

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
     * Pair up the participants the current cycle left without a meeting.
     *
     * Needed because the cycle is generated once and then frozen: somebody who
     * joins the programme mid-cycle - or whose partner left and took the
     * meeting with them - would otherwise wait for the next cycle, which with a
     * 4-week frequency is a month away.
     *
     * New meetings keep the running cycle's date, so max(cycle_date) stays on
     * the matching-day grid and the frequency countdown does not shift. (That
     * shift is exactly what made the first version of this button be removed,
     * before currentCycleDate() existed.)
     *
     * @return Collection<int, CoffeeMeeting>
     */
    public function topUp(CarbonImmutable $cycleDate, ?CarbonImmutable $notBefore = null): Collection
    {
        CoffeeSetting::current();

        return DB::transaction(function () use ($cycleDate, $notBefore) {
            $settings = CoffeeSetting::query()->lockForUpdate()->first();

            $busy = $this->busyUserIds($cycleDate, (int) $settings->frequency_weeks);

            $waiting = $this->participants()
                ->reject(fn (Member $member) => in_array((int) $member->user_id, $busy, true));

            if ($waiting->count() < 2) {
                return new Collection();
            }

            return $this->planFor($waiting, $cycleDate, $notBefore)
                ->map(fn (array $attributes) => CoffeeMeeting::create($attributes));
        });
    }

    /**
     * Everyone who already has a meeting inside the running cycle - generated
     * or added by hand, so a manual meeting is not doubled by a top-up.
     *
     * @return array<int, int>
     */
    private function busyUserIds(CarbonImmutable $cycleDate, int $frequencyWeeks): array
    {
        $windowEnd = $cycleDate->addDays(max(1, $frequencyWeeks) * 7);

        $meetings = CoffeeMeeting::query()
            ->where('scheduled_at', '>=', $cycleDate->startOfDay())
            ->where('scheduled_at', '<', $windowEnd->startOfDay())
            ->get(['user_one_id', 'user_two_id']);

        return $meetings
            ->flatMap(fn (CoffeeMeeting $meeting) => [(int) $meeting->user_one_id, (int) $meeting->user_two_id])
            ->unique()
            ->values()
            ->all();
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
     * Working days (Mon-Fri) strictly inside the cycle: days +1 up to the day
     * before the next cycle's generation day, so that day itself stays free.
     *
     * The window follows frequency_weeks - a 4-week cycle spreads its meetings
     * over four weeks instead of cramming them into the first one.
     *
     * @return array<int, CarbonImmutable>
     */
    private function workingDaysAfter(CarbonImmutable $generationDate, int $frequencyWeeks, ?CarbonImmutable $notBefore = null): array
    {
        $days = [];
        $lastOffset = max(1, $frequencyWeeks) * 7 - 1;

        for ($offset = 1; $offset <= $lastOffset; $offset++) {
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