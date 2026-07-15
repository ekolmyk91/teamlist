<?php

declare(strict_types=1);

namespace App\Services\RandomCoffee;

use App\CoffeeMeeting;
use App\Member;
use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\Services\Telegram\Exceptions\TelegramApiException;
use App\Services\Telegram\Messages\TelegramMessage;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * The morning after a meeting, asks each participant whether it took place.
 * The question carries two signed links (yes / no) that hit a public
 * endpoint and store the answer — no login required.
 */
final class ConfirmationSender
{
    private const LINK_TTL_DAYS = 7;

    public function __construct(
        private readonly CoffeeBotClientInterface $bot,
    ) {
    }

    /**
     * Idempotent: each participant is asked at most once (tracked via the
     * user_*_asked_at columns), so retries and re-runs do not spam.
     *
     * @return int number of questions sent
     */
    public function sendFor(CarbonImmutable $meetingDay): int
    {
        $meetings = CoffeeMeeting::query()
            ->whereBetween('scheduled_at', [$meetingDay->startOfDay(), $meetingDay->endOfDay()])
            ->with(['userOne.member', 'userTwo.member'])
            ->get();

        $sent = 0;

        foreach ($meetings as $meeting) {
            $sides = [
                ['user' => $meeting->userOne, 'answer' => $meeting->user_one_attended, 'askedColumn' => 'user_one_asked_at'],
                ['user' => $meeting->userTwo, 'answer' => $meeting->user_two_attended, 'askedColumn' => 'user_two_asked_at'],
            ];

            foreach ($sides as $side) {
                if ($side['user'] === null || $side['answer'] !== null || $meeting->{$side['askedColumn']} !== null) {
                    continue;
                }

                // Atomically reserve the participant: of two concurrent runs
                // only one gets affected-rows=1 and actually sends.
                $reserved = CoffeeMeeting::query()
                    ->whereKey($meeting->id)
                    ->whereNull($side['askedColumn'])
                    ->update([$side['askedColumn'] => now()]);

                if ($reserved === 0) {
                    continue;
                }

                if ($this->ask($meeting, $side['user'])) {
                    $sent++;
                } else {
                    // Release the reservation so a later run can retry.
                    CoffeeMeeting::query()
                        ->whereKey($meeting->id)
                        ->update([$side['askedColumn'] => null]);
                }
            }
        }

        return $sent;
    }

    private function ask(CoffeeMeeting $meeting, User $user): bool
    {
        $member = $user->member;

        if (! $member instanceof Member || $member->telegram_chat_id === null) {
            return false;
        }

        $partner = (int) $meeting->user_one_id === (int) $user->id
            ? $meeting->userTwo?->member
            : $meeting->userOne?->member;

        try {
            $this->bot->send(new TelegramMessage(
                (string) $member->telegram_chat_id,
                $this->buildText($meeting, (int) $user->id, $partner),
            ));

            return true;
        } catch (TelegramApiException $e) {
            Log::warning('Random Coffee: failed to send confirmation question.', [
                'meeting_id' => $meeting->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function buildText(CoffeeMeeting $meeting, int $userId, ?Member $partner): string
    {
        $expiresAt = now()->addDays(self::LINK_TTL_DAYS);

        $yes = URL::temporarySignedRoute('coffee.confirm', $expiresAt, [
            'meeting' => $meeting->id,
            'user' => $userId,
            'answer' => 'yes',
        ]);
        $no = URL::temporarySignedRoute('coffee.confirm', $expiresAt, [
            'meeting' => $meeting->id,
            'user' => $userId,
            'answer' => 'no',
        ]);

        // Details matter: by the next morning a participant may have had more
        // than one meeting, and the question has to say which one it is about.
        $when = $meeting->scheduled_at
            ->locale('uk')
            ->translatedFormat('l, d.m') . ' о ' . $meeting->scheduled_at->format('H:i');

        return "Чи відбулася ваша Random Coffee зустріч? \u{2615}\n\n"
            . "\u{1F465} Пара: <b>"
            . e($partner !== null ? trim($partner->name . ' ' . $partner->surname) : 'колега') . "</b>\n"
            . "\u{1F4C5} " . e($when) . "\n"
            . "\u{1F517} " . e((string) $meeting->meeting_url) . "\n\n"
            . '<a href="' . e($yes) . "\">\u{2705} Так, відбулася</a>\n"
            . '<a href="' . e($no) . "\">\u{274C} Ні, не відбулася</a>";
    }
}