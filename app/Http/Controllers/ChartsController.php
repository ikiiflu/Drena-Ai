<?php

namespace App\Http\Controllers;

use App\Models\Sensor;

class ChartsController extends Controller
{
    public function index()
    {
        $sensors = Sensor::with('latestReading')
            ->where('active', true)
            ->orderBy('code')
            ->get();

        return view('charts.index', compact('sensors'));
    }
}
