@extends('admin.layouts.base')
@section('title', 'Tambah Project')
@section('content')

<h1 class="h3 mb-4 text-gray-800">Tambah Project</h1>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ route('project.store') }}" method="POST">
            @csrf

            <div class="form-group mt-3">
                <label for="device_id">ID Device</label>
                <input type="text" name="device_id" id="device_id" class="form-control" required>
            </div>

            <div class="form-group mt-3">
                <label for="tipe">Tipe Proyek</label>
                <select name="tipe" id="tipe" class="form-control" required>
                    <option value="">-- Pilih Tipe Proyek --</option>
                    <option value="DoorLock">DoorLock</option>
                    <option value="Parking">Parking</option>
                    <option value="Plants">Plants</option>
                </select>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary"> Tambah Proyek </button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>

@endsection