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
                        <form action="{{ route('admin.members.store') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Fist Name</label>
                                        <input name='name' type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Last Name</label>
                                        <input name='surname' type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Email</label>
                                        <input name='email' type="email" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-control">Birthday</label>
                                        <input id='datepicker' name='birthday' type="text" class="form-control datetimepicker" value="" placeholder="dd/mm/yyyy"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Phone</label>
                                        <input name='phone_1' type="tel" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Additional Phone</label>
                                        <input name='phone_2' type="tel" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Department</label>
                                        <select name='department' class="form-control selectpicker" data-style="btn btn-link" id="exampleFormControlSelect1">
                                            <option value="">-- Select --</option>
                                            @foreach($departments as $department)
                                                <option value="{{$department->id}}">{{$department->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            {{--<div class="row">--}}
                                {{--<div class="col-md-6">--}}
                                    {{--<label class="bmd-label-floating">Avatar</label>--}}
                                    {{--<input name='avatar' type="file" class="form-control-file" id="avatarFile" aria-describedby="fileHelp">--}}

                                        {{--<input type="file" class="form-control-file" name="avatar" id="avatarFile" aria-describedby="fileHelp">--}}
                                        {{--<small id="fileHelp" class="form-text text-muted">Please upload a valid image file. Size of image should not be more than 2MB.</small>--}}
                                {{--</div>--}}
                            {{--</div>--}}


                            <div class="row">
                                <div class="col-md-6">
                                    <label for="avatar" class="col-md-4 col-form-label text-md-right">{{ __('Avatar') }}</label>
                                    <input id="avatar" type="file" class="form-control" name="avatar">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>About</label>
                                        <div class="form-group">
                                            <textarea name="about" class="form-control" rows="5"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary pull-right">Create Member</button>
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
                selector:'textarea',
                width: 900,
                height: 300
            });
        </script>
        <script>
            <!-- javascript for init -->
            $( function() {
                $( "#datepicker" ).datepicker();
                console.log(111)
            } );


        </script>
    {{--@endpush--}}
@endsection

