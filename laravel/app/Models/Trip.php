<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    public function vehicle(): HasOne
    {
        return $this->hasOne(Vehicle::class);
    }
}
