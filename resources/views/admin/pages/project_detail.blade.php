<!-- PROJECT DOOR LOCK -->

@extends('admin.layouts.base')
@section('title', 'Door Lock Detail')
@section('content')

<h1 class="h3 mb-4 text-gray-800">Project Detail - Device: {{ $device->device_id }}</h1>

<div class="row">
    <div class="col-md-4">
        <div class="card border-left-primary shadow mb-4">
            <div class="card-body">
                <p class="mb-1"><strong>Status Koneksi:</strong></p>
                <h5 id="device-status" class="text-danger">Offline</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-left-info shadow mb-4">
            <div class="card-body">
                <p class="mb-1"><strong>Status Pintu Saat Ini:</strong></p>
                <h5 id="status-pintu-text">-</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success shadow mb-4 text-center">
            <div class="card-body">
                <h5 class="card-title">Waktu Terakhir Pintu Dibuka/Ditutup</h5>
                <h5 id="waktu-terakhir" style="font-size: 1rem;">-</h5>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between">
        <h5 class="m-0 font-weight-bold text-primary">Grafik Aktivitas Pintu</h5>
    </div>
    <div class="card-body">
        <div class="chart-container" style="height: 400px;">
            <canvas id="sensorChart"></canvas>
        </div>
    </div>
</div>

<!-- TABEL -->
<div class="card shadow mb-4 mt-4">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">Riwayat Akses Pintu</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Tanggal & Waktu</th>
                        <th>Status Akses</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y H:i:s') }}</td>
                        <td>
                            @if($log->akses == 'Akses Diberikan')
                            <span class="badge badge-success">Akses Diberikan</span>
                            @elseif($log->akses == 'Akses Ditolak!')
                            <span class="badge badge-danger">Akses Ditolak</span>
                            @else
                            <span class="badge badge-secondary">Tidak Dikenal</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center">Belum ada data akses</td>
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
    const ctx = document.getElementById('sensorChart').getContext('2d');

    const sensorChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Status Pintu (1 = Terbuka, 0 = Tertutup)',
                borderColor: 'blue',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                data: [],
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            scales: {
                y: {
                    min: 0,
                    max: 1,
                    ticks: {
                        stepSize: 1,
                        callback: value => value == 1 ? 'Terbuka' : 'Tertutup'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Waktu'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: context => context.raw == 1 ? 'Terbuka' : 'Tertutup'
                    }
                }
            }
        }
    });

    const client = mqtt.connect('wss://broker.emqx.io:8084/mqtt');
    const statusEl = document.getElementById('device-status');
    const expectedTopic = `iot/zeta49kunix/status/DoorLock/{{ $device->device_id }}`;
    let statusTimeout;

    client.on('connect', () => {
        client.subscribe(expectedTopic);
        client.subscribe(`iot/zeta49kunix/DoorLock/{{ $device->device_id }}`);
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
            const payload = JSON.parse(message.toString());
            const statusPintu = payload.doorStatus;
            const waktu = new Date().toLocaleString('id-ID');
            const statusText = statusPintu === 'open' ? 'Terbuka' : 'Tertutup';

            // Update teks info
            document.getElementById('status-pintu-text').textContent = `Pintu ${statusText}`;
            document.getElementById('waktu-terakhir').textContent = `Pintu ${statusText} pada ${waktu}`;


            // Update grafik
            const status = statusPintu === 'open' ? 1 : 0;
            sensorChart.data.labels.push(waktu);
            sensorChart.data.datasets[0].data.push(status);

            if (sensorChart.data.labels.length > 20) {
                sensorChart.data.labels.shift();
                sensorChart.data.datasets[0].data.shift();
            }

            sensorChart.update();
        } catch (err) {
            console.error("Gagal parsing JSON:", err);
        }
    });

    client.on('error', error => console.error('MQTT Error:', error));
});
</script>

@endsection