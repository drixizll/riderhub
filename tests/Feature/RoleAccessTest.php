<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_the_riderhub_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('welcome')
            ->assertSee('Book a Ride')
            ->assertSee('Send a Package');
    }

    public function test_signed_in_users_are_redirected_to_their_role_dashboard_from_root(): void
    {
        foreach ([
            'customer' => 'customer.dashboard',
            'rider' => 'rider.dashboard',
            'admin' => 'admin.dashboard',
        ] as $role => $dashboardRoute) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get('/')
                ->assertRedirect(route($dashboardRoute));
        }
    }

    public function test_dashboard_route_redirects_users_to_their_role_dashboard(): void
    {
        foreach ([
            'customer' => 'customer.dashboard',
            'rider' => 'rider.dashboard',
            'admin' => 'admin.dashboard',
        ] as $role => $dashboardRoute) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get('/dashboard')
                ->assertRedirect(route($dashboardRoute));
        }
    }

    public function test_customer_role_can_access_customer_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)
            ->get('/customer/dashboard')
            ->assertOk();
    }

    public function test_rider_role_is_denied_access_to_customer_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'rider']);

        $this->actingAs($user)
            ->get('/customer/dashboard')
            ->assertForbidden();
    }

    public function test_admin_role_can_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertOk();
    }
}
