<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\Services\Telegram\Exceptions\TelegramApiException;
use Illuminate\Console\Command;

/**
 * Registers, removes or inspects the Random Coffee bot webhook. Needed because
 * Telegram only pushes updates to a webhook once setWebhook is called (and that
 * call also disables getUpdates polling, so the two modes are mutually
 * exclusive at Telegram's side).
 */
final class ManageCoffeeTelegramWebhook extends Command
{
    protected $signature = 'coffee:telegram-webhook
        {action=info : set, delete or info}
        {--drop-pending : Discard updates Telegram has queued (destructive; off by default)}';

    protected $description = 'Manage the Random Coffee bot Telegram webhook (set/delete/info).';

    public function handle(CoffeeBotClientInterface $bot): int
    {
        if ((string) config('coffee.bot_token', '') === '') {
            $this->error('Random Coffee bot token is not configured (TELEGRAM_COFFEE_BOT_TOKEN).');

            return self::FAILURE;
        }

        $action = (string) $this->argument('action');

        try {
            return match ($action) {
                'set' => $this->setWebhook($bot),
                'delete' => $this->deleteWebhook($bot),
                'info' => $this->showInfo($bot),
                default => $this->invalidAction($action),
            };
        } catch (TelegramApiException $e) {
            $this->error('Telegram API error: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function setWebhook(CoffeeBotClientInterface $bot): int
    {
        $secret = (string) config('coffee.webhook_secret', '');

        if ($secret === '') {
            $this->error('Set TELEGRAM_COFFEE_BOT_WEBHOOK_SECRET before registering the webhook.');

            return self::FAILURE;
        }

        // Telegram accepts a secret token of 1-256 chars from A-Z, a-z, 0-9, _
        // and - only; a base64 secret with /, + or = would be rejected there.
        if (preg_match('/^[A-Za-z0-9_-]{1,256}$/', $secret) !== 1) {
            $this->error('TELEGRAM_COFFEE_BOT_WEBHOOK_SECRET must be 1-256 chars of A-Z, a-z, 0-9, _ or - only.');

            return self::FAILURE;
        }

        $url = route('coffee.telegram.webhook');

        // Telegram refuses non-HTTPS webhook URLs; APP_URL drives the scheme.
        if (! str_starts_with($url, 'https://')) {
            $this->error('Webhook URL must be HTTPS (got ' . $url . '). Set APP_URL to your https domain.');

            return self::FAILURE;
        }

        $bot->setWebhook($url, $secret, dropPendingUpdates: $this->wantsDropPending());

        $this->info('Webhook set to ' . $url);

        return self::SUCCESS;
    }

    private function deleteWebhook(CoffeeBotClientInterface $bot): int
    {
        $bot->deleteWebhook(dropPendingUpdates: $this->wantsDropPending());

        $this->info('Webhook deleted.');

        // Polling only runs from the scheduler when APP_ENV=local, so outside
        // local nothing will consume updates until a webhook is set again.
        if ($this->getLaravel()->environment('local')) {
            $this->line('Local environment: the bot falls back to getUpdates polling.');
        } else {
            $this->warn('No updates will be received until a webhook is set again (polling runs only locally).');
        }

        return self::SUCCESS;
    }

    private function wantsDropPending(): bool
    {
        return (bool) $this->option('drop-pending');
    }

    private function showInfo(CoffeeBotClientInterface $bot): int
    {
        $info = $bot->getWebhookInfo();

        $url = (string) ($info['url'] ?? '');

        $this->line('URL: ' . ($url === '' ? '(none — polling mode)' : $url));
        $this->line('Pending updates: ' . (string) ($info['pending_update_count'] ?? 0));

        if (! empty($info['last_error_message'])) {
            $this->warn('Last error: ' . (string) $info['last_error_message']);
        }

        return self::SUCCESS;
    }

    private function invalidAction(string $action): int
    {
        $this->error(sprintf('Unknown action "%s". Use set, delete or info.', $action));

        return self::FAILURE;
    }
}
