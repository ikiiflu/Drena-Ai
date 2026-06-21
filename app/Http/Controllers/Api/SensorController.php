<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sensor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    /**
     * GET /api/sensors
     * All active sensors with their latest reading and derived status.
     */
    public function index(): JsonResponse
    {
        $sensors = Sensor::with('latestReading')
            ->where('active', true)
            ->orderBy('code')
            ->get()
            ->map(fn ($s) => $this->formatSensor($s));

        return response()->json(['data' => $sensors]);
    }

    /**
     * GET /api/sensors/{sensor}
     * Single sensor with latest reading.
     */
    public function show(Sensor $sensor): JsonResponse
    {
        $sensor->load('latestReading');

        return response()->json(['data' => $this->formatSensor($sensor)]);
    }

    /**
     * GET /api/sensors/{sensor}/readings?limit=100
     * Paginated reading history for one sensor.
     */
    public function readings(Request $request, Sensor $sensor): JsonResponse
    {
        $limit    = min((int) $request->query('limit', 50), 500);
        $readings = $sensor->readings()->limit($limit)->get();

        return response()->json([
            'sensor' => ['id' => $sensor->id, 'code' => $sensor->code, 'name' => $sensor->name],
            'data'   => $readings,
        ]);
    }

    private function formatSensor(Sensor $sensor): array
    {
        $r = $sensor->latestReading;

        return [
            'id'         => $sensor->id,
            'code'       => $sensor->code,
            'name'       => $sensor->name,
            'address'    => $sensor->address,
            'region'     => $sensor->region,
            'latitude'   => $sensor->latitude,
            'longitude'  => $sensor->longitude,
            'status'     => $sensor->status,
            'reading'    => $r ? [
                'obstruction_pct' => $r->obstruction_pct,
                'rainfall_mm'     => $r->rainfall_mm,
                'flow_lps'        => $r->flow_lps,
                'recorded_at'     => $r->recorded_at?->toIso8601String(),
            ] : null,
        ];
    }
}
