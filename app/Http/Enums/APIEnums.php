<?php

namespace App\Http\Enums;

class APIEnums
{
    const COUNTRIES = 'countries';
    const HISTORY = 'history';
    const BASE_URL = 'base_url';

    const ENDPOINTS = [
        self::BASE_URL  => 'https://covid-193.p.rapidapi.com/',
        self::COUNTRIES => self::COUNTRIES,
        self::HISTORY   => self::HISTORY,
    ];
}