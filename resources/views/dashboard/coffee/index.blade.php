@extends('layouts.dashboard.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.coffee.settings') }}" class="btn btn-primary">Settings</a>
            <a href="{{ route('admin.coffee.guide') }}" class="btn btn-info">How it works</a>
            <form action="{{ route('admin.coffee.generate') }}" method="post" style="display: inline-block">
                @csrf
                <button type="submit" class="btn btn-success"
                        title="Runs the current cycle if it has not run yet. Keeps the usual schedule: meetings are dated from the matching day and only land on days still ahead.">
                    Generate pairs now
                </button>
            </form>
            <form action="{{ route('admin.coffee.topUp') }}" method="post" style="display: inline-block">
                @csrf
                <button type="submit" class="btn btn-warning"
                        title="Pairs up everyone the running cycle left without a meeting: joined mid-cycle, or lost their partner. Keeps the current cycle's date, so the schedule does not shift.">
                    Top up cycle
                </button>
            </form>

            <div class="row">
                @foreach ([
                    ['color' => 'info', 'icon' => 'group', 'label' => 'Participants', 'value' => $stats['participants']],
                    ['color' => 'primary', 'icon' => 'send', 'label' => 'Linked to bot', 'value' => $stats['linked'] . '/' . $stats['participants']],
                    ['color' => 'warning', 'icon' => 'local_cafe', 'label' => 'Meetings', 'value' => $stats['meetings']],
                    ['color' => 'success', 'icon' => 'check', 'label' => 'Held', 'value' => $stats['held']],
                    ['color' => 'danger', 'icon' => 'close', 'label' => 'Not held', 'value' => $stats['notHeld']],
                ] as $card)
                    <div class="col-md col-sm-6">
                        <div class="card card-stats" style="min-height: 90px">
                            <div class="card-header card-header-{{ $card['color'] }} card-header-icon">
                                <div class="card-icon"><i class="material-icons">{{ $card['icon'] }}</i></div>
                                <p class="card-category" style="white-space: nowrap">{{ $card['label'] }}</p>
                                <h3 class="card-title">{{ $card['value'] }}</h3>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($botUsername !== '')
                <p>
                    Bot link for employees:
                    <a href="https://t.me/{{ $botUsername }}" target="_blank" rel="noopener">https://t.me/{{ $botUsername }}</a>
                    — employee opens it, presses Start and sends their work email to join.
                </p>
            @else
                <p class="text-danger">
                    Telegram bot is not configured — set <code>TELEGRAM_COFFEE_BOT_TOKEN</code> and
                    <code>TELEGRAM_COFFEE_BOT_USERNAME</code> in <code>.env</code>.
                </p>
            @endif

            <div class="card">
                <div class="card-header card-header-success">
                    <h4 class="card-title">Add meeting manually</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('admin.coffee.meetings.store') }}" method="post" class="form-inline">
                        @csrf
                        <select name="user_one_id" class="custom-select" style="max-width: 220px" required>
                            <option value="">-- Participant 1 --</option>
                            @foreach ($candidates as $candidate)
                                <option value="{{ $candidate->user_id }}"
                                    {{ (string) $candidate->user_id === (string) old('user_one_id') ? 'selected' : '' }}>
                                    {{ $candidate->surname }} {{ $candidate->name }}
                                </option>
                            @endforeach
                        </select>
                        <select name="user_two_id" class="custom-select" style="max-width: 220px" required>
                            <option value="">-- Participant 2 --</option>
                            @foreach ($candidates as $candidate)
                                <option value="{{ $candidate->user_id }}"
                                    {{ (string) $candidate->user_id === (string) old('user_two_id') ? 'selected' : '' }}>
                                    {{ $candidate->surname }} {{ $candidate->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="datetime-local" name="scheduled_at" class="form-control"
                               value="{{ old('scheduled_at') }}"
                               min="{{ \Carbon\CarbonImmutable::now(config('coffee.timezone'))->format('Y-m-d\TH:i') }}"
                               required>
                        <button type="submit" class="btn btn-success btn-sm">Add meeting</button>
                    </form>
                    <small class="text-muted">
                        The video link is generated automatically; linked participants get a Telegram notification.
                    </small>
                </div>
            </div>

            <div class="card">
                <div class="card-header card-header-danger">
                    <h4 class="card-title">Coffee Meetings</h4>
                </div>
                <div class="card-body">
                    @include('alert')

                    <form action="{{ route('admin.coffee.index') }}" method="get" class="form-inline">
                        <select name="employee" class="custom-select" style="max-width: 260px">
                            <option value="">-- All employees --</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->user_id }}"
                                    {{ (string) $employee->user_id === (string) $filters['employee'] ? 'selected' : '' }}>
                                    {{ $employee->surname }} {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                        <select name="status" class="custom-select" style="max-width: 180px">
                            <option value="">-- All statuses --</option>
                            @foreach (\App\CoffeeMeeting::STATUSES as $status)
                                <option value="{{ $status }}" {{ $status === $filters['status'] ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="{{ route('admin.coffee.index') }}" class="btn btn-link btn-sm">Reset</a>
                    </form>

                    <div class="table-responsive">
                        <table class="table">
                            <thead class="text-primary">
                                <th>ID</th>
                                <th>Participant 1</th>
                                <th>Participant 2</th>
                                <th>Scheduled at</th>
                                <th>Link</th>
                                <th>Answers</th>
                                <th>Status</th>
                                <th>Set status</th>
                            </thead>
                            <tbody>
                            @forelse ($meetings as $meeting)
                                <tr>
                                    <td>{{ $meeting->id }}</td>
                                    <td>{{ $meeting->userOne?->member?->name }} {{ $meeting->userOne?->member?->surname }}</td>
                                    <td>{{ $meeting->userTwo?->member?->name }} {{ $meeting->userTwo?->member?->surname }}</td>
                                    <td>{{ $meeting->scheduled_at->format('d.m.Y H:i') }}</td>
                                    <td><a href="{{ $meeting->meeting_url }}" target="_blank" rel="noopener">Jitsi</a></td>
                                    <td>
                                        {{ $meeting->user_one_attended === null ? '—' : ($meeting->user_one_attended ? 'yes' : 'no') }}
                                        /
                                        {{ $meeting->user_two_attended === null ? '—' : ($meeting->user_two_attended ? 'yes' : 'no') }}
                                    </td>
                                    <td>
                                        @if ($meeting->status === \App\CoffeeMeeting::STATUS_HELD)
                                            <span class="badge badge-success">held</span>
                                        @elseif ($meeting->status === \App\CoffeeMeeting::STATUS_NOT_HELD)
                                            <span class="badge badge-danger">not held</span>
                                        @else
                                            <span class="badge badge-info">scheduled</span>
                                        @endif
                                    </td>
                                    <td class="td-actions">
                                        <form action="{{ route('admin.coffee.meetings.status', $meeting) }}" method="post" style="display: inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ \App\CoffeeMeeting::STATUS_HELD }}">
                                            <button type="submit" class="btn btn-success btn-sm" title="Mark as held">
                                                <i class="material-icons">check</i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.coffee.meetings.status', $meeting) }}" method="post" style="display: inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ \App\CoffeeMeeting::STATUS_NOT_HELD }}">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Mark as not held">
                                                <i class="material-icons">close</i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.coffee.meetings.destroy', $meeting) }}" method="post" style="display: inline"
                                              onsubmit="return confirm('Delete meeting #{{ $meeting->id }}? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-secondary btn-sm" title="Delete meeting">
                                                <i class="material-icons">delete</i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">No meetings yet. Enable the program in Settings or press "Generate pairs now".</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $meetings->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
