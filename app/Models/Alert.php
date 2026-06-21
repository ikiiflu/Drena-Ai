<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'sensor_id', 'severity', 'message', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }

    public function isActive(): bool
    {
        return $this->resolved_at === null;
    }
}
