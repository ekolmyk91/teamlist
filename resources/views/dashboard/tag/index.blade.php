@extends('layouts.dashboard.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.tags.create')}}" class="btn btn-primary">Add Tag</a>
            <div class="card">
                <div class="card-header card-header-danger">
                    <h4 class="card-title ">Tags</h4>
                </div>
                <div class="card-body">
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

                            @foreach ($tags as $tag)
                                <tr>
                                    <td>
                                        {{$tag->id}}
                                    </td>
                                    <td>
                                        {{$tag->name}}
                                    </td>
                                    <td class="td-actions">
                                       <a href="{{ route('admin.tags.edit', $tag->id)}}" class="btn btn-success"><i class="material-icons">edit</i></a>
                                       <form action="{{ route('admin.tags.destroy', $tag->id)}}" method="post">
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

