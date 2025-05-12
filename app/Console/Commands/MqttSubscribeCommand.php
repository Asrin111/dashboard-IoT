<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\PlantsLog;
use App\Models\DoorlockLog;
use Illuminate\Support\Facades\Log;

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

        $this->info('✅ Terhubung ke MQTT broker!');

        $baseTopic = 'iot/zeta49kunix/+/' . '+';

        $mqtt->subscribe($baseTopic, function (string $topic, string $message) {
            $parts = explode('/', $topic);
            $project = $parts[2] ?? null;
            $deviceId = $parts[3] ?? null;

            Log::info("[DATA MASUK] Proyek: $project | Device: $deviceId | Payload: $message");
            $this->info("[MQTT] Proyek: $project | Device: $deviceId | Payload: $message");

            $data = json_decode($message, true);

            if (!is_array($data)) {
                Log::warning("⚠️ Gagal decode JSON: $message");
                $this->warn("⚠️ Gagal decode JSON: $message");
                return;
            }

            switch (strtolower($project)) {
                case 'plants':
                    PlantsLog::create([
                        'device_id'  => $deviceId,
                        'suhu'       => $data['suhu'] ?? 0,
                        'kelembapan' => $data['kelembapan'] ?? 0,
                        'moisture'   => $data['moisture'] ?? 0,
                        'logged_at'  => now(),
                    ]);
                    Log::info("✅ Data Plants berhasil disimpan.");
                    $this->info("✅ Plants: Data disimpan.");
                    break;

                case 'doorlock':
                    DoorlockLog::create([
                        'device_id' => $deviceId,
                        'akses'     => $data['akses'] ?? 'unknown',
                    ]);
                    Log::info("✅ Data Doorlock berhasil disimpan.");
                    $this->info("✅ Doorlock: Data disimpan.");
                    break;

                default:
                    Log::warning("⚠️ Proyek tidak dikenali: $project");
                    $this->warn("⚠️ Proyek tidak dikenali: $project");
                    break;
            }
        }, 0);

        $mqtt->loop(true);
    }
}