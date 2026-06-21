<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('id')->get()->keyBy('key');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'reading_interval_seconds' => 'required|integer|min:10|max:86400',
            'alert_threshold_atencao'  => 'required|integer|min:1|max:99',
            'alert_threshold_risco'    => 'required|integer|min:1|max:99',
            'alert_threshold_critico'  => 'required|integer|min:1|max:99',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Configurações salvas com sucesso.');
    }
}
