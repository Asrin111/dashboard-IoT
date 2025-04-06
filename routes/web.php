<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MqttDataController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\MqttController;
use Illuminate\Support\Facades\Route;
use App\Mqtt\MqttService;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'IndexPage'])->name('dashboard');
Route::get('/project/{id}/detail', [ProjectController::class, 'detail'])->name('project.detail');
Route::post('/mqtt/publish', [MqttController::class, 'publishMessage']);
Route::get('/mqtt-test', function () {
    $mqtt = new MqttService();
    $mqtt->publish('test/topic', 'Hello from Laravel');
    return "MQTT message sent!";
});
Route::post('/admin/mqtt/store', [MqttDataController::class, 'store']);
Route::get('/admin/mqtt/data', [MqttDataController::class, 'index']);