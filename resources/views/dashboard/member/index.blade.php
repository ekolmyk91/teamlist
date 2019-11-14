@extends('layouts.dashboard.admin.app')


    {{--<td>--}}
        {{--<a href="{{ route('contacts.edit',$contact->id)}}" class="btn btn-primary">Edit</a>--}}
    {{--</td>--}}
    {{--<td>--}}
        {{--<form action="{{ route('contacts.destroy', $contact->id)}}" method="post">--}}
            {{--@csrf--}}
            {{--@method('DELETE')--}}
            {{--<button class="btn btn-danger" type="submit">Delete</button>--}}
        {{--</form>--}}
    {{--</td>--}}

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.members.create')}}" class="btn btn-primary">Add Member</a>
            <div class="card">
                <div class="card-header card-header-danger">
                    <h4 class="card-title ">Members</h4>
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
                            <th>
                                Country
                            </th>
                            <th>
                                City
                            </th>
                            <th>
                                Salary
                            </th>
                            <th>Actions</th>
                            </thead>
                            <tbody>
                            @foreach ($members as $member)
                            <tr>
                                <td>
                                    {{$member->id}}
                                </td>
                                <td>
                                    {{$member->created_at}}
                                </td>
                                <td>
                                    Niger
                                </td>
                                <td>
                                    Oud-Turnhout
                                </td>
                                <td class="text-primary">
                                    $36,738
                                </td>
                                <td class="td-actions">
                                    <button type="button" rel="tooltip" class="btn btn-info">
                                        <i class="material-icons">person</i>
                                    </button>
                                    <button type="button" rel="tooltip" class="btn btn-success">
                                        <i class="material-icons">edit</i>
                                    </button>
                                    <button type="button" rel="tooltip" class="btn btn-danger">
                                        <i class="material-icons">close</i>
                                    </button>
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

