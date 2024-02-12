<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get all of the vehicles owned by the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ownedVehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'owner_id');
    }

    /**
     * The vehicles used by the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function usedVehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class);
    }
}
