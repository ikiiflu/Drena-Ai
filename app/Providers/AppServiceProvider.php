<?php

namespace App\Providers;

use App\Models\Alert;
use App\Models\Sensor;
use App\Models\SensorReading;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Injeta sensores + contagem de alertas em toda view que usa o sidebar
        View::composer('layout.sidebar', function ($view) {
            $sensors    = Sensor::with('latestReading')->where('active', true)->orderBy('code')->get();
            $alertCount = Alert::whereNull('resolved_at')->count();

            $view->with('navSensors', $sensors)
                 ->with('navAlertCount', $alertCount);
        });

        // Injeta dados do rodapé em todas as páginas
        View::composer('layout.footer', function ($view) {
            $activeSensors = Sensor::where('active', true)->count();
            $lastSync      = SensorReading::max('recorded_at');

            $view->with('footerActiveSensors', $activeSensors)
                 ->with('footerTotalSensors', $activeSensors)
                 ->with('footerLastSync', $lastSync);
        });
    }
}
