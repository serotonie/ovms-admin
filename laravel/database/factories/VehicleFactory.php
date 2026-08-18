<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'module_id' => fake()->unique()->bothify('module-####'),
            'module_username' => fake()->unique()->userName(),
            'module_pwd' => Hash::make('password'),
            'owner_id' => User::factory(),
            'main_user_id' => User::factory(),
        ];
    }
}
