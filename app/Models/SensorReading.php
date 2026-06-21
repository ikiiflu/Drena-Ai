<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sensor_id', 'obstruction_pct', 'rainfall_mm', 'flow_lps', 'recorded_at',
    ];

    protected $casts = [
        'obstruction_pct' => 'float',
        'rainfall_mm'     => 'float',
        'flow_lps'        => 'float',
        'recorded_at'     => 'datetime',
    ];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }
}
