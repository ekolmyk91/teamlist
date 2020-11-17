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
                        <form action="{{ route('admin.certificates.update', $certificate->id) }}" method="post">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Name</label>
                                        <input name='name' type="text" class="form-control"
                                               value="{{$certificate->name}}" required>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <img src="{{ $certificate->logo }}" class="img-rounded" width="45px"
                                     alt="...">
                            </div>
                            <div class="row">

                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="logo"
                                           class="col-form-label">{{ __('Logo') }}</label>
                                    <input id="logo" type="file" class="form-control" name="logo">
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

