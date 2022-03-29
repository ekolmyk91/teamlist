@extends('layouts.dashboard.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('admin.links.create')}}" class="btn btn-primary">Add Link</a>
                <div class="card">
                    <div class="card-header card-header-danger">
                        <h4 class="card-title ">Links</h4>
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
                                    Link
                                </th>
                                <th>
                                    Icon
                                </th>
                                <th>
                                    Order Number
                                </th>
                                <th>Actions</th>
                                </thead>
                                <tbody>

                                @foreach ($links as $link)
                                    <tr>
                                        <td>
                                            {{$link->id}}
                                        </td>
                                        <td>
                                            <a href="{{$link->url}}" target="_blank">{{$link->title}}</a>
                                        </td>
                                        <td>
                                            {{$link->icon}}
                                        </td>
                                        <td>
                                            {{$link->order_number}}
                                        </td>
                                        <td class="td-actions">
                                            <a href="{{ route('admin.links.edit', $link->id)}}"
                                               class="btn btn-success"><i class="material-icons">edit</i></a>
                                            <form action="{{ route('admin.links.destroy', $link->id)}}"
                                                  method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" rel="tooltip" class="btn btn-danger"
                                                        type="submit">
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

