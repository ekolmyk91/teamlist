<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\CoffeeMeeting;
use App\CoffeeSetting;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCoffeeMeetingRequest;
use App\Http\Requests\UpdateCoffeeSettingsRequest;
use App\Member;
use App\Services\RandomCoffee\MeetingGenerator;
use App\Services\RandomCoffee\MeetingNotifier;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CoffeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $meetings = CoffeeMeeting::query()
            ->with(['userOne.member', 'userTwo.member'])
            ->when($request->filled('employee'), fn ($query) => $query->forUser((int) $request->input('employee')))
            ->when($request->filled('status'), fn ($query) => $query->withStatus((string) $request->input('status')))
            ->orderByDesc('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.coffee.index', [
            'meetings' => $meetings,
            // Everyone for the filter (deactivated employees still have history),
            // only active ones for the manual meeting form.
            'employees' => Member::query()->orderBy('surname')->get(['user_id', 'name', 'surname']),
            'candidates' => Member::query()
                ->whereHas('user', fn ($query) => $query->where('active', true))
                ->orderBy('surname')
                ->get(['user_id', 'name', 'surname']),
            'stats' => $this->stats(),
            'botUsername' => (string) config('coffee.bot_username'),
            'filters' => [
                'employee' => $request->input('employee'),
                'status' => $request->input('status'),
            ],
        ]);
    }

    /**
     * Runs the current cycle now instead of starting a new one dated today:
     * the cycle keeps its matching-day date, so the frequency countdown - and
     * with it the scheduler's next run - stays on the usual weekly grid.
     * Meetings only land on days that are still ahead.
     */
    public function generate(MeetingGenerator $generator, MeetingNotifier $notifier): RedirectResponse
    {
        $today = CarbonImmutable::now((string) config('coffee.timezone', 'UTC'))->startOfDay();
        $cycleDate = $generator->currentCycleDate($today);

        if ($generator->alreadyGeneratedFor($cycleDate)) {
            return redirect()->route('admin.coffee.index')
                ->with('success', 'Meetings for the current cycle already exist.');
        }

        $meetings = $generator->generate($cycleDate, $today->addDay());

        if ($meetings->isEmpty()) {
            return redirect()->route('admin.coffee.index')
                ->with('success', 'Nothing to generate: the current cycle has no working days left, or there are not enough participants.');
        }

        $sent = $meetings->sum(fn (CoffeeMeeting $meeting) => $notifier->notify($meeting));

        return redirect()->route('admin.coffee.index')
            ->with('success', sprintf('Generated %d meeting(s), sent %d notification(s).', $meetings->count(), $sent));
    }

    /**
     * Fills the gaps in the cycle that is already running: the generate button
     * is a no-op once the cycle exists, so somebody added mid-cycle - or left
     * partnerless when their pair dropped out - has nowhere else to come from.
     */
    public function topUp(MeetingGenerator $generator, MeetingNotifier $notifier): RedirectResponse
    {
        $today = CarbonImmutable::now((string) config('coffee.timezone', 'UTC'))->startOfDay();
        $cycleDate = $generator->currentCycleDate($today);

        $meetings = $generator->topUp($cycleDate, $today->addDay());

        if ($meetings->isEmpty()) {
            return redirect()->route('admin.coffee.index')
                ->with('success', 'Nothing to top up: everyone already has a meeting this cycle, or no working days are left.');
        }

        $sent = $meetings->sum(fn (CoffeeMeeting $meeting) => $notifier->notify($meeting));

        return redirect()->route('admin.coffee.index')
            ->with('success', sprintf('Added %d meeting(s) to the current cycle, sent %d notification(s).', $meetings->count(), $sent));
    }

    public function storeMeeting(
        StoreCoffeeMeetingRequest $request,
        MeetingGenerator $generator,
        MeetingNotifier $notifier
    ): RedirectResponse {
        $scheduledAt = CarbonImmutable::parse(
            (string) $request->input('scheduled_at'),
            (string) config('coffee.timezone', 'UTC'),
        );

        // Manual meetings carry no cycle_date, so they never block or
        // postpone the automatically generated cycles.
        $meeting = CoffeeMeeting::create([
            'user_one_id' => (int) $request->input('user_one_id'),
            'user_two_id' => (int) $request->input('user_two_id'),
            'scheduled_at' => $scheduledAt,
            'meeting_url' => $generator->buildMeetingUrl($scheduledAt),
        ]);

        $sent = $notifier->notify($meeting);

        return redirect()->route('admin.coffee.index')
            ->with('success', sprintf('Meeting created, sent %d notification(s).', $sent));
    }

    public function destroyMeeting(CoffeeMeeting $meeting): RedirectResponse
    {
        $meeting->delete();

        return redirect()->back()->with('success', 'Meeting deleted!');
    }

    public function updateStatus(Request $request, CoffeeMeeting $meeting): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', CoffeeMeeting::STATUSES),
        ]);

        $meeting->update(['status' => $request->input('status')]);

        return redirect()->back()->with('success', 'Meeting status updated!');
    }

    public function settings()
    {
        return view('dashboard.coffee.settings', [
            'settings' => CoffeeSetting::current(),
        ]);
    }

    public function updateSettings(UpdateCoffeeSettingsRequest $request): RedirectResponse
    {
        CoffeeSetting::current()->update($request->settingsData());

        return redirect()->route('admin.coffee.settings')->with('success', 'Settings saved!');
    }

    public function guide()
    {
        return view('dashboard.coffee.guide', [
            'settings' => CoffeeSetting::current(),
            'botUsername' => (string) config('coffee.bot_username'),
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function stats(): array
    {
        $participants = Member::query()
            ->where('random_coffee', true)
            ->whereHas('user', fn ($query) => $query->where('active', true));

        return [
            'participants' => (clone $participants)->count(),
            'linked' => (clone $participants)->whereNotNull('telegram_chat_id')->count(),
            'meetings' => CoffeeMeeting::count(),
            'held' => CoffeeMeeting::withStatus(CoffeeMeeting::STATUS_HELD)->count(),
            'notHeld' => CoffeeMeeting::withStatus(CoffeeMeeting::STATUS_NOT_HELD)->count(),
        ];
    }
}
