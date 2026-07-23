<?php

declare(strict_types=1);

namespace App\Services\RandomCoffee\Contracts;

use App\Services\Telegram\Messages\TelegramMessage;

interface CoffeeBotClientInterface
{
    /**
     * Send a message from the Random Coffee bot and return the message_id.
     *
     * @throws \App\Services\Telegram\Exceptions\TelegramApiException
     */
    public function send(TelegramMessage $message): int;

    /**
     * Fetch pending updates (long-poll disabled) starting after the offset.
     *
     * @return array<int, array<string, mixed>> raw Telegram update objects
     *
     * @throws \App\Services\Telegram\Exceptions\TelegramApiException
     */
    public function getUpdates(int $offset, int $limit = 50): array;

    /**
     * Register a webhook URL. Telegram then pushes updates there and disables
     * getUpdates. The secret is echoed back in the
     * X-Telegram-Bot-Api-Secret-Token header on each delivery.
     *
     * @throws \App\Services\Telegram\Exceptions\TelegramApiException
     */
    public function setWebhook(string $url, string $secretToken, bool $dropPendingUpdates = false): void;

    /**
     * Remove the webhook, switching the bot back to getUpdates polling.
     *
     * @throws \App\Services\Telegram\Exceptions\TelegramApiException
     */
    public function deleteWebhook(bool $dropPendingUpdates = false): void;

    /**
     * Current webhook status (url, pending_update_count, last_error_message…).
     *
     * @return array<string, mixed>
     *
     * @throws \App\Services\Telegram\Exceptions\TelegramApiException
     */
    public function getWebhookInfo(): array;
}