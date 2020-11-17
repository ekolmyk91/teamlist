@extends('layouts.dashboard.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.solutions.create')}}" class="btn btn-primary">Add Solution</a>
            <div class="card">
                <div class="card-header card-header-danger">
                    <h4 class="card-title ">Solution</h4>
                </div>
                <div class="card-body">
                    @include('alert')
                    <div class="table-responsive">
                        <table class="table">
                            <thead class=" text-primary">
                            <th>
                                ID
                            </th>
                            <th>
                                Title
                            </th>
                            <th>
                                Author
                            </th>
                            <th>
                                Active
                            </th>
                            <th>
                                Updated
                            </th>
                            <th>Actions</th>
                            </thead>
                            <tbody>
                            @foreach ($solutions as $solution)
                            <tr>
                                <td>
                                    {{$solution->id}}
                                </td>
                                <td>
                                    {{$solution->title}}
                                </td>
                                <td>
                                    {{$solution->author}}
                                </td>
                                <td>
                                    {{$solution->active}}
                                </td>
                                <td>
                                    {{$solution->updated_at}}
                                </td>
                                <td class="td-actions">
                                    <a href="{{ route('admin.solutions.edit', $solution->id)}}" class="btn btn-success"><i class="material-icons">edit</i></a>
                                    <form action="{{ route('admin.solutions.destroy', $solution->id)}}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" rel="tooltip" class="btn btn-danger" type="submit">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

