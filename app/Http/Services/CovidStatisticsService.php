<?php

namespace App\Http\Services;

use App\Http\Contracts\CovidStatisticsContract;
use App\Http\Enums\APIEnums;
use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class CovidStatisticsService implements CovidStatisticsContract
{
    /**
     * @param string $country
     * @param string $date
     *
     * @return \Closure
     */
    public function getByCountryAndDate(string $country, string $date): Closure
    {
        sleep(1);
        return function () use ($country, $date) {
            return Http::withHeaders(Config::get('api.corona-api-settings'))->get(
                APIEnums::ENDPOINTS[APIEnums::BASE_URL] . APIEnums::ENDPOINTS[APIEnums::HISTORY]."?country=$country&day=$date"
            )->body();
        };
    }
}