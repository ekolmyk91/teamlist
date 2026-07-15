<?php

declare(strict_types=1);

namespace App\Services\RandomCoffee;

use App\Member;
use App\Services\Telegram\Messages\TelegramMessage;

/**
 * Stateless handler for incoming Random Coffee bot updates: one shared bot
 * link for the whole team, employees identify themselves by sending their
 * work email, which is matched against members.email to store the chat id.
 */
final class TelegramLinkHandler
{
    public function __construct(
        private readonly ParticipationCanceller $canceller,
    ) {
    }

    /**
     * @param array<string, mixed> $update raw Telegram update
     */
    public function handle(array $update): ?TelegramMessage
    {
        $message = $update['message'] ?? null;

        if (! is_array($message)) {
            return null;
        }

        $chat = $message['chat'] ?? [];
        $chatId = isset($chat['id']) ? (string) $chat['id'] : '';
        $chatType = (string) ($chat['type'] ?? '');
        $text = trim((string) ($message['text'] ?? ''));

        if ($chatId === '' || $chatType !== 'private' || $text === '') {
            return null;
        }

        if (str_starts_with($text, '/stop')) {
            return $this->leave($chatId);
        }

        if (filter_var($text, FILTER_VALIDATE_EMAIL) !== false) {
            return $this->linkByEmail($chatId, $text);
        }

        // Everything else - /start, /help, small talk - depends on whether this
        // chat is already connected: asking a linked employee for their email
        // again is just confusing.
        $linked = Member::query()->where('telegram_chat_id', $chatId)->first();

        if ($linked !== null) {
            return new TelegramMessage($chatId, $this->connectedText($linked));
        }

        if (str_starts_with($text, '/start')) {
            return new TelegramMessage($chatId, $this->welcomeText());
        }

        return new TelegramMessage(
            $chatId,
            "Надішліть, будь ласка, вашу робочу email-адресу, щоб підключитися до Random Coffee \u{2615}",
        );
    }

    private function linkByEmail(string $chatId, string $email): TelegramMessage
    {
        $member = Member::query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])
            ->first();

        if ($member === null) {
            return new TelegramMessage(
                $chatId,
                'На жаль, співробітника з email <b>' . e($email) . '</b> не знайдено. '
                . 'Перевірте адресу або зверніться до адміністратора.',
            );
        }

        // Linking is by email alone, so a colleague's address would silently
        // hijack their notifications. Re-linking an already connected employee
        // is refused instead: the takeover attempt surfaces to the admin.
        if ($member->telegram_chat_id !== null && (string) $member->telegram_chat_id !== $chatId) {
            return new TelegramMessage(
                $chatId,
                'Співробітник з email <b>' . e($email) . '</b> вже підключений до Random Coffee '
                . 'з іншого Telegram-акаунта. Якщо це ви змінили акаунт — зверніться до адміністратора, '
                . 'він відвʼяже старий.',
            );
        }

        // Linking only connects the chat: taking part is the admin's checkbox,
        // which the bot may switch off (/stop) but never on.
        $member->telegram_chat_id = $chatId;
        $member->save();

        if (! $member->random_coffee) {
            return new TelegramMessage(
                $chatId,
                'Telegram підключено, але участь у Random Coffee для вас вимкнена. '
                . 'Щоб повернутися до програми, зверніться до адміністратора.',
            );
        }

        return new TelegramMessage(
            $chatId,
            "Готово, " . e((string) $member->name) . "! \u{1F389}\n"
            . "Тепер я надсилатиму вам ваші пари Random Coffee для зустрічей.\n\n"
            . 'Не хочете брати участь — надішліть /stop.',
        );
    }

    private function leave(string $chatId): TelegramMessage
    {
        $member = Member::query()
            ->where('telegram_chat_id', $chatId)
            ->first();

        if ($member === null) {
            return new TelegramMessage(
                $chatId,
                'Ви й так не підключені до Random Coffee. '
                . 'Щоб приєднатися, надішліть вашу робочу email-адресу.',
            );
        }

        $member->telegram_chat_id = null;
        $member->random_coffee = false;
        $member->save();

        // Upcoming meetings would otherwise leave partners waiting in an empty
        // room; they are dropped and the partners are told.
        $cancelled = $this->canceller->cancelUpcomingFor($member);

        return new TelegramMessage(
            $chatId,
            "Ви вийшли з Random Coffee — більше не братимете участі в парах \u{1F44B}\n"
            . ($cancelled > 0 ? "Заплановані зустрічі скасовано, партнерів попереджено.\n" : '')
            . 'Захочете повернутися — зверніться до адміністратора.',
        );
    }

    private function connectedText(Member $member): string
    {
        $status = $member->random_coffee
            ? "Ви берете участь — я надішлю вашу пару, щойно буде новий цикл \u{2615}"
            : 'Участь у програмі для вас вимкнена. Щоб повернутися, зверніться до адміністратора.';

        return 'Вітаю, ' . e((string) $member->name) . "! Ваш Telegram уже підключено.\n"
            . $status . "\n\n"
            . 'Команди: /stop — вийти з Random Coffee.';
    }

    private function welcomeText(): string
    {
        return "Привіт! Я бот Random Coffee \u{2615}\n"
            . "Я знайомлю колег: раз на тиждень підбираю випадкові пари для короткої неформальної зустрічі.\n\n"
            . "Щоб отримувати свої пари, надішліть мені вашу робочу email-адресу.\n"
            . 'Не хочете брати участь — надішліть /stop.';
    }
}