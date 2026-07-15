<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\CoffeeMeeting;
use App\Member;
use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\Services\Telegram\Messages\TelegramMessage;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakeCoffeeBot;
use Tests\TestCase;

class PollCoffeeTelegramUpdatesCommandTest extends TestCase
{
    use RefreshDatabase;

    private FakeCoffeeBot $bot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bot = new FakeCoffeeBot();
        $this->app->instance(CoffeeBotClientInterface::class, $this->bot);
        config(['coffee.bot_token' => 'test-token']);
    }

    public function testSkipsGracefullyWhenBotTokenIsMissing(): void
    {
        config(['coffee.bot_token' => '']);
        $this->bot->updates = [$this->update(1, '555', '/start')];

        $this->artisan('coffee:telegram-poll')
            ->expectsOutputToContain('Skipped')
            ->assertExitCode(0);

        $this->assertSame([], $this->bot->sent);
    }

    public function testStartCommandGetsWelcomeAskingForEmail(): void
    {
        $this->bot->updates = [$this->update(1, '555', '/start')];

        $this->artisan('coffee:telegram-poll')->assertExitCode(0);

        $this->assertCount(1, $this->bot->sent);
        $this->assertSame('555', $this->bot->sent[0]->chatId);
        $this->assertStringContainsString('email', $this->bot->sent[0]->text);
    }

    public function testKnownEmailLinksChatId(): void
    {
        $member = $this->createEmployee('worker@w4p.com');
        $this->bot->updates = [$this->update(2, '777', 'Worker@W4P.com')];

        $this->artisan('coffee:telegram-poll')->assertExitCode(0);

        $this->assertSame('777', $member->fresh()->telegram_chat_id);
        $this->assertStringContainsString('Random Coffee', $this->bot->sent[0]->text);
    }

    public function testLinkedEmployeeIsNotAskedForTheirEmailAgain(): void
    {
        $member = $this->createEmployee('worker@w4p.com');
        $member->update(['telegram_chat_id' => '777']);

        // Small talk and /start from a connected chat: the bot must not ask for
        // an email it already has.
        $this->bot->updates = [
            $this->update(11, '777', 'привіт'),
            $this->update(12, '777', '/start'),
        ];

        $this->artisan('coffee:telegram-poll')->assertExitCode(0);

        $this->assertCount(2, $this->bot->sent);

        foreach ($this->bot->sent as $message) {
            $this->assertStringNotContainsString('email-адресу', $message->text);
            $this->assertStringContainsString('уже підключено', $message->text);
        }
    }

    public function testUnknownEmailDoesNotLinkAnything(): void
    {
        $member = $this->createEmployee('worker@w4p.com');
        $this->bot->updates = [$this->update(3, '777', 'stranger@w4p.com')];

        $this->artisan('coffee:telegram-poll')->assertExitCode(0);

        $this->assertNull($member->fresh()->telegram_chat_id);
        $this->assertStringContainsString('не знайдено', $this->bot->sent[0]->text);
    }

    public function testLinkingDoesNotSwitchParticipationBackOn(): void
    {
        $member = $this->createEmployee('worker@w4p.com');
        $member->update(['random_coffee' => false]);

        $this->bot->updates = [$this->update(7, '777', 'worker@w4p.com')];

        $this->artisan('coffee:telegram-poll')->assertExitCode(0);

        // The chat is linked, but only the admin can put someone back in.
        $member->refresh();
        $this->assertSame('777', $member->telegram_chat_id);
        $this->assertFalse($member->random_coffee);
        $this->assertStringContainsString('адміністратора', $this->bot->sent[0]->text);
    }

    public function testStopUnlinksAndLeavesTheProgramme(): void
    {
        $member = $this->createEmployee('worker@w4p.com');
        $member->update(['telegram_chat_id' => '777', 'random_coffee' => true]);

        $this->bot->updates = [$this->update(8, '777', '/stop')];

        $this->artisan('coffee:telegram-poll')->assertExitCode(0);

        $member->refresh();
        $this->assertNull($member->telegram_chat_id);
        $this->assertFalse($member->random_coffee);
        $this->assertStringContainsString('вийшли з Random Coffee', $this->bot->sent[0]->text);
    }

    public function testStopCancelsUpcomingMeetingsAndWarnsThePartner(): void
    {
        $leaver = $this->createEmployee('leaver@w4p.com');
        $leaver->update(['telegram_chat_id' => '777']);
        $partner = $this->createEmployee('partner@w4p.com');
        $partner->update(['telegram_chat_id' => '888']);

        $upcoming = CoffeeMeeting::create([
            'user_one_id' => $leaver->user_id,
            'user_two_id' => $partner->user_id,
            'scheduled_at' => CarbonImmutable::now()->addDays(2),
            'meeting_url' => 'https://meet.jit.si/w4p-coffee-upcoming',
        ]);
        $past = CoffeeMeeting::create([
            'user_one_id' => $leaver->user_id,
            'user_two_id' => $partner->user_id,
            'scheduled_at' => CarbonImmutable::now()->subDays(2),
            'meeting_url' => 'https://meet.jit.si/w4p-coffee-past',
        ]);

        $this->bot->updates = [$this->update(10, '777', '/stop')];

        $this->artisan('coffee:telegram-poll')->assertExitCode(0);

        // The future meeting is gone, history stays.
        $this->assertNull(CoffeeMeeting::find($upcoming->id));
        $this->assertNotNull(CoffeeMeeting::find($past->id));

        $recipients = array_map(fn (TelegramMessage $message) => $message->chatId, $this->bot->sent);
        $this->assertContains('888', $recipients);

        $toPartner = collect($this->bot->sent)->firstWhere('chatId', '888');
        $this->assertStringContainsString('скасовано', $toPartner->text);
    }

    public function testStopFromAnUnlinkedChatChangesNothing(): void
    {
        $member = $this->createEmployee('worker@w4p.com');
        $member->update(['telegram_chat_id' => '777', 'random_coffee' => true]);

        $this->bot->updates = [$this->update(9, '999', '/stop')];

        $this->artisan('coffee:telegram-poll')->assertExitCode(0);

        $member->refresh();
        $this->assertSame('777', $member->telegram_chat_id);
        $this->assertTrue($member->random_coffee);
        $this->assertStringContainsString('не підключені', $this->bot->sent[0]->text);
    }

    public function testAlreadyLinkedEmployeeIsNotHijackedFromAnotherAccount(): void
    {
        $member = $this->createEmployee('worker@w4p.com');
        $member->update(['telegram_chat_id' => '777']);

        // Someone else sends the colleague's email from their own chat.
        $this->bot->updates = [$this->update(5, '999', 'worker@w4p.com')];

        $this->artisan('coffee:telegram-poll')->assertExitCode(0);

        $this->assertSame('777', $member->fresh()->telegram_chat_id);
        $this->assertSame('999', $this->bot->sent[0]->chatId);
        $this->assertStringContainsString('вже підключений', $this->bot->sent[0]->text);
    }

    public function testResendingTheEmailFromTheSameChatIsAccepted(): void
    {
        $member = $this->createEmployee('worker@w4p.com');
        $member->update(['telegram_chat_id' => '777']);

        $this->bot->updates = [$this->update(6, '777', 'worker@w4p.com')];

        $this->artisan('coffee:telegram-poll')->assertExitCode(0);

        $this->assertSame('777', $member->fresh()->telegram_chat_id);
        $this->assertStringContainsString('Random Coffee', $this->bot->sent[0]->text);
    }

    public function testGroupMessagesAreIgnored(): void
    {
        $this->bot->updates = [$this->update(4, '888', 'worker@w4p.com', 'group')];

        $this->artisan('coffee:telegram-poll')->assertExitCode(0);

        $this->assertSame([], $this->bot->sent);
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