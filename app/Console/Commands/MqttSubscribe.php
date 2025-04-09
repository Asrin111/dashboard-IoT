<?php

// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use PhpMqtt\Client\MqttClient;
// use PhpMqtt\Client\ConnectionSettings;
// use App\Models\MqttData;
// use Illuminate\Support\Facades\Log;

// class MqttSubscribe extends Command
// {
//     protected $signature = 'mqtt:subscribe';
//     protected $description = 'Subscribe ke MQTT broker dan simpan data ke database';

//     public function handle()
//     {
//         $server   = 'broker.emqx.io'; // ganti broker sesuai yang kamu pakai
//         $port     = 1883;
//         $clientId = 'laravel_subscriber_' . uniqid();
//         $username = null; 
//         $password = null;

//         $connectionSettings = (new ConnectionSettings)
//             ->setUsername($username)
//             ->setPassword($password);

//         $mqtt = new MqttClient($server, $port, $clientId);

//         $mqtt->connect($connectionSettings, true);

//         $this->info('Connected to MQTT broker, subscribing...');

//         $mqtt->subscribe('iot/smartHome0oa8gdj/smartHome/access_status', function (string $topic, string $message) {
//             Log::info("Pesan baru diterima: {$message} di topik {$topic}");

//             MqttData::create([
//                 'topic' => $topic,
//                 'message' => $message,
//             ]);
//         }, 0);

//         $mqtt->loop(true);
//     }
// }