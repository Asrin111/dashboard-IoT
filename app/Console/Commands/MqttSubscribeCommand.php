<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\MqttData;
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
        $username = null;
        $password = null;

        $mqtt = new MqttClient($server, $port, $clientId);

        $connectionSettings = (new ConnectionSettings())
            ->setUsername($username)
            ->setPassword($password)
            ->setKeepAliveInterval(60)
            ->setLastWillTopic('dashboard/lastwill')
            ->setLastWillMessage('Client disconnected unexpectedly')
            ->setLastWillQualityOfService(0);

        $mqtt->connect($connectionSettings, true);

        $this->info('Terhubung ke MQTT broker!');

        // Daftar topik dan jenis perangkat
        $topics = [
            'iot/smartHome0oa8gdj/smartHome/access_status' => 'smarthome',
            'iot/smartCity84jgs90/smartCity' => 'smartcity',
        ];

        foreach ($topics as $pattern => $deviceType) {
            $mqtt->subscribe($pattern, function (string $topic, string $message) use ($deviceType) {
                // Log ke file
                Log::info("[$deviceType] Pesan MQTT diterima: [$topic] $message");

                // Log ke terminal
                echo "[$deviceType] Pesan MQTT diterima: [$topic] $message\n";

                // Simpan ke database
                MqttData::create([
                    'topic' => $topic,
                    'message' => $message,
                    'device_type' => $deviceType,
                ]);
            }, 0);
        }

        // Jalankan loop untuk terus menerima pesan
        $mqtt->loop(true);
    }
}