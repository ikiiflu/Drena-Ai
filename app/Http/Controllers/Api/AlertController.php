<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\JsonResponse;

class AlertController extends Controller
{
    /**
     * GET /api/alerts/active
     * All unresolved alerts, highest severity first.
     */
    public function active(): JsonResponse
    {
        $severityOrder = ['critico' => 3, 'risco' => 2, 'atencao' => 1];

        $alerts = Alert::with('sensor:id,code,name,region')
            ->whereNull('resolved_at')
            ->orderByDesc('created_at')
            ->get()
            ->sortByDesc(fn ($a) => $severityOrder[$a->severity] ?? 0)
            ->values()
            ->map(fn ($a) => [
                'id'         => $a->id,
                'severity'   => $a->severity,
                'message'    => $a->message,
                'created_at' => $a->created_at->toIso8601String(),
                'sensor'     => $a->sensor,
            ]);

        return response()->json(['data' => $alerts, 'count' => $alerts->count()]);
    }

    /**
     * GET /api/alerts
     * Full alert history (last 200), including resolved.
     */
    public function index(): JsonResponse
    {
        $alerts = Alert::with('sensor:id,code,name,region')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return response()->json(['data' => $alerts]);
    }
}
