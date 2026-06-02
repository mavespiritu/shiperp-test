<?php

namespace App\Actions;

use App\Services\Weather\WeatherApiException;
use App\Services\Weather\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCachedWeather
{
    use AsAction;

    public function __construct(
        private readonly WeatherService $weatherService
    ) {}

    public function asController(string $city): JsonResponse
    {
        $city = trim($city);
        $cacheKey = 'weather:' . strtolower($city);

        try {
            $wasCached = Cache::has($cacheKey);

            $weather = Cache::remember(
                $cacheKey,
                now()->addMinutes(10),
                fn () => $this->weatherService->fetch($city)
            );

            return response()->json([
                ...$weather,
                'source' => $wasCached ? 'cache' : 'external',
            ]);
        } catch (WeatherApiException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->statusCode());
        }
    }
}