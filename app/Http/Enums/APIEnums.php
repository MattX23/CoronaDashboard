<?php

namespace App\Http\Enums;

class APIEnums
{
    const COUNTRIES = 'countries';
    const HISTORY = 'history';
    const BASE_URL = 'https://covid-193.p.rapidapi.com/';

    const ENDPOINTS = [
        self::BASE_URL  => self::BASE_URL,
        self::COUNTRIES => self::COUNTRIES,
        self::HISTORY   => self::HISTORY,
    ];
}