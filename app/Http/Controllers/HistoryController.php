<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorReading;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $sensors  = Sensor::where('active', true)->orderBy('code')->get(['id', 'code', 'name']);
        $selected = $request->integer('sensor_id') ?: ($sensors->first()?->id);

        $readings = SensorReading::where('sensor_id', $selected)
            ->orderByDesc('recorded_at')
            ->limit(200)
            ->get();

        return view('history.index', compact('sensors', 'selected', 'readings'));
    }
}
