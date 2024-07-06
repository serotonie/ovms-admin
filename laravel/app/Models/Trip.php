<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['path'];

    /**
     * Get all of the waypoints for the Trip
     */
    public function waypoints(): HasMany
    {
        return $this->hasMany(Waypoint::class);
    }

    /**
     * The GeoJson Object for the trip
     *
     * @return array
     */
    protected function getPathAttribute()
    {
        $waypoints = [
            new \GeoJson\Geometry\Point([
                $this->start_point_lat,
                $this->start_point_long,
            ]),
        ];

        foreach ($this->waypoints as $waypoint) {
            $point = new \GeoJson\Geometry\Point([
                $waypoint->position_lat,
                $waypoint->position_long,
            ]);
            array_push($waypoints, $point);
        }

        $path = new \GeoJson\Geometry\LineString($waypoints);

        return $path;
    }
}
