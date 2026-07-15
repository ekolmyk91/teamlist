<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\Services\Telegram\Messages\TelegramMessage;

final class FakeCoffeeBot implements CoffeeBotClientInterface
{
    /** @var array<int, TelegramMessage> */
    public array $sent = [];

    /** @var array<int, array<string, mixed>> */
    public array $updates = [];

    public function send(TelegramMessage $message): int
    {
        $this->sent[] = $message;

        return count($this->sent);
    }

    public function getUpdates(int $offset, int $limit = 50): array
    {
        return $this->updates;
    }
}