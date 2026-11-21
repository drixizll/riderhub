<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RouteController extends Controller
{
    /**
     * Fetches real road-following routes (primary + alternative) and turn-by-turn
     * navigation steps from OSRM (OpenStreetMap road network, free & keyless).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_lat' => ['required', 'numeric', 'between:-90,90'],
            'from_lng' => ['required', 'numeric', 'between:-180,180'],
            'to_lat' => ['required', 'numeric', 'between:-90,90'],
            'to_lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $fromLat = round((float) $validated['from_lat'], 4);
        $fromLng = round((float) $validated['from_lng'], 4);
        $toLat = round((float) $validated['to_lat'], 4);
        $toLng = round((float) $validated['to_lng'], 4);

        $cacheKey = "riderhub.route.{$fromLat}.{$fromLng}.{$toLat}.{$toLng}";

        $payload = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($fromLat, $fromLng, $toLat, $toLng): array {
            try {
                $url = sprintf(
                    'https://router.project-osrm.org/route/v1/driving/%s,%s;%s,%s',
                    $fromLng,
                    $fromLat,
                    $toLng,
                    $toLat,
                );

                $response = Http::withHeaders(['User-Agent' => 'RiderHubCapstone/1.0'])
                    ->acceptJson()
                    ->timeout(8)
                    ->get($url, [
                        'overview' => 'full',
                        'geometries' => 'geojson',
                        'steps' => 'true',
                        'alternatives' => 'true',
                    ]);

                $routes = $response->json('routes');
                if (! $response->successful() || ! is_array($routes) || $routes === []) {
                    return $this->fallbackRoute($fromLat, $fromLng, $toLat, $toLng);
                }

                $formattedRoutes = [];
                foreach (array_slice($routes, 0, 2) as $index => $route) {
                    $coords = array_map(
                        fn (array $pair): array => [(float) $pair[1], (float) $pair[0]],
                        data_get($route, 'geometry.coordinates', []),
                    );

                    $steps = [];
                    foreach (data_get($route, 'legs.0.steps', []) as $step) {
                        $instruction = $this->formatInstruction($step);
                        if ($instruction !== '') {
                            $steps[] = [
                                'instruction' => $instruction,
                                'distance_m' => (int) round((float) ($step['distance'] ?? 0)),
                                'duration_s' => (int) round((float) ($step['duration'] ?? 0)),
                                'road' => (string) ($step['name'] ?? ''),
                            ];
                        }
                    }

                    $formattedRoutes[] = [
                        'is_primary' => $index === 0,
                        'distance_km' => round(((float) ($route['distance'] ?? 0)) / 1000, 1),
                        'duration_min' => max(1, (int) round(((float) ($route['duration'] ?? 0)) / 60)),
                        'coordinates' => $coords,
                        'steps' => $steps,
                    ];
                }

                return [
                    'source' => 'osrm',
                    'routes' => $formattedRoutes,
                ];
            } catch (ConnectionException) {
                return $this->fallbackRoute($fromLat, $fromLng, $toLat, $toLng);
            }
        });

        return response()->json($payload);
    }

    /**
     * Turns an OSRM maneuver object into a clear, spoken-friendly navigation instruction.
     *
     * @param  array<string, mixed>  $step
     */
    private function formatInstruction(array $step): string
    {
        $type = (string) data_get($step, 'maneuver.type', '');
        $modifier = str_replace('_', ' ', (string) data_get($step, 'maneuver.modifier', ''));
        $road = trim((string) ($step['name'] ?? ''));
        $distM = (int) round((float) ($step['distance'] ?? 0));
        $distLabel = $distM >= 1000 ? sprintf('%.1f km', $distM / 1000) : "{$distM} m";
        $onto = $road !== '' ? " onto {$road}" : '';

        return match ($type) {
            'depart' => ($road !== '' ? "Head along {$road}" : 'Start heading toward the route')." for {$distLabel}",
            'arrive' => 'You have arrived at your destination',
            'turn' => trim('Turn '.$modifier).$onto.($distM > 0 ? " ({$distLabel})" : ''),
            'merge' => 'Merge '.$modifier.$onto,
            'fork' => 'Keep '.$modifier.' at the fork'.$onto,
            'roundabout', 'rotary' => 'Enter the roundabout and exit'.$onto,
            'continue', 'new name' => 'Continue straight'.($road !== '' ? " on {$road}" : '')." for {$distLabel}",
            default => $road !== '' ? "Proceed on {$road} ({$distLabel})" : '',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function fallbackRoute(float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        $dLat = deg2rad($toLat - $fromLat);
        $dLng = deg2rad($toLng - $fromLng);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($dLng / 2) ** 2;
        $km = round(6371 * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);

        return [
            'source' => 'direct',
            'routes' => [[
                'is_primary' => true,
                'distance_km' => $km,
                'duration_min' => max(1, (int) round(($km / 24) * 60)),
                'coordinates' => [[$fromLat, $fromLng], [$toLat, $toLng]],
                'steps' => [[
                    'instruction' => "Head toward destination ({$km} km)",
                    'distance_m' => (int) round($km * 1000),
                    'duration_s' => (int) round(($km / 24) * 3600),
                    'road' => '',
                ]],
            ]],
        ];
    }
}
