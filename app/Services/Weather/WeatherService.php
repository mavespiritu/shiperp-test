<?php

namespace App\Services\Weather;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    public function fetch(string $city): array
    {
        $apiKey = config('services.openweathermap.api_key');

        if (!$apiKey) {
            throw new WeatherApiException('Weather API key is not configured.', 500);
        }

        $response = Http::timeout(5)
            ->retry(2, 200, throw: false)
            ->get(config('services.openweathermap.base_url') . '/weather', [
                'q' => $city,
                'appid' => $apiKey,
                'units' => 'metric',
            ]);

        if ($response->failed()) {
            Log::warning('OpenWeatherMap request failed', [
                'city' => $city,
                'status' => $response->status(),
            ]);

            throw new WeatherApiException(
                $response->status() === 404
                    ? 'City not found.'
                    : 'Unable to fetch weather data.',
                $response->status() === 404 ? 404 : 502
            );
        }

        $data = $response->json();

        return [
            'city' => $data['name'] ?? $city,
            'temperature' => $data['main']['temp'] ?? null,
            'weather_description' => $data['weather'][0]['description'] ?? null,
            'timestamp' => now()->toISOString(),
        ];
    }
}