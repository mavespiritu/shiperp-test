# SHIPERP

SHIPERP is a Laravel 13 application that exposes a small weather API backed by OpenWeatherMap.

It includes:

- A public landing page at `/`
- Weather endpoints at `/api/weather/{city}` and `/api/weather/{city}/cached`
- Cached weather responses with a 10-minute TTL
- JSON error responses for missing API keys, unknown cities, and upstream failures

## Tech Stack

- PHP 8.3
- Laravel 13
- Laravel Sanctum
- Laravel Actions
- Vite
- Tailwind CSS 4
- Pest for testing

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- A MySQL database
- An OpenWeatherMap API key

## Setup

1. Install PHP dependencies:

   ```bash
   composer install
   ```

2. Create your environment file:

   ```bash
   cp .env.example .env
   ```

3. Generate the application key:

   ```bash
   php artisan key:generate
   ```

4. Configure your `.env` values, especially:

   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
   - `OPENWEATHERMAP_API_KEY`

5. Run the database migrations:

   ```bash
   php artisan migrate
   ```

6. Install frontend dependencies and build assets:

   ```bash
   npm install
   npm run build
   ```

## Running The App

Start the Laravel server:

```bash
php artisan serve
```

If you want the full local development stack, use the Composer dev script:

```bash
composer run dev
```

That starts:

- `php artisan serve`
- `php artisan queue:listen`
- `php artisan pail`
- `npm run dev`

## API

### `GET /api/weather/{city}`

Fetches the latest weather data for a city from OpenWeatherMap.

Example response:

```json
{
  "city": "Manila",
  "temperature": 30.5,
  "weather_description": "scattered clouds",
  "timestamp": "2026-06-02T00:00:00.000000Z",
  "source": "external"
}
```

### `GET /api/weather/{city}/cached`

Returns the weather data from cache when available, otherwise fetches fresh data and stores it for 10 minutes.

The `source` field will be:

- `external` on the first request
- `cache` on subsequent requests within the cache window

## Error Responses

The weather endpoints return JSON errors when something goes wrong:

- `500` if `OPENWEATHERMAP_API_KEY` is missing
- `404` if the city is not found
- `502` if the upstream weather service fails

## Testing

Run the test suite with:

```bash
php artisan test
```

The weather API tests fake the OpenWeatherMap HTTP response, so they can run without hitting the real service.

## Notes

- The root page at `/` is currently a simple Laravel welcome view.
- Weather configuration lives in [`config/services.php`](config/services.php).
- The weather logic is implemented in [`app/Services/Weather/WeatherService.php`](app/Services/Weather/WeatherService.php).
