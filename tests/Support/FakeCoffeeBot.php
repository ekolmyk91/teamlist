<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\Services\Telegram\Exceptions\TelegramApiException;
use App\Services\Telegram\Messages\TelegramMessage;

final class FakeCoffeeBot implements CoffeeBotClientInterface
{
    /** @var array<int, TelegramMessage> */
    public array $sent = [];

    /** @var array<int, array<string, mixed>> */
    public array $updates = [];

    /** @var array<int, array<string, mixed>> */
    public array $webhookCalls = [];

    /** @var array<string, mixed>|null */
    public ?array $webhookInfo = null;

    public bool $throwOnSend = false;

    public function send(TelegramMessage $message): int
    {
        if ($this->throwOnSend) {
            throw new TelegramApiException('Simulated send failure.');
        }

        $this->sent[] = $message;

        return count($this->sent);
    }

    public function getUpdates(int $offset, int $limit = 50): array
    {
        return $this->updates;
    }

    public function setWebhook(string $url, string $secretToken, bool $dropPendingUpdates = false): void
    {
        $this->webhookCalls[] = [
            'action' => 'set',
            'url' => $url,
            'secret_token' => $secretToken,
            'drop_pending_updates' => $dropPendingUpdates,
        ];
    }

    public function deleteWebhook(bool $dropPendingUpdates = false): void
    {
        $this->webhookCalls[] = [
            'action' => 'delete',
            'drop_pending_updates' => $dropPendingUpdates,
        ];
    }

    public function getWebhookInfo(): array
    {
        return $this->webhookInfo ?? ['url' => ''];
    }
}