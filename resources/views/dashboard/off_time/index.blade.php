@extends('layouts.dashboard.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-2">
                        <a href="{{ route('admin.off_time.create')}}" class="btn btn-primary">Add Off-Time Item</a>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header card-header-danger">
                        <h4 class="card-title ">Off-Time List</h4>
                    </div>
                    <div class="card-body">
                        @include('alert')
                        <div class="table-responsive">
                            <table class="table">
                                <thead class=" text-primary">
                                <th>ID</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Status</th>
                                </thead>
                                <tbody>
                                @foreach ($offTimeList as $item)
                                    <tr
                                        @if(WAITING_APPROVE_STATUS === $item->status)
                                            style="font-weight:bold"
                                        @endif
                                    >
                                        <td>
                                            {{$item->id}}
                                        </td>
                                        <td>
                                            {{$item->start_day}}
                                        </td>
                                        <td>
                                            {{$item->end_day}}
                                        </td>
                                        <td>
                                            {{$item->member->name}} {{$item->member->surname}}
                                        </td>
                                        <td>
                                            {{$item->offTimeType->name}}
                                        </td>
                                        <td>
                                            {{$item->status}}
                                        </td>
                                        <td class="td-actions">
                                            <a href="{{ route('admin.off_time.edit', $item->id)}}"
                                               class="btn btn-success"><i class="material-icons">edit</i></a>
                                            <form action="{{ route('admin.off_time.destroy', $item->id)}}"
                                                  method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" rel="tooltip" class="btn btn-danger" type="submit"
                                                        onclick="return confirm('Are you sure?')">
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
                    @if ($offTimeList->total() > $offTimeList->count())
                        <div id="block-pagination">
                            <nav aria-label="Page navigation example">
                                <ul class="pagination">
                                    {{$offTimeList->links()}}
                                </ul>
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

