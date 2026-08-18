<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TripIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_auth_is_required(): void
    {
        $response = $this->getJson('/api/trips?vehicle_id=1&start_at=2026-01-01T00:00:00Z&end_at=2026-01-31T23:59:59Z');

        $response->assertUnauthorized();
    }

    public function test_it_returns_trips_for_an_accessible_vehicle_within_the_requested_time_range(): void
    {
        $user = User::factory()->create();
        $vehicle = $this->createVehicle($user);

        $matchingTripId = $this->insertTrip([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'start_time' => '2026-01-10 08:00:00',
            'stop_time' => '2026-01-10 09:00:00',
        ]);

        $this->insertTrip([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'start_time' => '2025-12-31 22:00:00',
            'stop_time' => '2025-12-31 23:00:00',
        ]);

        $response = $this->withBasicAuth($user->email, 'password')
            ->getJson("/api/trips?vehicle_id={$vehicle->id}&start_at=2026-01-01T00:00:00Z&end_at=2026-01-31T23:59:59Z");

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $matchingTripId)
            ->assertJsonPath('0.vehicle.id', $vehicle->id)
            ->assertJsonPath('0.user.id', $user->id);
    }

    public function test_it_returns_trips_for_a_user_across_accessible_vehicles(): void
    {
        $owner = User::factory()->create();
        $tripUser = User::factory()->create();

        $ownedVehicle = $this->createVehicle($owner);
        $sharedVehicle = $this->createVehicle($owner, ['main_user_id' => $tripUser->id]);
        $foreignVehicle = $this->createVehicle(User::factory()->create());

        $ownedTripId = $this->insertTrip([
            'vehicle_id' => $ownedVehicle->id,
            'user_id' => $tripUser->id,
            'start_time' => '2026-02-01 08:00:00',
            'stop_time' => '2026-02-01 09:00:00',
        ]);

        $sharedTripId = $this->insertTrip([
            'vehicle_id' => $sharedVehicle->id,
            'user_id' => $tripUser->id,
            'start_time' => '2026-02-03 08:00:00',
            'stop_time' => '2026-02-03 09:00:00',
        ]);

        $this->insertTrip([
            'vehicle_id' => $foreignVehicle->id,
            'user_id' => $tripUser->id,
            'start_time' => '2026-02-05 08:00:00',
            'stop_time' => '2026-02-05 09:00:00',
        ]);

        $response = $this->withBasicAuth($owner->email, 'password')
            ->getJson("/api/trips?user_id={$tripUser->id}&start_at=2026-02-01T00:00:00Z&end_at=2026-02-28T23:59:59Z");

        $response->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.id', $ownedTripId)
            ->assertJsonPath('1.id', $sharedTripId);
    }

    public function test_it_validates_filter_and_time_range_parameters(): void
    {
        $user = User::factory()->create();
        $vehicle = $this->createVehicle($user);

        $response = $this->withBasicAuth($user->email, 'password')
            ->getJson("/api/trips?vehicle_id={$vehicle->id}&user_id={$user->id}&start_at=2026-03-10T00:00:00Z&end_at=2026-03-01T00:00:00Z");

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['vehicle_id', 'user_id', 'end_at']);
    }

    private function createVehicle(User $owner, array $attributes = []): Vehicle
    {
        return Vehicle::create(array_merge([
            'name' => fake()->word(),
            'module_id' => fake()->unique()->bothify('module-####'),
            'module_username' => fake()->unique()->bothify('user-####'),
            'module_pwd' => 'secret',
            'owner_id' => $owner->id,
            'main_user_id' => $owner->id,
            'mqtt_superuser' => false,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function insertTrip(array $attributes): int
    {
        return (int) DB::table('trips')->insertGetId(array_merge([
            'vehicle_id' => null,
            'user_id' => null,
            'start_time' => '2026-01-01 08:00:00',
            'start_point_lat' => 48.8566,
            'start_point_long' => 2.3522,
            'stop_time' => '2026-01-01 09:00:00',
            'stop_point_lat' => 48.8570,
            'stop_point_long' => 2.3530,
            'distance' => 12.5,
        ], $attributes));
    }
}
