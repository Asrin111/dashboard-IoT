@extends('admin.layouts.base')
@section('title', 'Project Detail')
@section('content')

<h1 class="h3 mb-4 text-gray-800">Project Detail</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between">
        <h5 class="m-0 font-weight-bold text-primary">Device Monitoring</h5>
    </div>
    <div class="card-body">
        <p><strong>Status:</strong> <span id="device-status" class="text-danger">Offline</span></p>
        <p><strong>Status Parkir Saat Ini:</strong> <span id="status-pintu-text">-</span></p>

        <hr>
        <h5>Data Parkir</h5>
        <div class="chart-container" style="height: 400px;">
            <canvas id="dataChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('dataChart').getContext('2d');

    var dataChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Status Pintu (0 = Tertutup, 1 = Terbuka)',
                borderColor: 'green',
                backgroundColor: 'rgba(0,255,0,0.1)',
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
                        callback: function(value) {
                            return value == 1 ? 'Terbuka' : 'Tertutup';
                        }
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
                        label: function(context) {
                            return context.raw == 1 ? 'Terbuka' : 'Tertutup';
                        }
                    }
                }
            }
        }
    });

    const client = mqtt.connect('wss://broker.emqx.io:8084/mqtt');

    let statusTimeout;

    client.on('connect', function() {
        console.log('Terhubung ke MQTT broker!');

        client.subscribe('iot/smartHome0oa8gdj/smartHome/access_status', function(err) {
            if (!err) {
                console.log(
                    'Subscribed ke topik: iot/smartHome0oa8gdj/smartHome/access_status');
            } else {
                console.error('Gagal subscribe:', err);
            }
        });
    });

    client.on('message', function(topic, message) {
        console.log('Pesan diterima:', topic, message.toString());

        // Set status online
        document.getElementById('device-status').textContent = 'Online';
        document.getElementById('device-status').classList.remove('text-danger');
        document.getElementById('device-status').classList.add('text-success');

        // Reset timeout offline
        clearTimeout(statusTimeout);
        statusTimeout = setTimeout(() => {
            document.getElementById('device-status').textContent = 'Offline';
            document.getElementById('device-status').classList.remove('text-success');
            document.getElementById('device-status').classList.add('text-danger');
        }, 5000);

        // Konversi pesan string menjadi nilai status numerik
        var msg = message.toString().toUpperCase();
        var status = msg.includes("GRANTED") ? 1 : 0;

        // Update teks status
        document.getElementById('status-pintu-text').textContent = status === 1 ? 'Terbuka' :
            'Tertutup';

        // Tambahkan data ke grafik
        var currentTime = new Date().toLocaleTimeString();
        dataChart.data.labels.push(currentTime);
        dataChart.data.datasets[0].data.push(status);

        if (dataChart.data.labels.length > 20) {
            dataChart.data.labels.shift();
            dataChart.data.datasets[0].data.shift();
        }

        dataChart.update();
    });

    client.on('error', function(error) {
        console.error('MQTT Error:', error);
    });

    client.on('close', function() {
        console.warn('MQTT connection closed');
        document.getElementById('device-status').textContent = 'Offline';
        document.getElementById('device-status').classList.remove('text-success');
        document.getElementById('device-status').classList.add('text-danger');
    });
});
</script>

@endsection