@extends('layouts.dashboard.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header card-header-danger">
                        <h4 class="card-title">New Member</h4>
                    </div>
                    <div class="card-body">
                        @include('alert')
                        <form id="usr-create-form" action="{{ route('admin.members.store') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Fist Name</label>
                                        <input name='name' type="text" class="form-control" value="{{ old('name') }}"
                                         minlength="2"
                                         maxlength="20"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Last Name</label>
                                        <input name='surname' type="text" class="form-control"
                                         value="{{ old('surname') }}"
                                         minlength="2"
                                         maxlength="40"
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Email</label>
                                        <input name='email' type="email" class="form-control"
                                               value="{{ old('email') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Google Email</label>
                                        <input name='gmail' type="email" class="form-control"
                                               value="{{ old('gmail') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Password</label>
                                        <input id="pwd" name='password' type="password" class="form-control"
                                               value="{{ old('password') }}" minlength="8" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <button type="button" id="eye"><i class="fa fa-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-control">Birthday (mm/dd)</label>
                                        <input id='birthday' name='birthday' type="text"
                                               class="form-control"
                                               value="{{ old('birthday') }}" placeholder="mm/dd"
                                               pattern="(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-control">Start work day</label>
                                        <input id='datepicker' name='start_work_day' type="date"
                                               class="form-control"
                                               max="{{ date( 'Y-m-d', strtotime( 'today' ) ) }}"
                                               value="{{ old('start_work_day') }}"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Phone</label>
                                        <input name='phone_1' type="tel" class="form-control"
                                               value="{{ old('phone_1') }}"
                                               pattern="^[0-9\-\+]{7,15}$">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Additional Phone</label>
                                        <input name='phone_2' type="tel" class="form-control"
                                               value="{{ old('phone_2') }}"
                                               pattern="^[0-9\-\+]{7,15}$">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">City</label>
                                        <input name='city' type="text" class="form-control"
                                               value="{{ old('city') }}"
                                               minlength="2"
                                               maxlength="40">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Department</label>
                                        <select name='department' class="form-control selectpicker"
                                                data-style="btn btn-link" id="exampleFormControlSelect1">
                                            <option value="" disabled selected>-- Select --</option>
                                            @foreach($departments as $department)
                                                <option value="{{$department->id}}"
                                                    {{ old('department') == $department->id ? 'selected' : '' }}>{{$department->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Position</label>
                                        <select name='position' class="form-control selectpicker"
                                                data-style="btn btn-link" id="exampleFormControlSelect1">
                                            <option value="" disabled selected>-- Select --</option>
                                            @foreach($positions as $position)
                                                <option
                                                    value="{{$position->id}}" {{ old('position') == $position->id ? 'selected' : '' }}>{{$position->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <input type="checkbox" name="active" checked>
                                            <label>Active</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <input type="checkbox" name="trainee">
                                            <label>Trainee</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="avatar"
                                           class="col-md-4 col-form-label text-md-right">{{ __('Avatar') }}</label>
                                    <input id="avatar" type="file" class="form-control" name="avatar" accept="image/jpeg, image/jpg">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="certificate" class="bmd-label-floating">Certificates</label>
                                        <select id="cert-options" class="custom-select" name="certificate[]" multiple size="5">
                                            <option value="" disabled>-- no --</option>
                                            @foreach($certificates as $id => $certificate)
                                                <option name='certificate'
                                                        value="{{ $id }}" {{ (in_array($id, old('certificate', []))) ? 'selected' : '' }}>{{ $certificate }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input id="reset-btn" type="reset" name="Reset">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>About</label>
                                        <div class="form-group">
                                            <textarea name="about" class="form-control"
                                                      rows="5" maxlength="40">{{ old('about') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <input id="role" type="checkbox" name="manager">
                                            <label for="role">Manager</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary pull-right">Create Member</button>
                            <input name='url' type="hidden" value="{{ old('url', URL::previous()) }}">
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-profile">
                    <div class="card-avatar">
                        {{--<a href="#pablo">--}}
                        {{--<img class="img" src="../assets/img/faces/marc.jpg" />--}}
                        {{--</a>--}}
                        <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                            <div class="fileinput-new thumbnail img-raised">
                                <img src="http://style.anu.edu.au/_anu/4/images/placeholders/person_8x10.png" alt="...">
                            </div>
                            <div class="fileinput-preview fileinput-exists thumbnail img-raised"></div>
                        </div>
                    </div>

                    <div class="card-body">

                        {{--<div class="fileinput fileinput-new text-center" data-provides="fileinput">--}}
                        {{--<div>--}}
                        {{--<span class="btn btn-raised btn-round btn-default btn-file">--}}
                        {{--<span class="fileinput-new">Select image</span>--}}
                        {{--<span class="fileinput-exists">Change</span>--}}
                        {{--<input type="file" name="avatar"/>--}}
                        {{--</span>--}}
                        {{--<a href="#pablo" class="btn btn-danger btn-round fileinput-exists"--}}
                        {{--data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>--}}
                        {{--</div>--}}
                        {{--</div>--}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{--@push('scripts')--}}
    <script src="https://cloud.tinymce.com/stable/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: 'textarea',
            width: 900,
            height: 300
        });
    </script>
    <script>
        document.getElementById('reset-btn').onclick = function(event){
            event.preventDefault();
            document.getElementById('cert-options').selectedIndex = 0;
        }
    </script>
    <script>
        function show() {
            var pass = document.getElementById('pwd');
            pass.setAttribute('type', 'text');
        }
        function hide() {
            var pass = document.getElementById('pwd');
            pass.setAttribute('type', 'password');
        }
        var pwShown = 0;
        document.getElementById("eye").addEventListener("click", function () {
            if (pwShown == 0) {
                pwShown = 1;
                show();
            } else {
                pwShown = 0;
                hide();
            }
        }, false);
    </script>
    {{--@endpush--}}
@endsection
