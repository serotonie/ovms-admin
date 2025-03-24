<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => env('ADMIN_NAME', 'admin'),
            'password' => env('ADMIN_PASSWORD', 'changeme'),
            'role' => 'admin',
            'email' => env('ADMIN_MAIL', 'admin@example.com'),
            'email_verified_at' => now(),
        ]);
    }
}
