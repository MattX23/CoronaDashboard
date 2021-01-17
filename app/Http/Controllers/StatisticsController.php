<?php

namespace App\Http\Controllers;

use App\Http\Enums\APIEnums;
use App\Structs\StatisticsResultStruct;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class StatisticsController extends Controller
{
    /**
     * @param string|null $country
     * @param string|null $date
     *
     * @return string
     */
    public function getStatsByCountryAndDate(string $country, string $date = null): string
    {
        $today = Carbon::now()->startOfDay();
        $date = $date ?? $today->format('Y-m-d');

        $callback = function () use ($country, $date) {
            return Http::withHeaders(Config::get('api.corona-api-settings'))->get(
                APIEnums::ENDPOINTS[APIEnums::BASE_URL] . APIEnums::ENDPOINTS[APIEnums::HISTORY]."?country=$country&day=$date"
            )->body();
        };

        return json_encode(
            new StatisticsResultStruct(
                $this->dateIsInThePast($today, $date) ?
                    Cache::rememberForever("stats-history-$country-$date", $callback) :
                    Cache::remember("stats-history-$country-$date", 3600, $callback)
            )
        );
    }

    /**
     * @param \Carbon\Carbon $today
     * @param string|null    $date
     *
     * @return bool
     */
    protected function dateIsInThePast(Carbon $today, ?string $date): bool
    {
        return $date && Carbon::createFromDate($date)->diffInDays($today) > 0;
    }
}
