@extends('layouts.dashboard.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-danger">
                        <h4 class="card-title ">Calendar</h4>
                    </div>
                    <div class="card-body">
                        @include('alert')
                        <div class="table-responsive">
                            <table class="table">
                                <thead class=" text-primary">
                                <th>Date</th>
                                <th>Day Name</th>
                                <th>Is Weekday</th>
                                <th>Is Holiday</th>
                                <th>Actions</th>
                                </thead>
                                <tbody>

                                @foreach ($calendars as $calendar)
                                    <tr>
                                        <td>{{$calendar->dt}}</td>
                                        <td>{{$calendar->day_name}}</td>
                                        <td>{{$calendar->is_weekday}}</td>
                                        <td>{{$calendar->is_holiday}}</td>
                                        <td class="td-actions">
                                            <a href="{{ route('admin.calendar.edit', $calendar->id)}}"
                                               class="btn btn-success"><i class="material-icons">edit</i></a>
                                        <td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($calendars->total() > $calendars->count())
                        <div id="block-pagination">
                            <nav aria-label="Page navigation example">
                                <ul class="pagination">
                                    {{$calendars->links()}}
                                </ul>
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

