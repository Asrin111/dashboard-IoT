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
                        <td><span id="status-1" class="text-danger">Offline</span></td>
                        <td>
                            <a href="{{ route('project.detail', ['id' => 1]) }}" class="btn btn-success btn-sm"
                                style="margin-right: 10px">Detail</a>
                            <a href="#" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                    <tr>
                        <td>SmartCity</td>
                        <td>2025/03/19 10:20</td>
                        <td><span id="status-2" class="text-danger">Offline</span></td>
                        <td>
                            <a href="{{ route('project.detail.city', ['id' => 2]) }}"
                                class="btn btn-success btn-sm">Detail</a>
                            <a href="#" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MQTT JS -->
<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const client = mqtt.connect("wss://broker.emqx.io:8084/mqtt");

    const projects = {
        1: "iot/smartHome0oa8gdj/smartHome/access_status", // topik SmartHome
        2: "iot/smartCity84jgs90/smartCity" // topik SmartCity
    };

    const statusTimeouts = {};

    client.on("connect", function() {
        console.log("Terhubung ke MQTT broker!");

        Object.entries(projects).forEach(([id, topic]) => {
            client.subscribe(topic, function(err) {
                if (!err) {
                    console.log("Subscribed ke topik:", topic);
                } else {
                    console.error("Gagal subscribe ke topik:", topic);
                }
            });
        });
    });

    client.on("message", function(topic, message) {
        console.log("Pesan:", topic, message.toString());

        Object.entries(projects).forEach(([id, projectTopic]) => {
            if (topic === projectTopic) {
                const el = document.getElementById("status-" + id);
                el.textContent = "Online";
                el.classList.remove("text-danger");
                el.classList.add("text-success");

                // Auto set Offline jika tidak ada data baru dalam 5 detik
                clearTimeout(statusTimeouts[id]);
                statusTimeouts[id] = setTimeout(() => {
                    el.textContent = "Offline";
                    el.classList.remove("text-success");
                    el.classList.add("text-danger");
                }, 5000);
            }
        });
    });

    client.on("error", function(err) {
        console.error("MQTT Error:", err);
    });
});
</script>

@endsection