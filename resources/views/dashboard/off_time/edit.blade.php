@extends('layouts.dashboard.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header card-header-danger">
                        <h4 class="card-title">New Off-Time Item</h4>
                    </div>
                    <div class="card-body">
                        @include('alert')
                        <form action="{{ route('admin.off_time.update', $offTime->id) }}" method="post">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-control">Start Day</label>
                                        <input id='start_day' name='start_day' type="date"
                                               class="form-control"
                                               value="{{ old('start_day', $offTime->start_day) }}"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-control">End day</label>
                                        <input id='end_day' name='end_day' type="date"
                                               class="form-control"
                                               value="{{ old('end_day', $offTime->end_day) }}"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">User</label>
                                        <select name='user_id' class="form-control selectpicker"
                                                data-style="btn btn-link" id="exampleFormControlSelect1">
                                            <option value="" disabled selected>-- Select --</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->user_id }}"
                                                    {{old('user', $offTime->user_id) == $user->user_id ? 'selected':''}}
                                                >{{$user->surname}} {{$user->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Type</label>
                                        <select name='type' class="form-control selectpicker"
                                                data-style="btn btn-link" id="exampleFormControlSelect1">
                                            <option value="" disabled selected>-- Select --</option>
                                            @foreach($types as $type)
                                                <option
                                                    value="{{ $type->id }}"
                                                    {{old('type', $offTime->type_id) == $type->id ? 'selected':''}}
                                                >{{$type->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Status</label>
                                        <select name='status' class="form-control selectpicker"
                                                data-style="btn btn-link" id="exampleFormControlSelect1">
                                            <option value="" disabled selected>-- Select --</option>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status }}"
                                                    {{old('status', $offTime->status) == $status ? 'selected':''}}
                                                >
                                                    {{strtoupper($status)}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary pull-right">Create Off-Time Item</button>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
