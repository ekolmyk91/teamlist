<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\CoffeeMeeting;
use App\CoffeeSetting;
use App\Member;
use App\Role;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoffeeAdminPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-test@w4p.com',
            'password' => 'secret-password',
            'active' => true,
        ]);
        $this->admin->roles()->attach(Role::where('name', 'admin')->first()->id);
    }

    public function testDashboardSettingsAndGuidePagesOpen(): void
    {
        $this->actingAs($this->admin)->get(route('admin.coffee.index'))
            ->assertOk()
            ->assertSee('Coffee Meetings');

        // Time fields are driven by the picker widget, not by the native
        // input[type=time] - Firefox has no time popup at all.
        $this->actingAs($this->admin)->get(route('admin.coffee.settings'))
            ->assertOk()
            ->assertSee('Random Coffee Settings')
            ->assertSee('name="work_time_from" class="form-control js-timepicker"', false)
            ->assertSee(".datetimepicker({", false);

        $this->actingAs($this->admin)->get(route('admin.coffee.guide'))
            ->assertOk()
            ->assertSee('Як працює Random Coffee');
    }

    public function testNonAdminIsRedirected(): void
    {
        $user = User::create([
            'name' => 'Plain',
            'email' => 'plain-test@w4p.com',
            'password' => 'secret-password',
            'active' => true,
        ]);

        $this->actingAs($user)->get(route('admin.coffee.index'))->assertRedirect('/404');
    }

    public function testSettingsCanBeSaved(): void
    {
        $this->actingAs($this->admin)->put(route('admin.coffee.settings.update'), [
            'enabled' => '1',
            'frequency_weeks' => 2,
            'matching_day' => 3,
            'meeting_time' => '14:30',
            'meeting_duration' => 45,
            'work_time_from' => '09:00',
            'work_time_to' => '17:00',
            'exclusion_weeks' => 6,
        ])->assertRedirect(route('admin.coffee.settings'));

        $settings = CoffeeSetting::current();
        $this->assertTrue($settings->enabled);
        $this->assertSame(2, $settings->frequency_weeks);
        $this->assertSame(3, $settings->matching_day);
        $this->assertSame('14:30:00', (string) $settings->meeting_time);
    }

    public function testMeetingCanBeAddedManually(): void
    {
        $one = $this->createEmployee('one@w4p.com');
        $two = $this->createEmployee('two@w4p.com');

        $this->actingAs($this->admin)->post(route('admin.coffee.meetings.store'), [
            'user_one_id' => $one->user_id,
            'user_two_id' => $two->user_id,
            'scheduled_at' => $this->futureSlot(),
        ])->assertRedirect(route('admin.coffee.index'));

        $meeting = CoffeeMeeting::sole();
        $this->assertSame(str_replace('T', ' ', $this->futureSlot()), $meeting->scheduled_at->format('Y-m-d H:i'));
        $this->assertStringStartsWith('https://meet.jit.si/w4p-coffee-', $meeting->meeting_url);
        $this->assertNull($meeting->cycle_date);
    }

    public function testManualMeetingRejectsUsersWithoutMemberProfile(): void
    {
        $employee = $this->createEmployee('one@w4p.com');

        // The admin user has no members row and must not be accepted.
        $this->actingAs($this->admin)->post(route('admin.coffee.meetings.store'), [
            'user_one_id' => $this->admin->id,
            'user_two_id' => $employee->user_id,
            'scheduled_at' => $this->futureSlot(),
        ])->assertSessionHasErrors('user_one_id');

        $this->assertSame(0, CoffeeMeeting::count());
    }

    public function testManualMeetingRequiresTwoDifferentEmployees(): void
    {
        $one = $this->createEmployee('one@w4p.com');

        $this->actingAs($this->admin)->post(route('admin.coffee.meetings.store'), [
            'user_one_id' => $one->user_id,
            'user_two_id' => $one->user_id,
            'scheduled_at' => $this->futureSlot(),
        ])->assertSessionHasErrors('user_two_id');

        $this->assertSame(0, CoffeeMeeting::count());
    }

    public function testManualMeetingDoesNotBlockAutoCycle(): void
    {
        $one = $this->createEmployee('one@w4p.com');
        $two = $this->createEmployee('two@w4p.com');

        $this->actingAs($this->admin)->post(route('admin.coffee.meetings.store'), [
            'user_one_id' => $one->user_id,
            'user_two_id' => $two->user_id,
            'scheduled_at' => $this->futureSlot(),
        ]);

        // The generated cycle for the same week must not be blocked by the manual meeting.
        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13'])->assertExitCode(0);

        $this->assertSame(2, CoffeeMeeting::count());
    }

    public function testManualMeetingRejectsPastDate(): void
    {
        $one = $this->createEmployee('one@w4p.com');
        $two = $this->createEmployee('two@w4p.com');

        $past = CarbonImmutable::now(config('coffee.timezone'))->subHour()->format('Y-m-d H:i');

        $this->actingAs($this->admin)->post(route('admin.coffee.meetings.store'), [
            'user_one_id' => $one->user_id,
            'user_two_id' => $two->user_id,
            'scheduled_at' => $past,
        ])->assertSessionHasErrors('scheduled_at');

        $this->assertSame(0, CoffeeMeeting::count());
    }

    public function testManualMeetingRejectsDeactivatedEmployee(): void
    {
        $one = $this->createEmployee('one@w4p.com');
        $two = $this->createEmployee('two@w4p.com', active: false);

        $this->actingAs($this->admin)->post(route('admin.coffee.meetings.store'), [
            'user_one_id' => $one->user_id,
            'user_two_id' => $two->user_id,
            'scheduled_at' => $this->futureSlot(),
        ])->assertSessionHasErrors('user_two_id');

        $this->assertSame(0, CoffeeMeeting::count());
    }

    public function testGenerateButtonRunsCurrentCycleWithoutShiftingTheSchedule(): void
    {
        $this->createEmployee('a@w4p.com');
        $this->createEmployee('b@w4p.com');
        $this->createEmployee('c@w4p.com');
        $this->createEmployee('d@w4p.com');

        CoffeeSetting::current()->update(['enabled' => true, 'matching_day' => 1, 'frequency_weeks' => 1]);

        // Admin presses "Generate" on Wednesday, mid-cycle.
        $this->travelTo(CarbonImmutable::parse('2026-07-15 11:00:00', config('coffee.timezone')));

        $this->actingAs($this->admin)->post(route('admin.coffee.generate'))->assertRedirect();

        $meetings = CoffeeMeeting::all();
        $this->assertCount(2, $meetings);

        foreach ($meetings as $meeting) {
            // Dated as Monday's cycle, not as a new one starting on Wednesday.
            $this->assertSame('2026-07-13', $meeting->cycle_date->toDateString());
            // Thursday and Friday only: Tuesday and Wednesday are already gone.
            $this->assertTrue($meeting->scheduled_at->greaterThanOrEqualTo('2026-07-16'));
        }

        $this->travelBack();

        // The next Monday is still a full frequency period away, so it runs.
        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-20', '--auto' => true])
            ->assertExitCode(0);

        $this->assertSame(4, CoffeeMeeting::count());
    }

    public function testGenerateButtonIsIdempotentWithinTheCycle(): void
    {
        $this->createEmployee('a@w4p.com');
        $this->createEmployee('b@w4p.com');

        $this->travelTo(CarbonImmutable::parse('2026-07-15 11:00:00', config('coffee.timezone')));

        $this->actingAs($this->admin)->post(route('admin.coffee.generate'))->assertRedirect();
        $this->actingAs($this->admin)->post(route('admin.coffee.generate'))->assertRedirect();

        $this->assertSame(1, CoffeeMeeting::count());

        $this->travelBack();
    }

    public function testAdminUncheckingParticipationCancelsUpcomingMeetings(): void
    {
        $leaver = $this->createEmployee('leaver@web4pro.net');
        $partner = $this->createEmployee('partner@web4pro.net');
        $partner->update(['telegram_chat_id' => '888']);

        $upcoming = CoffeeMeeting::create([
            'user_one_id' => $leaver->user_id,
            'user_two_id' => $partner->user_id,
            'scheduled_at' => CarbonImmutable::now()->addDays(2),
            'meeting_url' => 'https://meet.jit.si/w4p-coffee-upcoming',
        ]);

        $this->actingAs($this->admin)->put(route('admin.members.update', $leaver->user_id), [
            'name' => $leaver->name,
            'surname' => $leaver->surname,
            'email' => $leaver->email,
            'active' => 'on',
            // random_coffee is absent: the admin unticked the checkbox.
            'url' => route('admin.coffee.index'),
        ])->assertRedirect();

        $this->assertFalse($leaver->fresh()->random_coffee);
        $this->assertNull(CoffeeMeeting::find($upcoming->id));
    }

    public function testParticipationCanBeToggledFromTheMembersList(): void
    {
        $leaver = $this->createEmployee('leaver-toggle@w4p.com');
        $partner = $this->createEmployee('partner-toggle@w4p.com');
        $partner->update(['telegram_chat_id' => '888']);

        $upcoming = CoffeeMeeting::create([
            'user_one_id' => $leaver->user_id,
            'user_two_id' => $partner->user_id,
            'scheduled_at' => CarbonImmutable::now()->addDays(2),
            'meeting_url' => 'https://meet.jit.si/w4p-coffee-upcoming',
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.members.coffee.toggle', $leaver->user_id))
            ->assertRedirect();

        $this->assertFalse($leaver->fresh()->random_coffee);
        // Same rule as unticking the box on the member card.
        $this->assertNull(CoffeeMeeting::find($upcoming->id));

        $this->actingAs($this->admin)
            ->patch(route('admin.members.coffee.toggle', $leaver->user_id))
            ->assertRedirect();

        $this->assertTrue($leaver->fresh()->random_coffee);
    }

    public function testTopUpPairsEmployeesTheRunningCycleLeftWithoutAMeeting(): void
    {
        $this->createEmployee('a-top@w4p.com');
        $this->createEmployee('b-top@w4p.com');

        CoffeeSetting::current()->update(['enabled' => true, 'matching_day' => 1, 'frequency_weeks' => 1]);

        // Monday: the scheduler pairs the two employees there are.
        $this->travelTo(CarbonImmutable::parse('2026-07-13 09:00:00', config('coffee.timezone')));
        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13', '--auto' => true])->assertExitCode(0);
        $this->assertSame(1, CoffeeMeeting::count());

        // Tuesday: two more people join the programme. "Generate" is a no-op -
        // the cycle exists - so they can only come in through the top-up.
        $this->travelTo(CarbonImmutable::parse('2026-07-14 11:00:00', config('coffee.timezone')));
        $this->createEmployee('c-top@w4p.com');
        $this->createEmployee('d-top@w4p.com');

        $this->actingAs($this->admin)->post(route('admin.coffee.generate'))->assertRedirect();
        $this->assertSame(1, CoffeeMeeting::count());

        $this->actingAs($this->admin)->post(route('admin.coffee.topUp'))->assertRedirect();
        $this->assertSame(2, CoffeeMeeting::count());

        $added = CoffeeMeeting::orderByDesc('id')->first();
        // Dated as the running cycle, so the frequency countdown stays put.
        $this->assertSame('2026-07-13', $added->cycle_date->toDateString());
        $this->assertTrue($added->scheduled_at->greaterThan('2026-07-14'));
        $this->assertTrue($added->scheduled_at->isWeekday());

        $this->travelBack();

        // The next Monday still runs on schedule.
        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-20', '--auto' => true])->assertExitCode(0);
        $this->assertSame(4, CoffeeMeeting::count());
    }

    public function testTopUpDoesNothingWhenEveryoneAlreadyHasAMeeting(): void
    {
        $this->createEmployee('a-full@w4p.com');
        $this->createEmployee('b-full@w4p.com');

        $this->travelTo(CarbonImmutable::parse('2026-07-13 09:00:00', config('coffee.timezone')));
        $this->artisan('coffee:generate-meetings', ['--date' => '2026-07-13'])->assertExitCode(0);

        $this->travelTo(CarbonImmutable::parse('2026-07-14 11:00:00', config('coffee.timezone')));
        $this->actingAs($this->admin)->post(route('admin.coffee.topUp'))->assertRedirect();

        $this->assertSame(1, CoffeeMeeting::count());

        $this->travelBack();
    }

    public function testTopUpSkipsEmployeesWhoAlreadyHaveAManualMeeting(): void
    {
        $paired = $this->createEmployee('manual-a@w4p.com');
        $partner = $this->createEmployee('manual-b@w4p.com');
        $this->createEmployee('lonely@w4p.com');

        $this->travelTo(CarbonImmutable::parse('2026-07-14 11:00:00', config('coffee.timezone')));

        CoffeeMeeting::create([
            'user_one_id' => $paired->user_id,
            'user_two_id' => $partner->user_id,
            'scheduled_at' => CarbonImmutable::parse('2026-07-16 12:00:00', config('coffee.timezone')),
            'meeting_url' => 'https://meet.jit.si/w4p-coffee-manual',
        ]);

        // Only one person is free, so there is nobody to pair them with.
        $this->actingAs($this->admin)->post(route('admin.coffee.topUp'))->assertRedirect();

        $this->assertSame(1, CoffeeMeeting::count());

        $this->travelBack();
    }

    public function testMeetingCanBeDeleted(): void
    {
        $meeting = $this->createMeeting();

        $this->actingAs($this->admin)
            ->delete(route('admin.coffee.meetings.destroy', $meeting))
            ->assertRedirect();

        $this->assertSame(0, CoffeeMeeting::count());
    }

    public function testMeetingStatusCanBeSetManually(): void
    {
        $meeting = $this->createMeeting();

        $this->actingAs($this->admin)
            ->patch(route('admin.coffee.meetings.status', $meeting), ['status' => CoffeeMeeting::STATUS_HELD])
            ->assertRedirect();

        $this->assertSame(CoffeeMeeting::STATUS_HELD, $meeting->fresh()->status);
    }

    public function testMeetingsCanBeFilteredByEmployee(): void
    {
        $meeting = $this->createMeeting();
        $outsider = $this->createEmployee('outsider@w4p.com');

        $this->actingAs($this->admin)
            ->get(route('admin.coffee.index', ['employee' => $meeting->user_one_id]))
            ->assertOk()
            ->assertSee('w4p-coffee-test');

        $this->actingAs($this->admin)
            ->get(route('admin.coffee.index', ['employee' => $outsider->user_id]))
            ->assertOk()
            ->assertDontSee('w4p-coffee-test');
    }

    private function createMeeting(): CoffeeMeeting
    {
        $one = $this->createEmployee('one@w4p.com');
        $two = $this->createEmployee('two@w4p.com');

        return CoffeeMeeting::create([
            'user_one_id' => $one->user_id,
            'user_two_id' => $two->user_id,
            'scheduled_at' => '2026-07-14 12:00:00',
            'meeting_url' => 'https://meet.jit.si/w4p-coffee-test',
        ]);
    }

    /**
     * A slot the manual meeting form would accept: local time, still ahead.
     */
    private function futureSlot(): string
    {
        // Exactly what the native datetime-local input posts.
        return CarbonImmutable::now(config('coffee.timezone'))
            ->addDays(3)
            ->setTime(15, 30)
            ->format('Y-m-d\TH:i');
    }

    private function createEmployee(string $email, bool $active = true): Member
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
        ]);
    }
}
