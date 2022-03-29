@extends('layouts.dashboard.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header card-header-danger">
                        <h4 class="card-title">New Link</h4>
                    </div>
                    <div class="card-body">
                        @include('alert')
                        <form action="{{ route('admin.links.store') }}" method="post">
                            @csrf

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Title</label>
                                        <input name='title' type="text" class="form-control"
                                               value="{{ old('title') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">URL</label>
                                        <input name='url' type="text" class="form-control"
                                               value="{{ old('url') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Icon**</label>
                                        <input name='icon' type="text" class="form-control"
                                               value="{{ old('icon') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Order Number</label>
                                        <input name='order_number' type="number" class="form-control"
                                               value="{{ old('order_number') ? : ($order_number+1) }}"
                                               min="1" max="999" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    **icons: team, pc, improvments, chill, library, photo, blog, redmine, days, faq or
                                    or any <a href="https://fontawesome.com/v4/icons/">other</a>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary pull-right">Create Link</button>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

