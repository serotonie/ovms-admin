<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all of the vehicles owned by the User
     */
    public function vehicles_owned(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'owner_id', 'id');
    }

    /**
     * Get all of the vehicles mainly used by the User
     */
    public function vehicles_main_user(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'main_user_id', 'id');
    }

    /**
     * The vehicles used by the User
     */
    public function vehicles_used(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class);
    }
}
