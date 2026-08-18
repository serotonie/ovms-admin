<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Notifications\UserInvited;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_send_user_invites(): void
    {
        $response = $this->post(route('admin.users.invite.store'), [
            'email' => 'invitee@example.com',
            'role' => 'user',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_send_an_invitation(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('admin.users.invite.store'), [
                'email' => 'invitee@example.com',
                'role' => 'admin',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        Notification::assertSentOnDemand(
            UserInvited::class,
            function (UserInvited $notification, array $channels, object $notifiable) use ($user) {
                return $notifiable->routes['mail'] === 'invitee@example.com'
                    && $notification->role === 'admin'
                    && $notification->sender->is($user);
            }
        );
    }

    public function test_authenticated_user_can_update_an_existing_user(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'role' => 'admin',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_authenticated_user_can_delete_a_user(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();

        $response = $this
            ->actingAs($admin)
            ->delete(route('admin.users.delete', $user));

        $response->assertOk();
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
