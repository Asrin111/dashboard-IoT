<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Models\MqttData;
use Illuminate\Support\Facades\Log;

class MqttDataController extends Controller
{
    // Menyimpan data MQTT ke database
    public function store(Request $request)
    {
        Log::info("Data diterima dari MQTT: " . json_encode($request->all()));

        $request->validate([
            'topic' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $mqttData = MqttData::create([
                'topic' => $request->topic,
                'message' => $request->message,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'data' => $mqttData
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data MQTT: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data.',
            ], 500);
        }
    }

    // Mengambil semua data MQTT dari database
    public function index()
    {
        $data = MqttData::latest()->limit(10)->get(); // Ambil 10 data terbaru
        
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data',
            'data' => $data
        ]);
    }
}