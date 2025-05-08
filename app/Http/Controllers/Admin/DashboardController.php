<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
use App\Models\Device;


class DashboardController extends Controller
{
     public function IndexPage()
     {
      $devices = Device::all();
        return view ('admin.pages.dashboard.index', compact('devices'));
     }
}