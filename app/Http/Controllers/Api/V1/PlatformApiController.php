<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\RiderHubAssistantService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlatformApiController extends Controller
{
    public function health(): JsonResponse
    {
        $databaseOk = true;
        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $databaseOk = false;
        }

        return response()->json([
            'status' => $databaseOk ? 'ok' : 'degraded',
            'app' => 'RiderHub Backend API',
            'version' => 'v1',
            'database' => $databaseOk ? 'connected' : 'unavailable',
            'region' => config('riderhub.map.region_name', 'Misamis Oriental'),
            'timestamp_pht' => now()->timezone('Asia/Manila')->format('Y-m-d H:i:s'),
        ]);
    }

    public function summary(): JsonResponse
    {
        $fare = config('riderhub.fare', ['base' => 55.00, 'per_km' => 18.50]);

        return response()->json([
            'platform' => 'RiderHub',
            'region' => config('riderhub.map.region_name', 'Misamis Oriental'),
            'fare' => [
                'currency' => 'PHP',
                'base' => (float) $fare['base'],
                'per_km' => (float) $fare['per_km'],
            ],
            'services' => ['ride', 'delivery'],
            'payment_methods' => ['cash', 'paymongo_gcash', 'paymongo_maya', 'paymongo_card'],
            'map_center' => config('riderhub.map.center', ['lat' => 8.5378, 'lng' => 124.7536]),
        ]);
    }

    public function estimateFare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'distance_km' => ['nullable', 'numeric', 'min:0.1', 'max:200'],
            'pickup_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'pickup_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'dropoff_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'dropoff_lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $fareConfig = config('riderhub.fare', ['base' => 55.00, 'per_km' => 18.50]);

        if (
            isset($validated['pickup_lat'], $validated['pickup_lng'], $validated['dropoff_lat'], $validated['dropoff_lng'])
        ) {
            $lat1 = deg2rad((float) $validated['pickup_lat']);
            $lng1 = deg2rad((float) $validated['pickup_lng']);
            $lat2 = deg2rad((float) $validated['dropoff_lat']);
            $lng2 = deg2rad((float) $validated['dropoff_lng']);
            $dLat = $lat2 - $lat1;
            $dLng = $lng2 - $lng1;
            $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLng / 2) ** 2;
            $distanceKm = 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
        } else {
            $distanceKm = (float) ($validated['distance_km'] ?? 3.0);
        }

        $distanceKm = round(max(0.1, $distanceKm), 2);
        $estimatedFare = round((float) $fareConfig['base'] + ($distanceKm * (float) $fareConfig['per_km']), 2);

        return response()->json([
            'currency' => 'PHP',
            'distance_km' => $distanceKm,
            'base_fare' => (float) $fareConfig['base'],
            'per_km_rate' => (float) $fareConfig['per_km'],
            'estimated_fare' => $estimatedFare,
            'formatted_fare' => '₱'.number_format($estimatedFare, 2),
        ]);
    }

    public function weather(Request $request, WeatherService $weather): JsonResponse
    {
        $lat = (float) $request->query('lat', config('riderhub.map.center.lat', 8.5378));
        $lng = (float) $request->query('lng', config('riderhub.map.center.lng', 124.7536));

        return response()->json($weather->current($lat, $lng));
    }

    public function chat(Request $request, RiderHubAssistantService $assistant): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'history' => ['nullable', 'array', 'max:10'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.text' => ['required_with:history', 'string', 'max:600'],
        ]);

        return response()->json(
            $assistant->respond(
                message: $validated['message'],
                user: $request->user(),
                history: $validated['history'] ?? [],
            )
        );
    }
}
