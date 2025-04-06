@extends('admin.layouts.base')
@section('title', 'Dashboard')
@section('content')

<h1 class="h3 mb-4 text-gray-800">Dashboard</h1>

<!-- Tabel List Device -->
<div class="card shadow mb-4 mt-4">
    <div class="card-header py-3 d-flex justify-content-between">
        <h5 class="m-0 font-weight-bold text-primary">List Project</h5>
        <a href="#" class="btn btn-success">Tambah Project</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nama Project</th>
                        <th>Created At</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>SmartHome</td>
                        <td>2025/03/20 14:30</td>
                        <td>Last Online: 2025/03/20 15:00</td>
                        <td>
                            <a href="{{ route('project.detail', ['id' => 1]) }}" class="btn btn-success btn-sm"
                                style="margin-right: 10px">Detail</a>
                            <a href="#" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                    <tr>
                        <td>SmartCity</td>
                        <td>2025/03/19 10:20</td>
                        <td>Offline</td>
                        <td>
                            <a href="{{ route('project.detail', ['id' => 2]) }}" class="btn btn-success btn-sm"
                                style="margin-right: 10px">Detail</a>
                            <a href="#" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection