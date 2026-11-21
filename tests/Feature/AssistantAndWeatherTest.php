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
            ->assertSee('rider-city-ride.jpg')
            ->assertSee('RiderHub AI');
    }
}
