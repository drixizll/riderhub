<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function __invoke(Request $request, WeatherService $weather): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $lat = isset($validated['lat']) ? (float) $validated['lat'] : (float) config('riderhub.map.center.lat', 8.5378);
        $lng = isset($validated['lng']) ? (float) $validated['lng'] : (float) config('riderhub.map.center.lng', 124.7536);

        return response()->json($weather->current($lat, $lng));
    }
}
