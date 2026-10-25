<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200)
            ->assertSee('Create your account')
            ->assertSee('name="role"', false)
            ->assertSee('data-password-strength', false)
            ->assertSee('name="phone"', false)
            ->assertSee('Show password');
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'customer',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('customer.dashboard', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'role' => 'customer']);
    }

    public function test_new_riders_are_redirected_to_the_rider_workspace(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Rider',
            'email' => 'rider@example.com',
            'role' => 'rider',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
        ]);

        $response->assertRedirect(route('rider.dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'rider@example.com', 'role' => 'rider']);
    }

    public function test_registration_rejects_admin_roles_and_weak_passwords(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Invalid Role',
            'email' => 'admin-signup@example.com',
            'role' => 'admin',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
        ])->assertSessionHasErrors('role');

        $this->from('/register')->post('/register', [
            'name' => 'Weak Password',
            'email' => 'weak-password@example.com',
            'role' => 'customer',
            'password' => 'lowercase',
            'password_confirmation' => 'lowercase',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }
}
