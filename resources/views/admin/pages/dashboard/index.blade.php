@extends('admin.layouts.base')
@section('title', 'Dashboard')
@section('content')

<h1 class="h3 mb-4 text-gray-800">Dashboard</h1>

<!-- Tabel List Device -->
<div class="card shadow mb-4 mt-4">
    <div class="card-header py-3 d-flex justify-content-between">
        <h5 class="m-0 font-weight-bold text-primary">List Project</h5>
        <a href="{{ route('project.create') }}" class="btn btn-success">Tambah Project</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Device ID</th>
                        <th>Created At</th>
                        <th>Status</th>
                        <th>Tipe Project</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devices as $apake)
                    <tr>
                        <td>{{ $apake->device_id }}</td>
                        <td>{{ $apake->created_at }}</td>
                        <td>
                            <span id="status-{{ $apake->device_id }}" class="text-danger">Offline</span>
                        </td>
                        <td>{{ $apake->tipe }}</td>
                        <td>
                            <a href="{{ route('project.detail', ['id' => $apake->id]) }}" class="btn btn-success btn-sm"
                                style="margin-right: 10px">Detail</a>
                            <form action="{{ route('project.delete', ['id' => $apake->id]) }}" method="POST"
                                style="display:inline;"
                                onsubmit="return confirm('Yakin ingin menghapus project ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection