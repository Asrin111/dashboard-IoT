<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'IndexPage'])->name('dashboard');
Route::get('/project/{id}/detail', [ProjectController::class, 'detail'])->name('project.detail');
Route::get('/project/{id}/detail-city', [ProjectController::class, 'detailCity'])->name('project.detail.city');
Route::get('/project/create', [ProjectController::class, 'create'])->name('project.create');