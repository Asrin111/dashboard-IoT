<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'IndexPage'])->name('dashboard');

Route::get('/project/{id}/detail-city', [ProjectController::class, 'detailCity'])->name('project.detail.city');

Route::get('/project/create', [ProjectController::class, 'create'])->name('project.create');
Route::get('/project/{device_id}', [ProjectController::class, 'detail'])->name('project.detail');

//tampilkan di index
// Route::get('/project/detail/{id}', [ProjectController::class, 'detail'])->name('project.detail');
Route::get('/project/detail/{id}', [ProjectController::class, 'detail'])->name('project.detail');

//tambah project
Route::post('/project/store', [ProjectController::class, 'store'])->name('project.store');

//hapus project di dashboard
Route::delete('/project/{id}/delete', [ProjectController::class, 'destroy'])->name('project.delete');