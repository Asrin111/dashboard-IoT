@extends('admin.layouts.base')
@section('title', 'Project Detail')
@section('content')

<h1 class="h3 mb-4 text-gray-800">Project Detail</h1>

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
                <p class="mb-1"><strong>Status Parkir Saat Ini:</strong></p>
                <h5 id="status-pintu-text">-</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success shadow mb-4 text-center">
            <div class="card-body">
                <h5 class="card-title">Sisa Slot Parkir</h5>
                <h1 id="slot-tersisa" style="font-size: 3rem;">-</h1>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between">
        <h5 class="m-0 font-weight-bold text-primary">Grafik Aktivitas Kendaraan</h5>
    </div>
    <div class="card-body">
        <div class="chart-container" style="height: 400px;">
            <canvas id="sensorChart"></canvas>
        </div>
    </div>
</div>

<!-- TABEL RIWAYAT PARKIR -->
<div class="card shadow mb-4 mt-4">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">Riwayat Parkir</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Tanggal & Waktu</th>
                        <th>Sisa Slot</th>
                        <th>Arah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y H:i:s') }}</td>
                        <td>{{ $log->slots }}</td>
                        <td>
                            @if($log->direction == 'in')
                            <span class="badge badge-success">Masuk</span>
                            @elseif($log->direction == 'out')
                            <span class="badge badge-info">Keluar</span>
                            @else
                            <span class="badge badge-secondary">Tidak Diketahui</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">Belum ada data parkir</td>
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
                label: 'Sisa Slot Parkir',
                borderColor: 'green',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                data: [],
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            scales: {
                y: {
                    min: 0,
                    max: 5, // Maks slot parkir
                    ticks: {
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Jumlah Slot Tersisa'
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
                        label: context => `Slot Tersisa: ${context.raw}`
                    }
                }
            }
        }
    });


    const client = mqtt.connect('wss://broker.emqx.io:8084/mqtt');
    const statusEl = document.getElementById('device-status');
    const expectedTopic = `iot/zeta49kunix/status/Parking/{{ $device->device_id }}`;
    let statusTimeout;

    client.on('connect', () => {
        client.subscribe(expectedTopic);
        client.subscribe(`iot/zeta49kunix/Parking/{{ $device->device_id }}`);
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
            const slotTersisa = payload.slots;
            const arah = payload.direction;
            const waktu = new Date().toLocaleTimeString();

            // Update teks info
            document.getElementById('status-pintu-text').textContent =
                `Kendaraan ${arah === 'in' ? 'Masuk' : 'Keluar'}`;
            document.getElementById('slot-tersisa').textContent = slotTersisa;

            // Update grafik
            // const status = arah === 'in' ? 1 : 0;
            sensorChart.data.labels.push(waktu);
            sensorChart.data.datasets[0].data.push(slotTersisa);

            if (sensorChart.data.labels.length > 5) {
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