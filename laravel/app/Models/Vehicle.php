<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'module_id',
        'module_username',
        'module_pwd',
        'owner_id',
        'main_user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'module_pwd',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['ownership_level'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_seen' => 'datetime',
        ];
    }

    /**
     * Get the owner of the Vehicle
     */
    public function owner(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'owner_id');
    }

    /**
     * Get the main user of the Vehicle
     */
    public function main_user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'main_user_id');
    }

    /**
     * The users of the Vehicle
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * The ownership level of the user for the Vehicle
     *
     * @return string
     */
    public function getOwnershipLevelAttribute()
    {
        $user_id = auth()->user()->id;

        if ($user_id === $this->owner_id) {
            return 'owner';
        } elseif ($user_id === $this->main_user_id) {
            return 'main_user';
        } elseif ($this->users()->pluck('user_id')->contains($user_id)) {
            return 'user';
        }

        return null;
    }
}
