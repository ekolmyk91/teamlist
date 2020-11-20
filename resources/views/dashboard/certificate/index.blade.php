@extends('layouts.dashboard.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('admin.certificates.create')}}" class="btn btn-primary">Add Certificate</a>
                <div class="card">
                    <div class="card-header card-header-danger">
                        <h4 class="card-title ">Certificates</h4>
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
                                    Logo
                                </th>
                                <th>Actions</th>
                                </thead>
                                <tbody>

                                @foreach ($certificates as $certificate)
                                    <tr>
                                        <td>
                                            {{$certificate->id}}
                                        </td>
                                        <td>
                                            {{$certificate->name}}
                                        </td>
                                        <td>
                                            <img src="{{ $certificate->logo }}" class="img-rounded" width="45px"
                                                 alt="...">
                                        </td>
                                        <td class="td-actions">
                                            <a href="{{ route('admin.certificates.edit', $certificate->id)}}"
                                               class="btn btn-success"><i class="material-icons">edit</i></a>
                                            <form action="{{ route('admin.certificates.destroy', $certificate->id)}}"
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

