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
}