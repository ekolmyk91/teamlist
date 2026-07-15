<?php

declare(strict_types=1);

namespace App\Services\RandomCoffee;

use App\CoffeeMeeting;
use App\Member;
use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\Services\Telegram\Exceptions\TelegramApiException;
use App\Services\Telegram\Messages\TelegramMessage;
use Illuminate\Support\Facades\Log;

/**
 * Someone who leaves the programme must not stay in meetings that have not
 * happened yet - otherwise their partner shows up to an empty room and is then
 * asked whether the meeting took place. Past meetings are kept: they are
 * history and statistics.
 */
final class ParticipationCanceller
{
    public function __construct(
        private readonly CoffeeBotClientInterface $bot,
    ) {
    }

    /**
     * Drop the employee's upcoming meetings and tell the partners.
     *
     * @return int number of cancelled meetings
     */
    public function cancelUpcomingFor(Member $member): int
    {
        $userId = (int) $member->user_id;

        $meetings = CoffeeMeeting::query()
            ->forUser($userId)
            ->withStatus(CoffeeMeeting::STATUS_SCHEDULED)
            ->where('scheduled_at', '>', now())
            ->with(['userOne.member', 'userTwo.member'])
            ->get();

        foreach ($meetings as $meeting) {
            $partner = (int) $meeting->user_one_id === $userId
                ? $meeting->userTwo?->member
                : $meeting->userOne?->member;

            $this->tellPartner($meeting, $partner, $member);
            $meeting->delete();
        }

        return $meetings->count();
    }

    private function tellPartner(CoffeeMeeting $meeting, ?Member $partner, Member $leaver): void
    {
        if ($partner?->telegram_chat_id === null) {
            return;
        }

        $when = $meeting->scheduled_at->locale('uk')->translatedFormat('l, d.m')
            . ' о ' . $meeting->scheduled_at->format('H:i');

        try {
            $this->bot->send(new TelegramMessage(
                (string) $partner->telegram_chat_id,
                "\u{1F614} Зустріч Random Coffee скасовано.\n"
                . '<b>' . e(trim($leaver->name . ' ' . $leaver->surname)) . '</b> більше не бере участі, '
                . 'тож зустріч ' . e($when) . " не відбудеться.\n\n"
                . 'Наступного циклу я підберу вам іншу пару.',
            ));
        } catch (TelegramApiException $e) {
            Log::warning('Random Coffee: failed to announce a cancelled meeting.', [
                'meeting_id' => $meeting->id,
                'user_id' => $partner->user_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}