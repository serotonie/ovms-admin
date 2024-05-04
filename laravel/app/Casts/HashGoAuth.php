<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Database\Eloquent\Model;

class HashGoAuth implements CastsInboundAttributes
{
    /**
     * Create a new cast class instance.
     */
    public function __construct()
    {
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return bcrypt($value, ['rounds' => 10]);
    }
}
