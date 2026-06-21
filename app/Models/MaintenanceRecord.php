<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    protected $fillable = [
        'sensor_id', 'operator_name', 'description', 'notes', 'performed_at',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }
}
