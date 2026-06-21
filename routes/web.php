<?php

use App\Http\Controllers\AlertsController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/',        [DashboardController::class, 'index'])->name('dashboard');
Route::get('/map',     [MapController::class,       'index'])->name('map.operational_map');
Route::get('/alerts',  [AlertsController::class,    'index'])->name('alerts.index');
Route::get('/history', [HistoryController::class,   'index'])->name('history.index');
Route::get('/charts',  [ChartsController::class,    'index'])->name('charts.index');

Route::get('/settings',  [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
