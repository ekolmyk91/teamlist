<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\Services\RandomCoffee\TelegramLinkHandler;
use App\Services\Telegram\Exceptions\TelegramApiException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class PollCoffeeTelegramUpdates extends Command
{
    private const OFFSET_CACHE_KEY = 'coffee:telegram-update-offset';

    protected $signature = 'coffee:telegram-poll';

    protected $description = 'Poll the Random Coffee bot for updates and link employees by their work email.';

    public function handle(CoffeeBotClientInterface $bot, TelegramLinkHandler $handler): int
    {
        if ((string) config('coffee.bot_token', '') === '') {
            $this->line('Skipped: Random Coffee bot token is not configured.');

            return self::SUCCESS;
        }

        $offset = (int) Cache::get(self::OFFSET_CACHE_KEY, 0);

        try {
            $updates = $bot->getUpdates($offset);
        } catch (TelegramApiException $e) {
            $this->error('Failed to fetch updates: ' . $e->getMessage());

            return self::FAILURE;
        }

        foreach ($updates as $update) {
            $updateId = (int) ($update['update_id'] ?? 0);

            $reply = $handler->handle($update);

            if ($reply !== null) {
                try {
                    $bot->send($reply);
                } catch (TelegramApiException $e) {
                    $this->warn(sprintf('Failed to reply to update %d: %s', $updateId, $e->getMessage()));
                }
            }

            if ($updateId >= $offset) {
                $offset = $updateId + 1;
                Cache::forever(self::OFFSET_CACHE_KEY, $offset);
            }
        }

        $this->line(sprintf('Processed %d update(s).', count($updates)));

        return self::SUCCESS;
    }
}