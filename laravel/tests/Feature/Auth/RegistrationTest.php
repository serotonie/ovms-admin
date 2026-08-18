<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_requires_a_valid_signature(): void
    {
        $response = $this->get('/register');

        $response->assertForbidden();
    }

    public function test_registration_screen_can_be_rendered_for_a_signed_invitation(): void
    {
        $response = $this->get(URL::temporarySignedRoute(
            'register.create',
            now()->addDay(),
            [
                'email' => 'invitee@example.com',
                'role' => 'admin',
            ],
        ));

        $response->assertOk();
    }

    public function test_signed_invitation_is_rejected_for_an_existing_email(): void
    {
        $user = User::factory()->create([
            'email' => 'invitee@example.com',
        ]);

        $response = $this->get(URL::temporarySignedRoute(
            'register.create',
            now()->addDay(),
            [
                'email' => $user->email,
                'role' => 'admin',
            ],
        ));

        $response->assertForbidden();
    }

    public function test_new_users_can_register_with_the_invited_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'admin',
        ]);
    }
}
