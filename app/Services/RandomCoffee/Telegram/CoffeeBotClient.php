<?php

declare(strict_types=1);

namespace App\Services\RandomCoffee\Telegram;

use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\Services\Telegram\Exceptions\TelegramApiException;
use App\Services\Telegram\Messages\TelegramMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;

final class CoffeeBotClient implements CoffeeBotClientInterface
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $apiUrl,
        private readonly string $botToken,
        private readonly int $timeout = 10,
    ) {
    }

    public function send(TelegramMessage $message): int
    {
        $body = $this->call('sendMessage', $message->toPayload());

        $messageId = $body['result']['message_id'] ?? null;

        return is_int($messageId) ? $messageId : 0;
    }

    public function getUpdates(int $offset, int $limit = 50): array
    {
        $body = $this->call('getUpdates', [
            'offset' => $offset,
            'limit' => $limit,
            'timeout' => 0,
        ]);

        $updates = $body['result'] ?? [];

        return is_array($updates) ? $updates : [];
    }

    public function setWebhook(string $url, string $secretToken, bool $dropPendingUpdates = false): void
    {
        $this->call('setWebhook', [
            'url' => $url,
            'secret_token' => $secretToken,
            'allowed_updates' => ['message'],
            'drop_pending_updates' => $dropPendingUpdates,
        ]);
    }

    public function deleteWebhook(bool $dropPendingUpdates = false): void
    {
        $this->call('deleteWebhook', [
            'drop_pending_updates' => $dropPendingUpdates,
        ]);
    }

    public function getWebhookInfo(): array
    {
        $body = $this->call('getWebhookInfo', []);

        $result = $body['result'] ?? [];

        return is_array($result) ? $result : [];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function call(string $method, array $payload): array
    {
        if ($this->botToken === '') {
            throw new TelegramApiException('Random Coffee bot token is not configured.');
        }

        $endpoint = sprintf('%s/bot%s/%s', rtrim($this->apiUrl, '/'), $this->botToken, $method);

        try {
            $response = $this->http
                ->asJson()
                ->acceptJson()
                ->timeout($this->timeout)
                ->retry(3, 200, throw: false)
                ->post($endpoint, $payload);
        } catch (ConnectionException $e) {
            throw new TelegramApiException('Telegram API is unreachable.', previous: $e);
        }

        $body = $response->json();

        if (! $response->successful() || ! is_array($body) || ($body['ok'] ?? false) !== true) {
            throw TelegramApiException::fromResponse(
                $response->status(),
                is_array($body) ? ($body['description'] ?? null) : null,
                is_array($body) ? ($body['error_code'] ?? null) : null,
            );
        }

        return $body;
    }
}