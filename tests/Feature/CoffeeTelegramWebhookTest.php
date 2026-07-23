<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Member;
use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\FakeCoffeeBot;
use Tests\TestCase;

class CoffeeTelegramWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'super-secret-token';

    private FakeCoffeeBot $bot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bot = new FakeCoffeeBot();
        $this->app->instance(CoffeeBotClientInterface::class, $this->bot);
        config([
            'coffee.bot_token' => 'test-token',
            'coffee.webhook_secret' => self::SECRET,
        ]);
    }

    public function testRejectsRequestWithoutTheSecretHeader(): void
    {
        $this->postJson('/coffee/telegram/webhook', $this->update(1, '555', '/start'))
            ->assertForbidden();

        $this->assertSame([], $this->bot->sent);
    }

    public function testRejectsRequestWithAWrongSecret(): void
    {
        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'nope')
            ->postJson('/coffee/telegram/webhook', $this->update(1, '555', '/start'))
            ->assertForbidden();

        $this->assertSame([], $this->bot->sent);
    }

    public function testRejectsEverythingWhenNoSecretIsConfigured(): void
    {
        config(['coffee.webhook_secret' => '']);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', '')
            ->postJson('/coffee/telegram/webhook', $this->update(1, '555', '/start'))
            ->assertForbidden();

        $this->assertSame([], $this->bot->sent);
    }

    public function testLinksEmployeeByEmailOnAValidUpdate(): void
    {
        $member = $this->createEmployee('worker@w4p.com');

        $this->postWebhook($this->update(2, '777', 'Worker@W4P.com'))
            ->assertNoContent();

        $this->assertSame('777', $member->fresh()->telegram_chat_id);
        $this->assertCount(1, $this->bot->sent);
        $this->assertSame('777', $this->bot->sent[0]->chatId);
        $this->assertStringContainsString('Random Coffee', $this->bot->sent[0]->text);
    }

    public function testDuplicateUpdateIdIsProcessedOnlyOnce(): void
    {
        $member = $this->createEmployee('worker@w4p.com');
        $update = $this->update(42, '777', '/start');

        $this->postWebhook($update)->assertNoContent();
        // Telegram re-delivers the same update_id when a 2xx is slow to arrive;
        // the second delivery must be ignored.
        $this->postWebhook($update)->assertNoContent();

        $this->assertCount(1, $this->bot->sent);
        // A well-formed but unrelated update still gets through.
        $this->postWebhook($this->update(43, '777', '/start'))->assertNoContent();
        $this->assertCount(2, $this->bot->sent);
    }

    public function testReplyDeliveryFailureStillCommitsTheWorkAndReturns2xx(): void
    {
        $member = $this->createEmployee('worker@w4p.com');
        // The chat is linked inside the handler; only the confirmation reply
        // fails to send. That must not undo the link nor trigger a retry.
        $this->bot->throwOnSend = true;

        $this->postWebhook($this->update(2, '777', 'worker@w4p.com'))
            ->assertNoContent();

        $this->assertSame('777', $member->fresh()->telegram_chat_id);
    }

    public function testHandlerFailureReturns500AndKeepsTheUpdateRetryable(): void
    {
        $this->createEmployee('worker@w4p.com');
        // Simulate a transient failure inside the handler (DB unavailable).
        Schema::disableForeignKeyConstraints();
        Schema::drop('members');
        Schema::enableForeignKeyConstraints();

        $update = $this->update(2, '777', 'worker@w4p.com');

        // First delivery fails with a 5xx so Telegram will redeliver.
        $this->postWebhook($update)->assertStatus(500);
        // The redelivery is NOT deduplicated away — the dedup key was released,
        // so it is processed again (and fails again while the table is gone).
        $this->postWebhook($update)->assertStatus(500);
    }

    /**
     * @param array<string, mixed> $update
     */
    private function postWebhook(array $update): \Illuminate\Testing\TestResponse
    {
        return $this->withHeader('X-Telegram-Bot-Api-Secret-Token', self::SECRET)
            ->postJson('/coffee/telegram/webhook', $update);
    }

    /**
     * @return array<string, mixed>
     */
    private function update(int $id, string $chatId, string $text, string $chatType = 'private'): array
    {
        return [
            'update_id' => $id,
            'message' => [
                'chat' => ['id' => $chatId, 'type' => $chatType],
                'text' => $text,
            ],
        ];
    }

    private function createEmployee(string $email): Member
    {
        $user = User::create([
            'name' => $email,
            'email' => $email,
            'password' => 'secret-password',
            'active' => true,
        ]);

        return Member::create([
            'user_id' => $user->id,
            'name' => 'Name-' . $user->id,
            'surname' => 'Surname-' . $user->id,
            'email' => $email,
        ]);
    }
}