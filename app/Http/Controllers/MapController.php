<?php

namespace App\Http\Controllers;

use App\Models\Sensor;

class MapController extends Controller
{
    public function index()
    {
        $sensors = Sensor::with('latestReading')
            ->where('active', true)
            ->orderBy('code')
            ->get();

        // Serializado aqui para evitar parse error do Blade com fn() dentro de @json()
        $sensorsJson = $sensors->map(function ($s) {
            return [
                'id'      => $s->id,
                'code'    => $s->code,
                'name'    => $s->name,
                'address' => $s->address,
                'region'  => $s->region,
                'lat'     => (float) $s->latitude,
                'lng'     => (float) $s->longitude,
                'status'  => $s->status,
                'reading' => $s->latestReading ? [
                    'obstruction_pct' => (float) $s->latestReading->obstruction_pct,
                    'rainfall_mm'     => (float) $s->latestReading->rainfall_mm,
                    'flow_lps'        => (float) $s->latestReading->flow_lps,
                ] : null,
            ];
        })->toJson();

        return view('map.operational_map', compact('sensors', 'sensorsJson'));
    }
}
