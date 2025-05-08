@extends('admin.layouts.base')
@section('title', 'Project Detail')
@section('content')

<h1 class="h3 mb-4 text-gray-800">Project Detail - Device: {{ $device->device_id }}</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between">
        <h5 class="m-0 font-weight-bold text-primary">
            @if($device->tipe == 'DoorLock')
            SmartHome - DoorLock
            @elseif($device->tipe == 'Parking')
            SmartCity - Parking
            @else
            Unknown Device
            @endif
        </h5>
    </div>
    <div class="card-body">
        <p><strong>Status:</strong> <span id="device-status" class="text-danger">Offline</span></p>
        <p><strong>Status Pintu Saat Ini:</strong> <span id="status-pintu-text">-</span></p>

        <hr>

        <!-- Menampilkan data atau grafik yang sesuai berdasarkan tipe device -->
        @if($device->tipe == 'DoorLock')
        <h5>Data Pintu</h5>
        <div class="chart-container" style="height: 400px;">
            <canvas id="dataChart"></canvas>
        </div>
        <!-- Grafik atau data terkait smart home -->
        @elseif($device->tipe == 'Parking')
        <h5>Data Parkir</h5>
        <div class="chart-container" style="height: 400px;">
            <canvas id="dataChart"></canvas>
        </div>
        <!-- Grafik atau data terkait smart city -->
        @else
        <p>Tipe device tidak dikenali.</p>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const deviceId = "{{ $device->device_id }}";
    const deviceType = "{{ $device->tipe }}";

    if (!deviceId || !deviceType) {
        console.error('Device ID atau Tipe tidak valid!');
        return;
    }

    const statusTextEl = document.getElementById('status-pintu-text');
    const statusIndicatorEl = document.getElementById('device-status');

    // Mapping label berdasarkan tipe device
    const deviceConfig = {
        DoorLock: {
            label: 'Status Pintu (0 = Tertutup, 1 = Terbuka)',
            onText: 'Terbuka',
            offText: 'Tertutup',
            match: msg => msg.includes("GRANTED")
        },
        Parking: {
            label: 'Status Parkir (0 = Kosong, 1 = Terisi)',
            onText: 'Terisi',
            offText: 'Kosong',
            match: msg => msg.includes("TERISI") || msg === "1"
        }
    };

    const config = deviceConfig[deviceType] || {
        label: 'Status Tidak Dikenal',
        onText: 'Aktif',
        offText: 'Nonaktif',
        match: () => false
    };

    // Inisialisasi Chart.js
    const ctx = document.getElementById('dataChart').getContext('2d');
    const dataChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: config.label,
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
                        callback: value => value === 1 ? config.onText : config.offText
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
                        label: context => context.raw === 1 ? config.onText : config.offText
                    }
                }
            }
        }
    });

    // Inisialisasi koneksi MQTT
    const client = mqtt.connect('wss://broker.emqx.io:8084/mqtt');
    let statusTimeout;

    client.on('connect', function() {
        console.log('Terhubung ke MQTT broker!');
        const topic = `iot/${deviceId}`;

        client.subscribe(topic, function(err) {
            if (err) {
                console.error('Gagal subscribe:', err);
            } else {
                console.log('Subscribed ke topik:', topic);
            }
        });
    });

    client.on('message', function(topic, message) {
        const msg = message.toString().toUpperCase();
        console.log('Pesan diterima:', topic, msg);

        // Update status online
        updateStatusIndicator(true);

        // Reset offline timeout
        clearTimeout(statusTimeout);
        statusTimeout = setTimeout(() => updateStatusIndicator(false), 5000);

        // Interpretasi status berdasarkan tipe device
        const status = config.match(msg) ? 1 : 0;
        statusTextEl.textContent = status === 1 ? config.onText : config.offText;

        // Update grafik
        const currentTime = new Date().toLocaleTimeString();
        dataChart.data.labels.push(currentTime);
        dataChart.data.datasets[0].data.push(status);

        if (dataChart.data.labels.length > 20) {
            dataChart.data.labels.shift();
            dataChart.data.datasets[0].data.shift();
        }

        dataChart.update();
    });

    client.on('error', error => console.error('MQTT Error:', error));

    client.on('close', function() {
        console.warn('MQTT connection closed');
        updateStatusIndicator(false);
    });

    function updateStatusIndicator(isOnline) {
        statusIndicatorEl.textContent = isOnline ? 'Online' : 'Offline';
        statusIndicatorEl.classList.toggle('text-success', isOnline);
        statusIndicatorEl.classList.toggle('text-danger', !isOnline);
    }
});
</script>
@endsection