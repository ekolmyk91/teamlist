@extends('layouts.dashboard.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.departments.create')}}" class="btn btn-primary">Add Department</a>
            <div class="card">
                <div class="card-header card-header-danger">
                    <h4 class="card-title ">Departments</h4>
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
                                Name
                            </th>
                            <th>Actions</th>
                            </thead>
                            <tbody>

                            @foreach ($departments as $department)
                                <tr>
                                    <td>
                                        {{$department->id}}
                                    </td>
                                    <td>
                                        {{$department->name}}
                                    </td>
                                    <td class="td-actions">
                                       <a href="{{ route('admin.departments.edit', $department->id)}}" class="btn btn-success"><i class="material-icons">edit</i></a>
                                       <form action="{{ route('admin.departments.destroy', $department->id)}}" method="post">
                                           @csrf
                                           @method('DELETE')
                                           <button type="submit" rel="tooltip" class="btn btn-danger" type="submit">
                                               <i class="material-icons">delete</i>
                                           </button>
                                       </form>
                                    <td>
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

