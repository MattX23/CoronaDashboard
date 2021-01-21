<?php

use App\Http\Controllers\CountryController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('statistics/{country?}/{date?}', [StatisticsController::class, 'getStatsByCountryAndDate']);
Route::get('countries', [CountryController::class, 'getCountries']);
