<?php

namespace App\Mqtt;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\MqttData; 
use Illuminate\Support\Facades\Log;

class MqttService
{
    protected $client;
    protected $server = 'broker.emqx.io';
    protected $port = 1883;
    protected $clientId;

    public function __construct()
    {
        $this->clientId = 'laravel_mqtt_client_' . rand(1, 9999);
    }

    private function connect()
    {
        $settings = (new ConnectionSettings())
            ->setKeepAliveInterval(60)
            ->setConnectTimeout(5)
            ->setUsername(null)
            ->setPassword(null);

        $this->client = new MqttClient($this->server, $this->port, $this->clientId);
        
        try {
            $this->client->connect($settings, true);
            Log::info("Berhasil terhubung ke MQTT Broker.");
        } catch (\Exception $e) {
            Log::error("Gagal terhubung ke MQTT Broker: " . $e->getMessage());
        }
    }

    public function publish($topic, $message)
    {
        try {
            $this->connect(); // Koneksikan MQTT setiap kali publish
            $this->client->publish($topic, $message, MqttClient::QOS_AT_MOST_ONCE);
            Log::info("Pesan berhasil dikirim ke $topic: $message");
        } catch (\Exception $e) {
            Log::error("Gagal mengirim pesan MQTT: " . $e->getMessage());
        } finally {
            $this->disconnect(); // Pastikan koneksi ditutup setelah publish
        }
    }

    public function disconnect()
    {
        if ($this->client !== null && $this->client->isConnected()) {
            $this->client->disconnect();
            Log::info("Terputus dari MQTT Broker.");
        }
    }

    public function subscribe($topic, $callback)
    {
        $this->connect();
        $this->client->subscribe($topic, function ($topic, $message) use ($callback) {
            Log::info("Menerima pesan dari $topic: $message");

            // Simpan ke database
            MqttData::create([
                'topic' => $topic,
                'message' => $message
            ]);

            $callback($topic, $message);
        }, MqttClient::QOS_AT_MOST_ONCE);

        // Ini WAJIB untuk jalanin loop MQTT
        $this->client->loop(true);
    }
}