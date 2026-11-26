<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssistantAndWeatherTest extends TestCase
{
    use RefreshDatabase;

    public function test_weather_endpoint_returns_normalized_open_meteo_data(): void
    {
        Cache::flush();
        Http::fake([
            'https://api.open-meteo.com/v1/forecast*' => Http::response([
                'current' => [
                    'temperature_2m' => 30.4,
                    'apparent_temperature' => 33.1,
                    'relative_humidity_2m' => 72,
                    'wind_speed_10m' => 12.5,
                    'weather_code' => 61,
                    'is_day' => 1,
                ],
                'hourly' => [
                    'precipitation_probability' => [80],
                ],
            ]),
        ]);

        $this->getJson('/api/weather?lat=14.5995&lng=120.9842')
            ->assertOk()
            ->assertJson([
                'available' => true,
                'temperature' => 30.4,
                'rain_chance' => 80,
                'label' => 'Rain showers',
                'icon' => 'rain',
                'severity' => 'alert',
            ]);
    }

    public function test_assistant_estimates_fare_for_requested_distance(): void
    {
        $response = $this->postJson('/api/assistant/chat', [
            'message' => 'Magkano pamasahe for 5 km?',
        ])->assertOk();

        // Base 55 + 5 * 18.50 = 147.50
        $this->assertStringContainsString('147.50', $response->json('reply'));
    }

    public function test_assistant_reports_authenticated_customer_booking_status(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $rider = User::factory()->create(['role' => 'rider', 'name' => 'Marco Santos']);
        Booking::factory()->create([
            'customer_id' => $customer->id,
            'rider_id' => $rider->id,
            'type' => 'ride',
            'pickup_address' => 'Ayala Triangle',
            'dropoff_address' => 'BGC High Street',
            'status' => 'picked_up',
            'fare' => 125.00,
        ]);

        $response = $this->actingAs($customer)
            ->postJson('/api/assistant/chat', ['message' => 'Check my booking status'])
            ->assertOk();

        $reply = $response->json('reply');
        $this->assertStringContainsString('picked up', $reply);
        $this->assertStringContainsString('Marco Santos', $reply);
    }

    public function test_welcome_page_displays_about_us_photo_showcase_and_ai_assistant(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('About RiderHub')
            ->assertSee('tagoloan-hall.jpg')
            ->assertSee('RiderHub AI');
    }

    public function test_assistant_enforces_strict_riderhub_domain_guardrails(): void
    {
        // Off-topic / unrelated questions must politely decline and list RiderHub-only topics
        $offTopic = $this->postJson('/api/assistant/chat', ['message' => 'Who is the president of France?'])
            ->assertOk();
        $this->assertStringContainsString('RiderHub Misamis Oriental', $offTopic->json('reply'));
        $this->assertStringContainsString('inquiries only', $offTopic->json('reply'));

        // Random short non-related messages like "ok" also receive the RiderHub scope notice
        $short = $this->postJson('/api/assistant/chat', ['message' => 'ok'])
            ->assertOk();
        $this->assertStringContainsString('inquiries only', $short->json('reply'));

        // Taglish navigation query should give RiderHub login guidance
        $loginHelp = $this->postJson('/api/assistant/chat', ['message' => 'san mag log in?'])
            ->assertOk();
        $this->assertStringContainsString('Login page', $loginHelp->json('reply'));

        // Casual greetings like "halo" should reply warmly with RiderHub greeting
        $greeting = $this->postJson('/api/assistant/chat', ['message' => 'halo'])
            ->assertOk();
        $this->assertStringContainsString('RiderHub AI', $greeting->json('reply'));

        // Promo code query works
        $promo = $this->postJson('/api/assistant/chat', ['message' => 'Promo codes'])
            ->assertOk();
        $this->assertStringContainsString('TAGOLOAN20', $promo->json('reply'));
    }

    public function test_route_endpoint_returns_real_road_steps_and_eta(): void
    {
        Cache::flush();
        Http::fake([
            'https://router.project-osrm.org/route/v1/driving/*' => Http::response([
                'routes' => [[
                    'distance' => 5400,
                    'duration' => 720,
                    'geometry' => ['coordinates' => [[124.7536, 8.5378], [124.6498, 8.4810]]],
                    'legs' => [[
                        'steps' => [
                            ['distance' => 300, 'duration' => 45, 'name' => 'National Highway', 'maneuver' => ['type' => 'depart']],
                            ['distance' => 0, 'duration' => 0, 'name' => '', 'maneuver' => ['type' => 'arrive']],
                        ],
                    ]],
                ]],
            ]),
        ]);

        $this->getJson('/api/route?from_lat=8.5378&from_lng=124.7536&to_lat=8.4810&to_lng=124.6498')
            ->assertOk()
            ->assertJsonPath('source', 'osrm')
            ->assertJsonPath('routes.0.distance_km', 5.4)
            ->assertJsonPath('routes.0.duration_min', 12);
    }
}
