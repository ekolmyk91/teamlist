@extends('layouts.dashboard.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header card-header-danger">
                        <h4 class="card-title">New Solution</h4>
                    </div>
                    <div class="card-body">
                        @include('alert')
                        <form action="{{ route('admin.solutions.store') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Title</label>
                                        <input name='title' type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Content</label>
                                        <div class="form-group">
                                            <textarea name="content" class="form-control" rows="5"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Categories</label>
                                        <select name='tags' class="form-control selectpicker" data-style="btn btn-link" id="exampleFormControlSelect1">
                                            <option value="">-- Select --</option>
                                            @foreach($categories as $category)
                                                <option value="{{$category->id}}">{{$category->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Tags</label>
                                        <select name='tags' class="form-control selectpicker" data-style="btn btn-link" id="exampleFormControlSelect1">
                                            <option value="">-- Select --</option>
                                            @foreach($tags as $tag)
                                                <option value="{{$tag->id}}">{{$tag->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="file" class="form-control-file" name="archive" id="archiveFile" aria-describedby="fileHelp">
                                    <small id="fileHelp" class="form-text text-muted">Please upload a valid image file. Size of image should not be more than 2MB.</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Active</label>
                                        <div class="form-group">
                                            <input type="checkbox" name="active">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary pull-right">Create Solution</button>
                            <div class="clearfix"></div>
                        </form>
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
            } );


        </script>
    {{--@endpush--}}
@endsection

