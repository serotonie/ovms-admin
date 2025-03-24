<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory()
            ->count(random_int(3, 15))
            ->sequence(fn (Sequence $sequence) => [
                'name' => 'user'.str($sequence->index),
                'password' => 'userpwd',
                'email' => 'user'.str($sequence->index).'@test.com',
                'email_verified_at' => now(),
            ])->create();

        foreach ($users as $user) {
            $user->assignRole('User');
        }

        $admins = User::factory(random_int(1, 3))
            ->sequence(fn (Sequence $sequence) => [
                'name' => 'admin'.str($sequence->index),
                'password' => 'adminpwd',
                'email' => 'admin'.str($sequence->index).'@test.com',
                'email_verified_at' => now(),
            ])->create();

        foreach ($admins as $admin) {
            $admin->assignRole('Admin');
        }
    }
}
