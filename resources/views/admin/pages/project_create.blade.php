@extends('admin.layouts.base')
@section('title', 'Tambah Project')
@section('content')

<h1 class="h3 mb-4 text-gray-800">Tambah Project</h1>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="#" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Nama Project</label>
                <input type="text" name="name" id="name" class="form-control" required placeholder="Contoh: SmartHome">
            </div>

            <div class="form-group mt-3">
                <label for="topic">Topik MQTT</label>
                <input type="text" name="topic" id="topic" class="form-control" required
                    placeholder="Contoh: iot/smartHomexxxx/device">
            </div>

            <div class="form-group mt-3">
                <label for="device_type">Tipe Project</label>
                <select name="device_type" id="device_type" class="form-control" required>
                    <option value="">-- Pilih Tipe Project --</option>
                    <option value="smarthome">SmartHome</option>
                    <option value="smartcity">SmartCity</option>
                    <option value="smartagriculture">Smart Agriculture</option>
                    <option value="smartparking">Smart Parking</option>
                    <option value="smartoffice">Smart Office</option>
                    <!-- Bisa ditambah tipe lainnya -->
                </select>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary" disabled>🔒 Simpan (Coming Soon)</button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>

@endsection