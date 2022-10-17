@extends('layouts.dashboard.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header card-header-danger">
                        <h4 class="card-title">Calendar Day: {{$calendar->day_name}} {{$calendar->dt}}</h4>
                    </div>
                    <div class="card-body">
                        @include('alert')
                        <form action="{{ route('admin.calendar.update', $calendar->id) }}" method="post">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Is Weekday</label>
                                        <input type="checkbox" name="is_weekday"
                                               @if(1 == $calendar->is_weekday)
                                                   checked
                                            @endif
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Is Holiday</label>
                                        <input type="checkbox" name="is_holiday"
                                               @if(1 == $calendar->is_holiday)
                                                   checked
                                            @endif
                                        >
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary pull-right">Update</button>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

