@extends('layouts.dashboard.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header card-header-danger">
                        <h4 class="card-title">Certificate: {{$certificate->name}}</h4>
                    </div>
                    <div class="card-body">
                        @include('alert')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-right">
                                    <img src="{{ $certificate->logo }}" class="img-rounded" width="45px"
                                         alt="...">
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('admin.certificates.update', $certificate->id) }}" method="post"
                              enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Name</label>
                                        <input name='name' type="text" class="form-control"
                                               value="{{$certificate->name}}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="bmd-label-floating">Logo</label>
                                    <input name='logo' type="file" class="form-control"
                                           value="{{$certificate->logo}}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary pull-right">Update Certificate</button>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
