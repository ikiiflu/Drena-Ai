<?php

namespace App\Http\Controllers;

use App\Models\Alert;

class AlertsController extends Controller
{
    public function index()
    {
        $activeAlerts = Alert::with('sensor:id,code,name,region')
            ->whereNull('resolved_at')
            ->orderByDesc('created_at')
            ->get();

        $resolvedAlerts = Alert::with('sensor:id,code,name,region')
            ->whereNotNull('resolved_at')
            ->orderByDesc('resolved_at')
            ->limit(50)
            ->get();

        return view('alerts.index', compact('activeAlerts', 'resolvedAlerts'));
    }
}
