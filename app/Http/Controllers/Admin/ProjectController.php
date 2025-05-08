<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;

class ProjectController extends Controller
{
    // ROUTING BERDASARKAN TEMPLATE BLADE
    public function detail($id)
    {
         // Mengambil device berdasarkan ID
        $device = Device::findOrFail($id);

        // Mengirimkan data device ke view
        return view('admin.pages.project_detail', compact('device'));
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

        return view('admin.pages.project_create');
    }

    // HAPUS PROJECT DI INDEX
    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();

        return redirect()->route('dashboard')->with('success', 'Project berhasil dihapus.');
    }
}