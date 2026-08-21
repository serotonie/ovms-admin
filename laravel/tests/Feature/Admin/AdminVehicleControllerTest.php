<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminVehicleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_store_a_vehicle(): void
    {
        $owner = User::factory()->create();
        $mainUser = User::factory()->create();
        $assignedUser = User::factory()->create();

        $response = $this->post(route('admin.vehicles.store'), [
            'name' => 'Roadster',
            'owner' => $owner->id,
            'main_user' => $mainUser->id,
            'users' => [$assignedUser->id],
            'module_id' => 'module-1',
            'module_username' => 'roadster-admin',
            'module_pwd' => 'top-secret',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_store_a_vehicle(): void
    {
        $admin = User::factory()->create();
        $owner = User::factory()->create();
        $mainUser = User::factory()->create();
        $assignedUser = User::factory()->create();

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.vehicles.store'), [
                'name' => 'Roadster',
                'owner' => $owner->id,
                'main_user' => $mainUser->id,
                'users' => [$assignedUser->id],
                'module_id' => 'module-1',
                'module_username' => 'roadster-admin',
                'module_pwd' => 'top-secret',
            ]);

        $response->assertOk();

        $vehicle = Vehicle::first();

        $this->assertNotNull($vehicle);
        $this->assertSame('Roadster', $vehicle->name);
        $this->assertSame($owner->id, $vehicle->owner_id);
        $this->assertSame($mainUser->id, $vehicle->main_user_id);
        $this->assertTrue(Hash::check('top-secret', $vehicle->module_pwd));
        $this->assertSame([$assignedUser->id], $vehicle->users()->pluck('users.id')->all());

        $this->assertDatabaseCount('mqtt_acls', 3);
        $this->assertDatabaseHas('mqtt_acls', [
            'username' => 'roadster-admin',
            'rw' => 1,
            'topic' => 'ovms/roadster-admin/module-1/#',
        ]);
        $this->assertDatabaseHas('mqtt_acls', [
            'username' => 'roadster-admin',
            'rw' => 2,
            'topic' => 'ovms/roadster-admin/module-1/#',
        ]);
        $this->assertDatabaseHas('mqtt_acls', [
            'username' => 'roadster-admin',
            'rw' => 4,
            'topic' => 'ovms/roadster-admin/module-1/#',
        ]);
    }

    public function test_authenticated_user_can_view_the_vehicle_cli_interface(): void
    {
        $admin = User::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'name' => 'CLI Vehicle',
            'module_id' => 'module-cli',
            'module_username' => 'cli-user',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.vehicles.cli', $vehicle));

        $response->assertOk();
    }

    public function test_authenticated_user_can_update_an_existing_vehicle(): void
    {
        $admin = User::factory()->create();
        $owner = User::factory()->create();
        $mainUser = User::factory()->create();
        $existingUser = User::factory()->create();
        $newOwner = User::factory()->create();
        $newMainUser = User::factory()->create();
        $newAssignedUser = User::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'owner_id' => $owner->id,
            'main_user_id' => $mainUser->id,
        ]);

        $vehicle->users()->attach($existingUser);

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.vehicles.update', $vehicle), [
                'name' => 'Updated Vehicle',
                'owner' => $newOwner->id,
                'main_user' => $newMainUser->id,
                'users' => [$newAssignedUser->id],
            ]);

        $response->assertOk();

        $vehicle->refresh();

        $this->assertSame('Updated Vehicle', $vehicle->name);
        $this->assertSame($newOwner->id, $vehicle->owner_id);
        $this->assertSame($newMainUser->id, $vehicle->main_user_id);
        $this->assertSame([$newAssignedUser->id], $vehicle->users()->pluck('users.id')->all());
    }

    public function test_authenticated_user_can_delete_a_vehicle(): void
    {
        $admin = User::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'module_id' => 'module-9',
            'module_username' => 'delete-me',
        ]);

        DB::table('mqtt_acls')->insert([
            ['username' => 'delete-me', 'rw' => 1, 'topic' => 'ovms/delete-me/module-9/#'],
            ['username' => 'delete-me', 'rw' => 2, 'topic' => 'ovms/delete-me/module-9/#'],
            ['username' => 'delete-me', 'rw' => 4, 'topic' => 'ovms/delete-me/module-9/#'],
        ]);

        $response = $this
            ->actingAs($admin)
            ->delete(route('admin.vehicles.delete', $vehicle));

        $response->assertOk();
        $this->assertDatabaseMissing('vehicles', [
            'id' => $vehicle->id,
        ]);
        $this->assertDatabaseCount('mqtt_acls', 0);
    }
}
