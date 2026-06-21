<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Sensor;

class DashboardController extends Controller
{
    public function index()
    {
        $sensors = Sensor::with('latestReading')
            ->where('active', true)
            ->orderBy('code')
            ->get();

        $activeAlerts = Alert::with('sensor:id,code,name')
            ->whereNull('resolved_at')
            ->orderByDesc('created_at')
            ->get();

        $readings = $sensors->map(fn ($s) => $s->latestReading)->filter();

        $metrics = [
            'avg_obstruction' => $readings->isEmpty() ? null : round($readings->avg('obstruction_pct'), 1),
            'avg_rainfall'    => $readings->isEmpty() ? null : round($readings->avg('rainfall_mm'), 2),
            'avg_flow'        => $readings->isEmpty() ? null : round($readings->avg('flow_lps'), 1),
            'sensors_count'   => $sensors->count(),
            'alerts_count'    => $activeAlerts->count(),
        ];

        // Derive worst status per region for the city band
        $statusRank = ['ok' => 0, 'atencao' => 1, 'risco' => 2, 'critico' => 3];
        $rankStatus = array_flip($statusRank);

        $regionStatus = $sensors->groupBy('region')->mapWithKeys(function ($group, $region) use ($statusRank, $rankStatus) {
            $worst = $group->map(fn ($s) => $statusRank[$s->status] ?? 0)->max() ?? 0;
            return [$region => $rankStatus[$worst] ?? 'ok'];
        });

        return view('dashboard.index', compact('sensors', 'activeAlerts', 'metrics', 'regionStatus'));
    }
}
