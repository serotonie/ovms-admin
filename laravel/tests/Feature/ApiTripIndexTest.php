<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiTripIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_api_requires_basic_authentication(): void
    {
        $response = $this->getJson('/api/trips?start_at=2026-01-01&end_at=2026-01-02&vehicle_id=1');

        $response->assertUnauthorized();
    }

    public function test_trip_api_returns_only_accessible_trips_overlapping_the_requested_period(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['owner_id' => $user->id, 'main_user_id' => null]);
        $otherVehicle = Vehicle::factory()->create();

        $matchingTrip = $this->createTrip($vehicle, $user, '2026-01-01 12:00:00', '2026-01-01 13:00:00');
        $this->createTrip($vehicle, $user, '2025-12-01 12:00:00', '2025-12-01 13:00:00');
        $this->createTrip($otherVehicle, $user, '2026-01-01 12:00:00', '2026-01-01 13:00:00');

        $response = $this->withBasicAuth($user->email, 'password')->getJson(
            '/api/trips?start_at=2026-01-01T00:00:00Z&end_at=2026-01-02T00:00:00Z&vehicle_id='.$vehicle->id
        );

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $matchingTrip->id)
            ->assertJsonPath('0.vehicle.id', $vehicle->id)
            ->assertJsonPath('0.vehicle.name', $vehicle->name);
    }

    public function test_trip_api_includes_open_trips_that_started_before_the_end_of_the_period(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['owner_id' => $user->id, 'main_user_id' => null]);
        $trip = $this->createTrip($vehicle, $user, '2025-12-31 23:00:00', null);

        $response = $this->withBasicAuth($user->email, 'password')->getJson(
            '/api/trips?start_at=2026-01-01T00:00:00Z&end_at=2026-01-01T12:00:00Z&vehicle_id='.$vehicle->id
        );

        $response->assertOk()->assertJsonPath('0.id', $trip->id);
    }

    public function test_trip_api_supports_a_shared_user_filter(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['owner_id' => $owner->id, 'main_user_id' => null]);
        $vehicle->users()->attach($user);
        $trip = $this->createTrip($vehicle, $user, '2026-01-01 12:00:00', '2026-01-01 13:00:00');

        $response = $this->withBasicAuth($user->email, 'password')->getJson(
            '/api/trips?start_at=2026-01-01&end_at=2026-01-02&user_id='.$user->id
        );

        $response->assertOk()->assertJsonPath('0.id', $trip->id);
    }

    public function test_trip_api_validates_the_period_and_selector(): void
    {
        $user = User::factory()->create();

        $response = $this->withBasicAuth($user->email, 'password')->getJson(
            '/api/trips?start_at=2026-01-02&end_at=2026-01-01&vehicle_id=1&user_id='.$user->id
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['end_at', 'vehicle_id', 'user_id']);
    }

    private function createTrip(Vehicle $vehicle, User $user, string $start, ?string $stop): Trip
    {
        $id = DB::table('trips')->insertGetId([
            'vehicle_id' => $vehicle->id,
            'start_time' => $start,
            'start_point_lat' => 48.8566,
            'start_point_long' => 2.3522,
            'stop_time' => $stop,
            'user_id' => $user->id,
        ]);

        return Trip::findOrFail($id);
    }
}
