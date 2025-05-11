@extends('admin.layouts.base')
@section('title', 'Project Detail')
@section('content')

<h1 class="h3 mb-4 text-gray-800">Project Detail - Device: {{ $device->device_id }}</h1>

<div class="row">
    <div class="col-md-4">
        <div class="card border-left-primary shadow mb-4">
            <div class="card-body">
                <p class="mb-1"><strong>Status</strong></p>
                <h5 id="device-status" class="text-danger"></h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success shadow mb-4 text-center">
            <div class="card-body">
                <h5 class="card-title">Suhu</h5>
                <h1 id="suhu" style="font-size: 3rem;"></h1>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success shadow mb-4 text-center">
            <div class="card-body">
                <h5 class="card-title">Kelembapan</h5>
                <h1 id="kelembapan" style="font-size: 3rem;"></h1>
            </div>
        </div>
    </div>
</div>

<!-- GRAFIK -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h5 class="m-0 font-weight-bold text-primary">Grafik Data Sensor</h5>
            </div>
            <div class="card-body">
                <canvas id="sensorChart" style="height: 400px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- TABEL -->
<div class="card shadow mb-4 mt-4">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">Riwayat Data Sensor</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Suhu (°C)</th>
                        <th>Kelembapan (%)</th>
                        <th>Kadar Air Tanah (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->logged_at }}</td>
                        <td>{{ number_format($log->suhu, 1) }}</td>
                        <td>{{ number_format($log->kelembapan, 1) }}</td>
                        <td>{{ number_format($log->moisture, 1) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data sensor</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Variabel untuk menyimpan data dari MQTT
    const labels = []; // Waktu log
    const suhuData = []; // Data suhu
    const kelembapanData = []; // Data kelembapan
    const moistureData = []; // Data kadar air tanah

    // Konfigurasi grafik menggunakan Chart.js
    const ctx = document.getElementById('sensorChart').getContext('2d');

    const sensorChart = new Chart(ctx, {
        type: 'line', // Jenis grafik line
        data: {
            labels: labels, // Label berdasarkan waktu
            datasets: [{
                    label: 'Suhu (°C)',
                    data: suhuData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: false,
                    tension: 0.1
                },
                {
                    label: 'Kelembapan (%)',
                    data: kelembapanData,
                    borderColor: 'rgba(153, 102, 255, 1)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    fill: false,
                    tension: 0.1
                },
                {
                    label: 'Kadar Air Tanah (%)',
                    data: moistureData,
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    fill: false,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Waktu'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Nilai'
                    },
                    min: 0 // Set minimum value to 0 for better readability
                }
            }
        }
    });

    // Setup MQTT connection
    const client = mqtt.connect('wss://broker.emqx.io:8084/mqtt');
    const statusEl = document.getElementById('device-status');
    const expectedTopic = `iot/zeta49kunix/status/Plants/{{ $device->device_id }}`;

    client.on('connect', () => {
        client.subscribe(expectedTopic);
        client.subscribe(`iot/zeta49kunix/Plants/{{ $device->device_id }}`);
    });

    client.on('close', () => {
        console.log('Koneksi MQTT terputus');
        if (statusEl) {
            statusEl.textContent = 'Offline';
            statusEl.classList.replace('text-success', 'text-danger');
        }
    });

    client.on('message', (topic, message) => {
        const payload = message.toString();

        if (topic === expectedTopic) {
            const isOnline = payload.trim().toLowerCase() === 'online';
            if (statusEl) {
                statusEl.textContent = isOnline ? 'Online' : 'Offline';
                statusEl.classList.toggle('text-success', isOnline);
                statusEl.classList.toggle('text-danger', !isOnline);
            }
            return;
        }

        try {
            const data = JSON.parse(payload);
            const suhu = data.suhu;
            const kelembapan = data.kelembapan;
            const moisture = data.moisture;
            const timestamp = new Date().toLocaleString();

            // Update tampilan suhu dan kelembapan di DOM
            document.getElementById('suhu').textContent = suhu.toFixed(1) + '°C';
            document.getElementById('kelembapan').textContent = kelembapan.toFixed(1) + '%';

            // Menambahkan data baru ke grafik
            sensorChart.data.labels.push(timestamp);
            sensorChart.data.datasets[0].data.push(suhu); // Update suhu
            sensorChart.data.datasets[1].data.push(kelembapan); // Update kelembapan
            sensorChart.data.datasets[2].data.push(moisture); // Update kadar air tanah

            // Menghapus data lama jika lebih dari 100 data (untuk menjaga performa)
            if (sensorChart.data.labels.length > 10) {
                sensorChart.data.labels.shift();
                sensorChart.data.datasets.forEach(dataset => dataset.data.shift());
            }

            // Memperbarui grafik
            sensorChart.update();
        } catch (err) {
            console.error("Gagal parsing JSON sensor:", err);
        }
    });

    client.on('error', error => console.error('MQTT Error:', error));
});
</script>


@endsection