<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use Illuminate\Support\Facades\URL;
use Tests\Support\FakeCoffeeBot;
use Tests\TestCase;

class ManageCoffeeTelegramWebhookCommandTest extends TestCase
{
    private FakeCoffeeBot $bot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bot = new FakeCoffeeBot();
        $this->app->instance(CoffeeBotClientInterface::class, $this->bot);
        config([
            'coffee.bot_token' => 'test-token',
            'coffee.webhook_secret' => 'super-secret-token',
        ]);
    }

    public function testFailsWhenTokenIsMissing(): void
    {
        config(['coffee.bot_token' => '']);

        $this->artisan('coffee:telegram-webhook set')
            ->expectsOutputToContain('token is not configured')
            ->assertExitCode(1);

        $this->assertSame([], $this->bot->webhookCalls);
    }

    public function testSetRegistersTheWebhookOverHttps(): void
    {
        URL::forceScheme('https');
        URL::forceRootUrl('https://coffee.test');

        $this->artisan('coffee:telegram-webhook set')
            ->expectsOutputToContain('https://coffee.test/coffee/telegram/webhook')
            ->assertExitCode(0);

        $this->assertCount(1, $this->bot->webhookCalls);
        $call = $this->bot->webhookCalls[0];
        $this->assertSame('set', $call['action']);
        $this->assertSame('https://coffee.test/coffee/telegram/webhook', $call['url']);
        $this->assertSame('super-secret-token', $call['secret_token']);
        // Queued updates are preserved unless --drop-pending is passed.
        $this->assertFalse($call['drop_pending_updates']);
    }

    public function testSetDropsPendingUpdatesOnlyWithTheFlag(): void
    {
        URL::forceScheme('https');
        URL::forceRootUrl('https://coffee.test');

        $this->artisan('coffee:telegram-webhook set --drop-pending')
            ->assertExitCode(0);

        $this->assertTrue($this->bot->webhookCalls[0]['drop_pending_updates']);
    }

    public function testSetRefusesAnInvalidSecret(): void
    {
        config(['coffee.webhook_secret' => 'has/invalid+chars=']);
        URL::forceScheme('https');
        URL::forceRootUrl('https://coffee.test');

        $this->artisan('coffee:telegram-webhook set')
            ->expectsOutputToContain('A-Z')
            ->assertExitCode(1);

        $this->assertSame([], $this->bot->webhookCalls);
    }

    public function testSetRefusesANonHttpsUrl(): void
    {
        URL::forceRootUrl('http://coffee.test');

        $this->artisan('coffee:telegram-webhook set')
            ->expectsOutputToContain('must be HTTPS')
            ->assertExitCode(1);

        $this->assertSame([], $this->bot->webhookCalls);
    }

    public function testSetRefusesWhenSecretIsMissing(): void
    {
        config(['coffee.webhook_secret' => '']);
        URL::forceScheme('https');
        URL::forceRootUrl('https://coffee.test');

        $this->artisan('coffee:telegram-webhook set')
            ->expectsOutputToContain('WEBHOOK_SECRET')
            ->assertExitCode(1);

        $this->assertSame([], $this->bot->webhookCalls);
    }

    public function testDeleteRemovesTheWebhook(): void
    {
        $this->artisan('coffee:telegram-webhook delete')
            ->assertExitCode(0);

        $this->assertCount(1, $this->bot->webhookCalls);
        $this->assertSame('delete', $this->bot->webhookCalls[0]['action']);
        $this->assertFalse($this->bot->webhookCalls[0]['drop_pending_updates']);
    }

    public function testInfoReportsTheCurrentWebhook(): void
    {
        $this->bot->webhookInfo = [
            'url' => 'https://coffee.test/coffee/telegram/webhook',
            'pending_update_count' => 3,
        ];

        $this->artisan('coffee:telegram-webhook info')
            ->expectsOutputToContain('https://coffee.test/coffee/telegram/webhook')
            ->expectsOutputToContain('3')
            ->assertExitCode(0);
    }

    public function testUnknownActionFails(): void
    {
        $this->artisan('coffee:telegram-webhook frobnicate')
            ->expectsOutputToContain('Unknown action')
            ->assertExitCode(1);
    }
}