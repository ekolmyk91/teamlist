@extends('layouts.dashboard.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.off_time_type.create')}}" class="btn btn-primary">Add Type</a>
            <div class="card">
                <div class="card-header card-header-danger">
                    <h4 class="card-title ">Off-Time Types</h4>
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
                            <th>
                                Days per year
                            </th>
                            <th>Actions</th>
                            </thead>
                            <tbody>

                            @foreach ($types as $type)
                                <tr>
                                    <td>
                                        {{$type->id}}
                                    </td>
                                    <td>
                                        {{$type->name}}
                                    </td>
                                    <td>
                                        {{$type->days_per_year}}
                                    </td>
                                    <td class="td-actions">
                                       <a href="{{ route('admin.off_time_type.edit', $type->id)}}" class="btn btn-success"><i class="material-icons">edit</i></a>
                                       <form action="{{ route('admin.off_time_type.destroy', $type->id)}}" method="post">
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

