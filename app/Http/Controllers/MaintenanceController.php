<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRecord;
use App\Models\Sensor;

class MaintenanceController extends Controller
{
    public function index()
    {
        $records = MaintenanceRecord::with('sensor:id,code,name,region')
            ->orderByDesc('performed_at')
            ->paginate(20);

        $sensors = Sensor::where('active', true)->orderBy('code')->get(['id', 'code', 'name']);

        return view('maintenance.index', compact('records', 'sensors'));
    }
}
