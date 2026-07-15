<?php

declare(strict_types=1);

namespace App\Services\RandomCoffee;

use App\CoffeeMeeting;
use App\Member;
use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\Services\Telegram\Exceptions\TelegramApiException;
use App\Services\Telegram\Messages\TelegramMessage;
use Illuminate\Support\Facades\Log;

final class MeetingNotifier
{
    public function __construct(
        private readonly CoffeeBotClientInterface $bot,
    ) {
    }

    /**
     * Send a personal message to both participants; each one sees only their
     * own meeting. Participants without a linked Telegram chat are skipped.
     *
     * @return int number of messages actually sent
     */
    public function notify(CoffeeMeeting $meeting): int
    {
        $meeting->loadMissing(['userOne.member', 'userTwo.member']);

        $sent = 0;
        $sides = [
            [$meeting->userOne?->member, $meeting->userTwo?->member],
            [$meeting->userTwo?->member, $meeting->userOne?->member],
        ];

        foreach ($sides as [$recipient, $partner]) {
            if ($recipient?->telegram_chat_id === null || $partner === null) {
                continue;
            }

            try {
                $this->bot->send(new TelegramMessage(
                    (string) $recipient->telegram_chat_id,
                    $this->buildText($meeting, $partner),
                ));
                $sent++;
            } catch (TelegramApiException $e) {
                Log::warning('Random Coffee: failed to notify participant.', [
                    'meeting_id' => $meeting->id,
                    'user_id' => $recipient->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function buildText(CoffeeMeeting $meeting, Member $partner): string
    {
        $when = $meeting->scheduled_at
            ->locale('uk')
            ->translatedFormat('l, d.m') . ' о ' . $meeting->scheduled_at->format('H:i');

        return "\u{2615} <b>Random Coffee!</b>\n"
            . 'Твоя пара цього циклу — <b>' . e(trim($partner->name . ' ' . $partner->surname)) . "</b>.\n"
            . "\u{1F4C5} " . e($when) . "\n"
            . "\u{1F517} " . e((string) $meeting->meeting_url) . "\n\n"
            . 'Якщо час не підходить — домовтесь напряму та перенесіть зустріч.';
    }
}