<?php

use Illuminate\Support\Facades\Config;

return [
    'corona-api-settings' => [
        'x-rapidapi-key'  => env('X_RAPID_API_KEY'),
        'x-rapidapi-host' => 'covid-193.p.rapidapi.com',
    ],
];
