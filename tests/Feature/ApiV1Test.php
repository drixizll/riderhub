<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiV1Test extends TestCase
{
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
}
