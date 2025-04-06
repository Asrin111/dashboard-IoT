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
        $server   = 'broker.emqx.io';  // Ganti broker jika beda
        $port     = 1883;                 // Port MQTT
        $clientId = 'dashboard-iot-' . uniqid();
        $username = null;                 // Diisi kalau broker pakai auth
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

        // Subscribe ke topic
        $mqtt->subscribe('iot/smartHome0oa8gdj/smartHome/access_status', function (string $topic, string $message) {
            Log::info("Pesan MQTT diterima: [$topic] $message");

            // Simpan ke database
            MqttData::create([
                'topic' => $topic,
                'message' => $message,
            ]);
        }, 0);

        // Mulai loop listening
        $mqtt->loop(true);
    }
}