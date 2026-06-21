<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\SensorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AquaSense — API Routes
|--------------------------------------------------------------------------
|
| GET /api/sensors                        – all sensors + latest reading
| GET /api/sensors/{sensor}               – single sensor
| GET /api/sensors/{sensor}/readings      – reading history (paginated)
|
| GET /api/alerts/active                  – unresolved alerts
| GET /api/alerts                         – full alert history
|
| GET /api/analytics/summary              – fleet-wide aggregated metrics
| GET /api/analytics/by-region            – metrics grouped by region
| GET /api/analytics/timeseries/{sensor}  – time-series for one sensor
|
*/

Route::prefix('sensors')->group(function () {
    Route::get('/',                  [SensorController::class, 'index']);
    Route::get('/{sensor}',          [SensorController::class, 'show']);
    Route::get('/{sensor}/readings', [SensorController::class, 'readings']);
});

Route::prefix('alerts')->group(function () {
    Route::get('/active', [AlertController::class, 'active']);
    Route::get('/',       [AlertController::class, 'index']);
});

Route::prefix('analytics')->group(function () {
    Route::get('/summary',              [AnalyticsController::class, 'summary']);
    Route::get('/by-region',            [AnalyticsController::class, 'byRegion']);
    Route::get('/timeseries/{sensor}',  [AnalyticsController::class, 'timeseries']);
});
