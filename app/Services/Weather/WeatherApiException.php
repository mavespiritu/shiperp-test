<?php

namespace App\Services\Weather;

use Exception;

class WeatherApiException extends Exception
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 500
    ) {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}