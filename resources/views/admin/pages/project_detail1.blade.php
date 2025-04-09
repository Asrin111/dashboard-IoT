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
            <canvas id="dataChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('dataChart').getContext('2d');

    const dataChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Status Parkir (1 = Masuk, 0 = Keluar)',
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
                        callback: value => value == 1 ? 'Masuk' : 'Keluar'
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
                        label: context => context.raw == 1 ? 'Masuk' : 'Keluar'
                    }
                }
            }
        }
    });

    const client = mqtt.connect('wss://broker.emqx.io:8084/mqtt');
    let statusTimeout;

    client.on('connect', () => {
        console.log('Terhubung ke MQTT broker!');
        client.subscribe('iot/smartCity84jgs90/smartCity', err => {
            if (!err) {
                console.log('Subscribed ke topik: iot/smartCity84jgs90/smartCity');
            } else {
                console.error('Gagal subscribe:', err);
            }
        });
    });

    client.on('message', (topic, message) => {
        console.log('Pesan diterima:', topic, message.toString());

        // Update status koneksi
        document.getElementById('device-status').textContent = 'Online';
        document.getElementById('device-status').classList.replace('text-danger', 'text-success');

        clearTimeout(statusTimeout);
        statusTimeout = setTimeout(() => {
            document.getElementById('device-status').textContent = 'Offline';
            document.getElementById('device-status').classList.replace('text-success',
                'text-danger');
        }, 5000);

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
            const status = arah === 'in' ? 1 : 0;
            dataChart.data.labels.push(waktu);
            dataChart.data.datasets[0].data.push(status);

            if (dataChart.data.labels.length > 20) {
                dataChart.data.labels.shift();
                dataChart.data.datasets[0].data.shift();
            }

            dataChart.update();
        } catch (err) {
            console.error("Gagal parsing JSON:", err);
        }
    });

    client.on('error', error => console.error('MQTT Error:', error));
    client.on('close', () => {
        console.warn('MQTT connection closed');
        document.getElementById('device-status').textContent = 'Offline';
        document.getElementById('device-status').classList.replace('text-success', 'text-danger');
    });
});
</script>

@endsection