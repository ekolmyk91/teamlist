@extends('layouts.dashboard.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header card-header-danger">
                        <h4 class="card-title">Member: {{$member->surname}} {{$member->name}} </h4>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())

                            <div class="alert alert-danger">

                                <strong>Whoops!</strong> There were some problems with your input.<br><br>

                                <ul>

                                    @foreach ($errors->all() as $error)

                                        <li>{{ $error }}</li>

                                    @endforeach

                                </ul>

                            </div>

                        @endif
                        <form action="{{ route('admin.members.update', $member->user_id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Fist Name</label>
                                        <input name='name' type="text" class="form-control"
                                               value="{{$member->name}}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Last Name</label>
                                        <input name='surname' type="text" class="form-control"
                                               value="{{$member->surname}}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Email</label>
                                        <input name='email' type="email" class="form-control"
                                               value="{{$member->email}}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-control">Birthday</label>
                                        <input id='datepicker' name='birthday' type="text"
                                               class="form-control datetimepicker"
                                               value="{{ Carbon\Carbon::parse($member->birthday)->format('d/m/Y') }}"
                                               placeholder="dd/mm/yyyy"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Phone</label>
                                        <input name='phone_1' type="tel" class="form-control"
                                               value="{{$member->phone_1}}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Additional Phone</label>
                                        <input name='phone_2' type="tel" class="form-control"
                                               value="{{$member->phone_2}}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Department</label>
                                        <select name='department' class="form-control selectpicker"
                                                data-style="btn btn-link" id="exampleFormControlSelect1">
                                            <option value="">-- Select --</option>
                                            @foreach($departments as $department)
                                                <option value="{{$department->id}}"
                                                        @if($department->id == $member->department_id)
                                                        selected="selected"
                                                        @endif >
                                                    {{$department->name}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="bmd-label-floating">Avatar</label>
                                    <input name='avatar' type="file" class="form-control"
                                           value="{{$member->user->avatar}}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>About</label>
                                        <div class="form-group">
                                            <textarea name="about" class="form-control about"
                                                      rows="5">{{$member->about}}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Active</label>
                                        <div class="form-group">
                                            <input type="checkbox" name="active"
                                            @if($member->user->active)
                                                checked
                                            @endif
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary pull-right">Update Member</button>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-profile">
                    <div class="card-avatar">
                        @if($member->user->avatar)
                            <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                <div class="fileinput-new thumbnail img-raised">
                                    <img src="/storage/avatar/{{ $member->user->avatar }}" alt="...">
                                </div>
                                <div class="fileinput-preview fileinput-exists thumbnail img-raised"></div>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                            <div>
                                {{--<span class="btn btn-raised btn-round btn-default btn-file">--}}
                                    {{--<span class="fileinput-new">Select image</span>--}}
                                    {{--<span class="fileinput-exists">Change</span>--}}
                                    {{--<input type="file" name="avatar"/>--}}
                                {{--</span>--}}
                                {{--<a href="#pablo" class="btn btn-danger btn-round fileinput-exists"--}}
                                   {{--data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{--@push('scripts')--}}
    <script src="https://cloud.tinymce.com/stable/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector:'textarea',
            width: 900,
            height: 300
        });
    </script>
    {{--@endpush--}}
@endsection

