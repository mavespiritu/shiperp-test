<?php

namespace App\Actions;

use App\Services\Weather\WeatherApiException;
use App\Services\Weather\WeatherService;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class GetWeather
{
    use AsAction;

    public function __construct(
        private readonly WeatherService $weatherService
    ) {}

    public function asController(string $city): JsonResponse
    {
        try {
            return response()->json([
                ...$this->weatherService->fetch(trim($city)),
                'source' => 'external',
            ]);
        } catch (WeatherApiException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->statusCode());
        }
    }
}