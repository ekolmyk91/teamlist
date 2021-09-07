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
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('admin.members.create')}}" class="btn btn-primary">Add Member</a>
                </div>
                <div class="col-md-6">
                    <form class="navbar-form" action="{{ route('admin.members.search') }}" method="POST">
                        @csrf
                        <div class="input-group no-border">
                            <input type="text" name="query" class="form-control" placeholder="Search...">
                            <button type="submit" class="btn btn-white btn-round btn-just-icon">
                                <i class="material-icons">search</i>
                                <div class="ripple-container"></div>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header card-header-danger">
                    <h4 class="card-title ">Members</h4>
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
                                Avatar
                            </th>
                            <th>
                                Name
                            </th>
                            <th>
                                Surname
                            </th>
                            <th>
                                Email
                            </th>
                            <th>Actions</th>
                            </thead>
                            <tbody>
                            @foreach ($members as $member)
                            <tr>
                                <td>
                                    {{$member->user_id}}
                                </td>
                                <td>
                                    @if($member->user->avatar)
                                        <div class="card-profile-preview">
                                            <div class="card-avatar-preview">
                                                <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                                    <div class="fileinput-new thumbnail img-raised">
                                                        <img src="{{ $member->user->avatar }}" width="45px" heigh="45px"  alt="...">
                                                    </div>
                                                    <div class="fileinput-preview fileinput-exists thumbnail img-raised"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    {{$member->name}}
                                </td>
                                <td>
                                    {{$member->surname}}
                                </td>
                                <td>
                                    {{$member->email}}
                                </td>
                                <td class="td-actions">
                                    {{--<button type="button" rel="tooltip" class="btn btn-info">--}}
                                        {{--<i class="material-icons">person</i>--}}
                                    {{--</button>--}}
                                    {{--<a href="{{ route('admin.members.edit', $member->id)}}" class="btn btn-success"><i class="material-icons">edit</i></a>--}}
                                    <a href="{{ route('admin.members.edit', $member->user_id)}}" class="btn btn-success"><i class="material-icons">edit</i></a>
                                    <form action="{{ route('admin.members.destroy', $member->user_id)}}" method="post">
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
                @if ($members->total() > $members->count())
                    <div id="block-pagination">
                        <nav aria-label="Page navigation example">
                            <ul class="pagination">
                                {{$members->links()}}
                            </ul>
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

