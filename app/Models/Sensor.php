<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sensor extends Model
{
    protected $fillable = [
        'code', 'name', 'address', 'region', 'latitude', 'longitude', 'active',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        'active'    => 'boolean',
    ];

    public function readings(): HasMany
    {
        return $this->hasMany(SensorReading::class)->orderByDesc('recorded_at');
    }

    public function latestReading(): HasOne
    {
        return $this->hasOne(SensorReading::class)->latestOfMany('recorded_at');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function activeAlerts(): HasMany
    {
        return $this->hasMany(Alert::class)->whereNull('resolved_at');
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    /** Thresholds cached per-request to avoid N queries for N sensors. */
    private static ?array $thresholds = null;

    private static function thresholds(): array
    {
        if (static::$thresholds !== null) {
            return static::$thresholds;
        }
        try {
            static::$thresholds = [
                'critico' => (float) Setting::get('alert_threshold_critico', 70),
                'risco'   => (float) Setting::get('alert_threshold_risco',   40),
                'atencao' => (float) Setting::get('alert_threshold_atencao', 10),
            ];
        } catch (\Throwable) {
            // Tabela settings ainda não existe (antes do migrate)
            static::$thresholds = ['critico' => 70.0, 'risco' => 40.0, 'atencao' => 10.0];
        }
        return static::$thresholds;
    }

    /** Derives status label from latest obstruction reading, respecting DB thresholds. */
    public function getStatusAttribute(): string
    {
        $obs = $this->latestReading?->obstruction_pct ?? 0;
        $t   = static::thresholds();

        return match (true) {
            $obs >= $t['critico'] => 'critico',
            $obs >= $t['risco']   => 'risco',
            $obs >= $t['atencao'] => 'atencao',
            default               => 'ok',
        };
    }
}
