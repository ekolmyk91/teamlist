@extends('layouts.dashboard.admin.app')

{{--@section('content')--}}
    {{--<div class="row">--}}
        {{--<div class="col-sm-12">--}}
            {{--<h1 class="display-3">Contacts</h1>--}}
            {{--<table class="table table-striped">--}}
                {{--<thead>--}}
                {{--<tr>--}}
                    {{--<td>ID</td>--}}
                    {{--<td>Name</td>--}}
                    {{--<td>Email</td>--}}
                    {{--<td>Job Title</td>--}}
                    {{--<td>City</td>--}}
                    {{--<td>Country</td>--}}
                    {{--<td colspan = 2>Actions</td>--}}
                {{--</tr>--}}
                {{--</thead>--}}
                {{--<tbody>--}}
                {{--@foreach($contacts as $contact)--}}
                    {{--<tr>--}}
                        {{--<td>{{$contact->id}}</td>--}}
                        {{--<td>{{$contact->first_name}} {{$contact->last_name}}</td>--}}
                        {{--<td>{{$contact->email}}</td>--}}
                        {{--<td>{{$contact->job_title}}</td>--}}
                        {{--<td>{{$contact->city}}</td>--}}
                        {{--<td>{{$contact->country}}</td>--}}
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
                    {{--</tr>--}}
                {{--@endforeach--}}
                {{--</tbody>--}}
            {{--</table>--}}
            {{--<div>--}}
            {{--</div>--}}
{{--@endsection--}}
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <a href="#0" class="btn btn-primary">Add Member</a>
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

