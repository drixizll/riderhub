<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_api_v1_health_and_summary_endpoints_work(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('version', 'v1')
            ->assertJsonPath('status', 'ok');

        $this->getJson('/api/v1/summary')
            ->assertOk()
            ->assertJsonPath('platform', 'RiderHub')
            ->assertJsonPath('fare.currency', 'PHP');
    }

    public function test_api_v1_fare_estimate_and_chat_endpoints_work(): void
    {
        $this->postJson('/api/v1/fare-estimate', ['distance_km' => 5])
            ->assertOk()
            ->assertJsonPath('distance_km', 5)
            ->assertJsonPath('estimated_fare', 147.5);

        $this->postJson('/api/v1/assistant/chat', ['message' => 'halo'])
            ->assertOk()
            ->assertJsonStructure(['reply', 'actions', 'suggestions']);
    }

    public function test_api_v1_auth_and_bookings_flow_works_end_to_end(): void
    {
        $register = $this->postJson('/api/v1/register', [
            'name' => 'API Customer',
            'email' => 'API.User@Example.com',
            'password' => '1234',
            'role' => 'customer',
        ])->assertCreated();

        $token = $register->json('token');
        $this->assertNotEmpty($token);

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'api.user@example.com');

        $this->withToken($token)
            ->postJson('/api/v1/bookings', [
                'type' => 'ride',
                'pickup_address' => 'Poblacion, Tagoloan',
                'pickup_lat' => 8.5378,
                'pickup_lng' => 124.7536,
                'dropoff_address' => 'Baluarte, Tagoloan',
                'dropoff_lat' => 8.5450,
                'dropoff_lng' => 124.7640,
                'payment_method' => 'cash',
            ])
            ->assertCreated()
            ->assertJsonPath('booking.status', 'pending');

        $this->withToken($token)
            ->getJson('/api/v1/bookings')
            ->assertOk()
            ->assertJsonCount(1, 'bookings');

        $this->withToken($token)
            ->postJson('/api/v1/logout')
            ->assertOk();

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }
}
