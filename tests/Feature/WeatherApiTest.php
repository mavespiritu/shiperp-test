<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.openweathermap.api_key' => 'fake-key',
        'services.openweathermap.base_url' => 'https://api.openweathermap.org/data/2.5',
    ]);

    Cache::flush();
});

it('fetches weather from the external api', function () {
    Http::fake([
        'api.openweathermap.org/*' => Http::response([
            'name' => 'Manila',
            'main' => [
                'temp' => 30.5,
            ],
            'weather' => [
                [
                    'description' => 'scattered clouds',
                ],
            ],
        ]),
    ]);

    $this->getJson('/api/weather/Manila')
        ->assertOk()
        ->assertJson([
            'city' => 'Manila',
            'temperature' => 30.5,
            'weather_description' => 'scattered clouds',
            'source' => 'external',
        ])
        ->assertJsonStructure([
            'city',
            'temperature',
            'weather_description',
            'timestamp',
            'source',
        ]);

    Http::assertSentCount(1);
});

it('returns external source on first cached request', function () {
    Http::fake([
        'api.openweathermap.org/*' => Http::response([
            'name' => 'Manila',
            'main' => [
                'temp' => 30.5,
            ],
            'weather' => [
                [
                    'description' => 'clear sky',
                ],
            ],
        ]),
    ]);

    $this->getJson('/api/weather/Manila/cached')
        ->assertOk()
        ->assertJson([
            'city' => 'Manila',
            'temperature' => 30.5,
            'weather_description' => 'clear sky',
            'source' => 'external',
        ]);

    Http::assertSentCount(1);
});

it('returns cache source on second cached request', function () {
    Http::fake([
        'api.openweathermap.org/*' => Http::response([
            'name' => 'Manila',
            'main' => [
                'temp' => 30.5,
            ],
            'weather' => [
                [
                    'description' => 'clear sky',
                ],
            ],
        ]),
    ]);

    $this->getJson('/api/weather/Manila/cached')
        ->assertOk()
        ->assertJson([
            'source' => 'external',
        ]);

    $this->getJson('/api/weather/Manila/cached')
        ->assertOk()
        ->assertJson([
            'source' => 'cache',
        ]);

    Http::assertSentCount(1);
});

it('handles city not found from the external api', function () {
    Http::fake([
        'api.openweathermap.org/*' => Http::response([
            'message' => 'city not found',
        ], 404),
    ]);

    $this->getJson('/api/weather/InvalidCity')
        ->assertStatus(404)
        ->assertJson([
            'message' => 'City not found.',
        ]);
});

it('handles external api failure', function () {
    Http::fake([
        'api.openweathermap.org/*' => Http::response([
            'message' => 'server error',
        ], 500),
    ]);

    $this->getJson('/api/weather/Manila')
        ->assertStatus(502)
        ->assertJson([
            'message' => 'Unable to fetch weather data.',
        ]);
});

it('requires an api key to be configured', function () {
    config([
        'services.openweathermap.api_key' => null,
    ]);

    $this->getJson('/api/weather/Manila')
        ->assertStatus(500)
        ->assertJson([
            'message' => 'Weather API key is not configured.',
        ]);
});