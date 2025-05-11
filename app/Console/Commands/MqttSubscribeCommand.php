<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use PhpMqtt\Client\MqttClient;
    use PhpMqtt\Client\ConnectionSettings;
    // use App\Models\MqttData;
    use App\Models\PlantsLog;
    use Illuminate\Support\Facades\Log;

    // INI UNTUK SIMPAN DI DATABASE SAJA
    // NA DENGAR KI, BARU NANTI NA SIMPAN KE DATABASE. JADI UNTUK STATUS TIDAK PERLU DISETTING DISINI
    class MqttSubscribeCommand extends Command
    {
        protected $signature = 'mqtt:subscribe';
        protected $description = 'Subscribe to MQTT topics and store messages to database';

        public function handle()
        {
            $server   = 'broker.emqx.io';
            $port     = 1883;
            $clientId = 'dashboard-iot-' . uniqid();

            $mqtt = new MqttClient($server, $port, $clientId);

            $connectionSettings = (new ConnectionSettings())
                ->setKeepAliveInterval(60)
                ->setLastWillTopic('dashboard/lastwill')
                ->setLastWillMessage('Client disconnected unexpectedly')
                ->setLastWillQualityOfService(0);

            $mqtt->connect($connectionSettings, true);

            $this->info('Terhubung ke MQTT broker!');

            $baseTopic = 'iot/zeta49kunix/+/' . '+';

            $mqtt->subscribe($baseTopic, function (string $topic, string $message) {
                $parts = explode('/', $topic);
                $project = $parts[2] ?? null;
                $deviceId = $parts[3] ?? null;

                Log::info("[DATA] Proyek: $project | Device: $deviceId | Payload: $message");

                $data = json_decode($message, true);

                if ($data) {
                    // Simpan ke DB atau sesuaikan berdasarkan proyek
                    switch ($project) {
                        case 'Plants':
                            PlantsLog::create([
                                'device_id'  => $deviceId,
                                'suhu'       => $data['suhu'] ?? 0,
                                'kelembapan' => $data['kelembapan'] ?? 0,
                                'moisture'   => $data['moisture'] ?? 0,
                                'logged_at'  => now(),
                            ]);
                    }
                }
            }, 0);

            // Jalankan loop untuk terus menerima pesan
            $mqtt->loop(true);
        }
    }