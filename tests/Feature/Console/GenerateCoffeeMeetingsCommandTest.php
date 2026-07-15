<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\CoffeeMeeting;
use App\CoffeeSetting;
use App\Member;
use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\Services\Telegram\Messages\TelegramMessage;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakeCoffeeBot;
use Tests\TestCase;

class GenerateCoffeeMeetingsCommandTest extends TestCase
{
    use RefreshDatabase;

    private FakeCoffeeBot $bot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bot = new FakeCoffeeBot();
        $this->app->instance(CoffeeBotClientInterface::class, $this->bot);
    }

    public function testGeneratesPairsOnWorkingDaysAndNotifiesLinkedParticipants(): void
    {
        $this->createEmployee('a@w4p.com', chatId: '111');
        $this->createEmployee('b@w4p.com', chatId: '222');
        $this->createEmployee('c@w4p.com');
        $this->createEmployee('d@w4p.com');

        // 2026-07-13 is Monday.
        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13'])
            ->assertExitCode(0);

        $meetings = CoffeeMeeting::all();
        $this->assertCount(2, $meetings);

        $participants = [];
        foreach ($meetings as $meeting) {
            $participants[] = (int) $meeting->user_one_id;
            $participants[] = (int) $meeting->user_two_id;

            $this->assertTrue($meeting->scheduled_at->isWeekday());
            $this->assertTrue($meeting->scheduled_at->greaterThan('2026-07-13'));
            $this->assertTrue($meeting->scheduled_at->lessThan('2026-07-20'));
            $this->assertStringStartsWith('https://meet.jit.si/w4p-coffee-20260713-', $meeting->meeting_url);
            $this->assertSame(CoffeeMeeting::STATUS_SCHEDULED, $meeting->status);
        }

        $this->assertCount(4, array_unique($participants));

        // Only the two linked employees can receive a personal message.
        $sentTo = array_map(fn (TelegramMessage $message) => $message->chatId, $this->bot->sent);
        sort($sentTo);
        $this->assertSame(['111', '222'], $sentTo);
    }

    public function testDoesNotDuplicateMeetingsForTheSameCycle(): void
    {
        $this->createEmployee('a@w4p.com');
        $this->createEmployee('b@w4p.com');

        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13'])->assertExitCode(0);
        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13'])->assertExitCode(0);

        $this->assertSame(1, CoffeeMeeting::count());
    }

    public function testNextWeeklyCycleIsNotBlockedByPreviousOne(): void
    {
        $this->createEmployee('a@w4p.com');
        $this->createEmployee('b@w4p.com');

        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13'])->assertExitCode(0);
        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-20'])->assertExitCode(0);

        $this->assertSame(2, CoffeeMeeting::count());
    }

    public function testExactFitSlotIntersectionIsUsedInsteadOfDefaultTime(): void
    {
        // Both slots are 16:00-16:30 with a 30-minute meeting: the only valid
        // start is 16:00, the 12:00 default must not leak outside the slot.
        $one = $this->createEmployee('a@w4p.com');
        $two = $this->createEmployee('b@w4p.com');
        $one->update(['work_time_from' => '16:00', 'work_time_to' => '16:30']);
        $two->update(['work_time_from' => '16:00', 'work_time_to' => '16:30']);

        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13'])->assertExitCode(0);

        $this->assertSame('16:00', CoffeeMeeting::sole()->scheduled_at->format('H:i'));
    }

    public function testDryRunPersistsAndSendsNothing(): void
    {
        $this->createEmployee('a@w4p.com', chatId: '111');
        $this->createEmployee('b@w4p.com', chatId: '222');

        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13', '--dry-run' => true])
            ->assertExitCode(0);

        $this->assertSame(0, CoffeeMeeting::count());
        $this->assertSame([], $this->bot->sent);
    }

    public function testExcludesInactiveAndOptedOutEmployees(): void
    {
        $this->createEmployee('a@w4p.com');
        $this->createEmployee('b@w4p.com');
        $this->createEmployee('inactive@w4p.com', active: false);
        $this->createEmployee('optout@w4p.com', coffee: false);

        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13'])->assertExitCode(0);

        $meeting = CoffeeMeeting::sole();
        $emails = User::whereIn('id', [$meeting->user_one_id, $meeting->user_two_id])->pluck('email')->sort()->values()->all();

        $this->assertSame(['a@w4p.com', 'b@w4p.com'], $emails);
    }

    public function testAutoModeSkipsWhenDisabled(): void
    {
        $this->createEmployee('a@w4p.com');
        $this->createEmployee('b@w4p.com');

        CoffeeSetting::current()->update(['enabled' => false]);

        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13', '--auto' => true])
            ->assertExitCode(0);

        $this->assertSame(0, CoffeeMeeting::count());
    }

    public function testAutoFrequencyCountsFromCycleDateNotCreatedAt(): void
    {
        $this->createEmployee('a@w4p.com');
        $this->createEmployee('b@w4p.com');

        CoffeeSetting::current()->update(['enabled' => true, 'matching_day' => 1, 'frequency_weeks' => 2]);

        // Backfilled cycle: generated late (created_at 2026-07-22) for Monday 2026-07-13.
        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13'])->assertExitCode(0);
        CoffeeMeeting::query()->update(['created_at' => '2026-07-22 10:00:00']);

        // Two weeks after the cycle date the scheduler must run again.
        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-27', '--auto' => true])
            ->assertExitCode(0);

        $this->assertSame(2, CoffeeMeeting::count());
    }

    public function testAutoModeRunsOnMatchingDayWhenEnabled(): void
    {
        $this->createEmployee('a@w4p.com');
        $this->createEmployee('b@w4p.com');

        CoffeeSetting::current()->update(['enabled' => true, 'matching_day' => 1]);

        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13', '--auto' => true])
            ->assertExitCode(0);

        $this->assertSame(1, CoffeeMeeting::count());
    }

    private function createEmployee(string $email, bool $active = true, bool $coffee = true, ?string $chatId = null): Member
    {
        $user = User::create([
            'name' => $email,
            'email' => $email,
            'password' => 'secret-password',
            'active' => $active,
        ]);

        return Member::create([
            'user_id' => $user->id,
            'name' => 'Name-' . $user->id,
            'surname' => 'Surname-' . $user->id,
            'email' => $email,
            'random_coffee' => $coffee,
            'telegram_chat_id' => $chatId,
        ]);
    }
}