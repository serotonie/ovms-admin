<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'stop_time' => 'datetime',
        ];
    }

    /**
     * Get all of the waypoints for the Trip
     */
    public function waypoints(): HasMany
    {
        return $this->hasMany(Waypoint::class);
    }

    /**
     * Get the vehicle associated with the Trip
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user associated with the Trip
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
