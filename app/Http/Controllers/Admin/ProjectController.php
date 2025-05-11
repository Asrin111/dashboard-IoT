<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
// use App\Models\MqttData;
use App\Models\PlantsLog;


class ProjectController extends Controller
{
    // ROUTING BERDASARKAN TEMPLATE BLADE dan TAMPILKAN HISTORYCAL DATA
    public function detail($id)
    {
        $device = Device::findOrFail($id);

        switch ($device->tipe) {
            case 'DoorLock':
                // $logs = DoorLockLog::where('device_id', $device->device_id)
                //             ->orderBy('logged_at', 'desc')
                //             ->limit(20)
                //             ->get();
                // return view('admin.pages.project_detail', compact('device', 'logs'));
                return view('admin.pages.project_detail', compact('device'));

            case 'Parking':
                // $logs = ParkingLog::where('device_id', $device->device_id)
                //             ->orderBy('logged_at', 'desc')
                //             ->limit(20)
                //             ->get();
                // return view('admin.pages.project_detail1', compact('device', 'logs'));
                return view('admin.pages.project_detail1', compact('device'));


            case 'Plants':
                $logs = PlantsLog::where('device_id', $device->device_id)
                            ->orderBy('logged_at', 'desc')
                            ->limit(20)
                            ->get();
                return view('admin.pages.project_plants', compact('device', 'logs'));
        }
    }

    // KE LAMAN TAMBAH PROJECT
    public function create()
    {
        return view('admin.pages.project_create');
    }

    // SUBMIT ID DEVICE UNTUK TAMBAH PROJECT
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|unique:devices,device_id',
            'tipe' => 'string'  
        ]);

        Device::create([
            'device_id' => $validated['device_id'],
            'tipe' => $validated['tipe']
        ]);

        return redirect()->route('dashboard')->with('success', 'Project berhasil ditambahkan.');
    }

    // HAPUS PROJECT DI INDEX
    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();

        return redirect()->route('dashboard')->with('success', 'Project berhasil dihapus.');
    }

}