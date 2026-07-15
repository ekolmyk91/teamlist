@extends('layouts.dashboard.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <a href="{{ route('admin.coffee.index') }}" class="btn btn-primary">Back to Random Coffee</a>
            <div class="card">
                <div class="card-header card-header-danger">
                    <h4 class="card-title">Random Coffee Settings</h4>
                </div>
                <div class="card-body">
                    @include('alert')
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.coffee.settings.update') }}" method="post">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <input type="checkbox" name="enabled" id="enabled" value="1"
                                   {{ old('enabled', $settings->enabled) ? 'checked' : '' }}>
                            <label for="enabled">Enabled</label>
                            <small class="form-text text-muted">
                                Master switch: when off, the scheduler never generates new pairs automatically.
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Frequency, weeks</label>
                                    <input type="number" name="frequency_weeks" class="form-control" min="1" max="8"
                                           value="{{ old('frequency_weeks', $settings->frequency_weeks) }}">
                                    <small class="form-text text-muted">
                                        1 = every week, 2 = every two weeks, and so on.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Matching day</label>
                                    <select name="matching_day" class="custom-select">
                                        @foreach ([1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 0 => 'Sunday'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ (string) old('matching_day', $settings->matching_day) === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        Day of week when pairs are generated. Meetings themselves are spread over
                                        the following working days (Mon-Fri), each pair gets its own day.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Default meeting time</label>
                                    <input type="text" name="meeting_time" class="form-control js-timepicker" placeholder="HH:MM" autocomplete="off"
                                           value="{{ old('meeting_time', substr((string) $settings->meeting_time, 0, 5)) }}">
                                    <small class="form-text text-muted">
                                        Used when participants' work slots are not set or do not overlap.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Meeting duration, minutes</label>
                                    <input type="number" name="meeting_duration" class="form-control" min="15" max="120" step="5"
                                           value="{{ old('meeting_duration', $settings->meeting_duration) }}">
                                    <small class="form-text text-muted">Recommended length of one coffee talk.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Company work time from</label>
                                    <input type="text" name="work_time_from" class="form-control js-timepicker" placeholder="HH:MM" autocomplete="off"
                                           value="{{ old('work_time_from', substr((string) $settings->work_time_from, 0, 5)) }}">
                                    <small class="form-text text-muted">
                                        Default working hours; used for employees without a personal schedule.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Company work time to</label>
                                    <input type="text" name="work_time_to" class="form-control js-timepicker" placeholder="HH:MM" autocomplete="off"
                                           value="{{ old('work_time_to', substr((string) $settings->work_time_to, 0, 5)) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Exclusion window, weeks</label>
                                    <input type="number" name="exclusion_weeks" class="form-control" min="0" max="26"
                                           value="{{ old('exclusion_weeks', $settings->exclusion_weeks) }}">
                                    <small class="form-text text-muted">
                                        The same two people will not be paired again within this number of weeks.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary pull-right">Save Settings</button>
                        <div class="clearfix"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
