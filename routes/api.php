<?php

use App\Actions\GetWeather;
use App\Actions\GetCachedWeather;
use Illuminate\Support\Facades\Route;

Route::get('/weather/{city}', GetWeather::class);
Route::get('/weather/{city}/cached', GetCachedWeather::class);