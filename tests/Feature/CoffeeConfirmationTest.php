<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\CoffeeMeeting;
use App\Member;
use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Support\FakeCoffeeBot;
use Tests\TestCase;

class CoffeeConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function testYesAnswerMarksMeetingHeld(): void
    {
        $meeting = $this->createMeeting();

        $url = $this->confirmUrl($meeting, (int) $meeting->user_one_id, 'yes');

        $this->get($url)->assertOk()->assertSee('Дякуємо');

        $meeting->refresh();
        $this->assertTrue($meeting->user_one_attended);
        $this->assertSame(CoffeeMeeting::STATUS_HELD, $meeting->status);
    }

    public function testBothNoAnswersMarkMeetingNotHeld(): void
    {
        $meeting = $this->createMeeting();

        $this->get($this->confirmUrl($meeting, (int) $meeting->user_one_id, 'no'))->assertOk();
        $this->get($this->confirmUrl($meeting, (int) $meeting->user_two_id, 'no'))->assertOk();

        $meeting->refresh();
        $this->assertSame(CoffeeMeeting::STATUS_NOT_HELD, $meeting->status);
    }

    public function testRepeatedClickIsIdempotent(): void
    {
        $meeting = $this->createMeeting();
        $url = $this->confirmUrl($meeting, (int) $meeting->user_one_id, 'yes');

        $this->get($url)->assertOk();
        $this->get($url)->assertOk();

        $meeting->refresh();
        $this->assertSame(CoffeeMeeting::STATUS_HELD, $meeting->status);
    }

    public function testUnsignedRequestIsRejected(): void
    {
        $meeting = $this->createMeeting();

        $this->get(sprintf('/coffee/confirm/%d/%d/yes', $meeting->id, $meeting->user_one_id))
            ->assertForbidden();

        $this->assertSame(CoffeeMeeting::STATUS_SCHEDULED, $meeting->fresh()->status);
    }

    public function testForeignUserCannotAnswer(): void
    {
        $meeting = $this->createMeeting();
        $stranger = $this->createEmployee('stranger@w4p.com');

        $this->get($this->confirmUrl($meeting, (int) $stranger->user_id, 'yes'))
            ->assertForbidden();
    }

    public function testConfirmationQuestionsAreSentForYesterdayMeetings(): void
    {
        $bot = new FakeCoffeeBot();
        $this->app->instance(CoffeeBotClientInterface::class, $bot);

        $meeting = $this->createMeeting(chatIds: ['111', '222']);
        $meeting->update(['scheduled_at' => '2026-07-14 12:00:00']);

        $this->artisan('coffee:send-confirmations', ['--date' => '2026-07-14'])
            ->assertExitCode(0);

        $this->assertCount(2, $bot->sent);
        $this->assertStringContainsString('/coffee/confirm/', $bot->sent[0]->text);
        $this->assertStringContainsString('signature=', $bot->sent[0]->text);
        $this->assertStringContainsString('expires=', $bot->sent[0]->text);

        // The question names the meeting it asks about: partner, when, link.
        $partnerName = $meeting->userTwo->member->name;
        $this->assertStringContainsString($partnerName, $bot->sent[0]->text);
        $this->assertStringContainsString('12:00', $bot->sent[0]->text);
        $this->assertStringContainsString((string) $meeting->meeting_url, $bot->sent[0]->text);

        // Re-running the command must not ask the same participants again.
        $this->artisan('coffee:send-confirmations', ['--date' => '2026-07-14'])
            ->assertExitCode(0);

        $this->assertCount(2, $bot->sent);
    }

    public function testFailedConfirmationSendReleasesReservationForRetry(): void
    {
        $bot = new class implements \App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface {
            public function send(\App\Services\Telegram\Messages\TelegramMessage $message): int
            {
                throw new \App\Services\Telegram\Exceptions\TelegramApiException('boom');
            }

            public function getUpdates(int $offset, int $limit = 50): array
            {
                return [];
            }

            public function setWebhook(string $url, string $secretToken, bool $dropPendingUpdates = false): void
            {
            }

            public function deleteWebhook(bool $dropPendingUpdates = false): void
            {
            }

            public function getWebhookInfo(): array
            {
                return [];
            }
        };
        $this->app->instance(CoffeeBotClientInterface::class, $bot);

        $meeting = $this->createMeeting(chatIds: ['111', '222']);

        $this->artisan('coffee:send-confirmations', ['--date' => '2026-07-14'])
            ->assertExitCode(0);

        // Both sends failed, so neither participant is marked as asked —
        // a later run may retry.
        $meeting->refresh();
        $this->assertNull($meeting->user_one_asked_at);
        $this->assertNull($meeting->user_two_asked_at);
    }

    public function testExpiredSignedLinkIsRejected(): void
    {
        $meeting = $this->createMeeting();

        $url = URL::temporarySignedRoute('coffee.confirm', now()->subMinute(), [
            'meeting' => $meeting->id,
            'user' => $meeting->user_one_id,
            'answer' => 'yes',
        ]);

        $this->get($url)->assertForbidden();
        $this->assertSame(CoffeeMeeting::STATUS_SCHEDULED, $meeting->fresh()->status);
    }

    /**
     * @param array{0: ?string, 1: ?string} $chatIds
     */
    private function createMeeting(array $chatIds = [null, null]): CoffeeMeeting
    {
        $one = $this->createEmployee('one@w4p.com', $chatIds[0]);
        $two = $this->createEmployee('two@w4p.com', $chatIds[1]);

        return CoffeeMeeting::create([
            'user_one_id' => $one->user_id,
            'user_two_id' => $two->user_id,
            'scheduled_at' => '2026-07-14 12:00:00',
            'meeting_url' => 'https://meet.jit.si/w4p-coffee-test',
        ]);
    }

    private function createEmployee(string $email, ?string $chatId = null): Member
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
            'telegram_chat_id' => $chatId,
        ]);
    }

    private function confirmUrl(CoffeeMeeting $meeting, int $userId, string $answer): string
    {
        return URL::signedRoute('coffee.confirm', [
            'meeting' => $meeting->id,
            'user' => $userId,
            'answer' => $answer,
        ]);
    }
}