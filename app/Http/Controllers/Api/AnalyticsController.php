<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sensor;
use App\Models\SensorReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * GET /api/analytics/summary
     * Fleet-wide aggregated metrics from the last reading of each sensor.
     */
    public function summary(): JsonResponse
    {
        // Latest reading per sensor via correlated sub-query
        $latest = SensorReading::select(
                'sensor_id',
                DB::raw('MAX(recorded_at) as max_ts')
            )
            ->groupBy('sensor_id');

        $rows = DB::table('sensor_readings as r')
            ->joinSub($latest, 'l', function ($join) {
                $join->on('r.sensor_id', '=', 'l.sensor_id')
                     ->on('r.recorded_at', '=', 'l.max_ts');
            })
            ->select([
                DB::raw('ROUND(AVG(r.obstruction_pct), 2) as avg_obstruction'),
                DB::raw('ROUND(AVG(r.rainfall_mm), 3)     as avg_rainfall'),
                DB::raw('ROUND(AVG(r.flow_lps), 3)        as avg_flow'),
                DB::raw('ROUND(MAX(r.obstruction_pct), 2) as max_obstruction'),
                DB::raw('ROUND(MAX(r.rainfall_mm), 3)     as max_rainfall'),
                DB::raw('COUNT(*) as sensors_reporting'),
            ])
            ->first();

        return response()->json(['data' => $rows]);
    }

    /**
     * GET /api/analytics/by-region
     * Average metrics grouped by region from each sensor's latest reading.
     */
    public function byRegion(): JsonResponse
    {
        $latest = SensorReading::select(
                'sensor_id',
                DB::raw('MAX(recorded_at) as max_ts')
            )
            ->groupBy('sensor_id');

        $rows = DB::table('sensor_readings as r')
            ->joinSub($latest, 'l', function ($join) {
                $join->on('r.sensor_id', '=', 'l.sensor_id')
                     ->on('r.recorded_at', '=', 'l.max_ts');
            })
            ->join('sensors as s', 'r.sensor_id', '=', 's.id')
            ->select([
                's.region',
                DB::raw('ROUND(AVG(r.obstruction_pct), 2) as avg_obstruction'),
                DB::raw('ROUND(AVG(r.rainfall_mm), 3)     as avg_rainfall'),
                DB::raw('ROUND(AVG(r.flow_lps), 3)        as avg_flow'),
                DB::raw('COUNT(*) as sensor_count'),
            ])
            ->groupBy('s.region')
            ->orderBy('s.region')
            ->get();

        return response()->json(['data' => $rows]);
    }

    /**
     * GET /api/analytics/timeseries/{sensor}?hours=6
     * Time-series readings for one sensor over the last N hours.
     */
    public function timeseries(Sensor $sensor, \Illuminate\Http\Request $request): JsonResponse
    {
        $hours = min((int) $request->query('hours', 6), 72);

        $rows = SensorReading::where('sensor_id', $sensor->id)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'obstruction_pct', 'rainfall_mm', 'flow_lps']);

        return response()->json([
            'sensor' => ['id' => $sensor->id, 'code' => $sensor->code, 'name' => $sensor->name],
            'hours'  => $hours,
            'data'   => $rows,
        ]);
    }
}
