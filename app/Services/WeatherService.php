<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    /**
     * Fetches real-time weather from Open-Meteo (free, no API key required)
     * and caches the result for 10 minutes per rounded coordinate.
     *
     * @return array<string, mixed>
     */
    public function current(float $lat = 8.5378, float $lng = 124.7536): array
    {
        $roundedLat = round($lat, 2);
        $roundedLng = round($lng, 2);
        $cacheKey = sprintf('riderhub.weather.%s.%s', $roundedLat, $roundedLng);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($roundedLat, $roundedLng): array {
            try {
                $response = Http::acceptJson()
                    ->timeout(6)
                    ->get('https://api.open-meteo.com/v1/forecast', [
                        'latitude' => $roundedLat,
                        'longitude' => $roundedLng,
                        'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m,is_day',
                        'hourly' => 'precipitation_probability',
                        'forecast_days' => 1,
                        'timezone' => 'auto',
                    ]);

                if (! $response->successful()) {
                    return $this->fallback($roundedLat, $roundedLng);
                }

                $current = $response->json('current', []);
                $code = (int) data_get($current, 'weather_code', 0);
                $temp = round((float) data_get($current, 'temperature_2m', 29), 1);
                $feelsLike = round((float) data_get($current, 'apparent_temperature', $temp), 1);
                $humidity = (int) data_get($current, 'relative_humidity_2m', 65);
                $windKmh = round((float) data_get($current, 'wind_speed_10m', 10), 1);
                $isDay = (int) data_get($current, 'is_day', 1) === 1;
                $rainChance = (int) data_get($response->json('hourly.precipitation_probability', []), 0, 0);

                [$label, $icon, $severity, $advisory] = $this->describeCode($code, $isDay, $windKmh);

                return [
                    'available' => true,
                    'lat' => $roundedLat,
                    'lng' => $roundedLng,
                    'temperature' => $temp,
                    'feels_like' => $feelsLike,
                    'humidity' => $humidity,
                    'wind_kmh' => $windKmh,
                    'rain_chance' => $rainChance,
                    'weather_code' => $code,
                    'is_day' => $isDay,
                    'label' => $label,
                    'icon' => $icon,
                    'severity' => $severity,
                    'advisory' => $advisory,
                    'updated_at' => now()->toIso8601String(),
                ];
            } catch (ConnectionException) {
                return $this->fallback($roundedLat, $roundedLng);
            }
        });
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string} [label, icon, severity, advisory]
     */
    private function describeCode(int $code, bool $isDay, float $windKmh): array
    {
        return match (true) {
            $code === 0 => [
                $isDay ? 'Clear sky' : 'Clear night',
                $isDay ? 'sun' : 'moon',
                'good',
                'Great riding conditions. Clear roads and smooth trips expected.',
            ],
            in_array($code, [1, 2], true) => [
                'Partly cloudy',
                $isDay ? 'partly-cloudy' : 'moon',
                'good',
                'Comfortable weather for rides and express deliveries.',
            ],
            $code === 3 => [
                'Overcast',
                'cloud',
                'good',
                'Cloudy skies. Good visibility for riders on the road.',
            ],
            in_array($code, [45, 48], true) => [
                'Foggy',
                'cloud',
                'caution',
                'Reduced visibility on some roads. Riders will drive with extra care.',
            ],
            in_array($code, [51, 53, 55, 56, 57], true) => [
                'Light drizzle',
                'rain',
                'caution',
                'Light rain detected. Allow a few extra minutes for pickup.',
            ],
            in_array($code, [61, 63, 65, 66, 67, 80, 81, 82], true) => [
                $code >= 65 || $code === 82 ? 'Heavy rain' : 'Rain showers',
                'rain',
                'alert',
                'Wet roads ahead. Riders may travel slower to keep you and your package safe.',
            ],
            in_array($code, [95, 96, 99], true) => [
                'Thunderstorm',
                'storm',
                'alert',
                'Thunderstorm alert. Expect possible delays while riders stay safe.',
            ],
            default => [
                $windKmh >= 30 ? 'Windy' : 'Fair weather',
                'partly-cloudy',
                $windKmh >= 30 ? 'caution' : 'good',
                $windKmh >= 30
                    ? 'Strong gusts reported. Riders are taking extra care.'
                    : 'Normal riding conditions across the city.',
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function fallback(float $lat, float $lng): array
    {
        return [
            'available' => false,
            'lat' => $lat,
            'lng' => $lng,
            'temperature' => null,
            'feels_like' => null,
            'humidity' => null,
            'wind_kmh' => null,
            'rain_chance' => null,
            'weather_code' => null,
            'is_day' => true,
            'label' => 'Weather unavailable',
            'icon' => 'cloud',
            'severity' => 'good',
            'advisory' => 'Live weather feed is temporarily offline. Check local conditions before riding.',
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
