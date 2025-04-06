<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mqtt\MqttService;
use Illuminate\Support\Facades\Log;

class MqttController extends Controller
{
    protected $mqtt;

    public function __construct()
    {
        $this->mqtt = new MqttService();
    }

    public function publishMessage(Request $request)
    {
        $request->validate([
            'topic' => 'required|string',
            'message' => 'required|string',
        ]);

        $topic = $request->input('topic');
        $message = $request->input('message');

        Log::info("Mengirim MQTT: Topic = $topic, Message = $message");

        try {
            $this->mqtt->publish($topic, $message);
            return response()->json(['message' => "Pesan '$message' dikirim ke MQTT topic '$topic'"]);
        } catch (\Exception $e) {
            Log::error("MQTT Publish gagal: " . $e->getMessage());
            return response()->json(['message' => 'Gagal mengirim pesan MQTT!'], 500);
        }
    }
}