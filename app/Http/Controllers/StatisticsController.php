<?php

namespace App\Http\Controllers;

use App\Http\Contracts\CovidStatisticsContract;
use App\Models\Location;
use App\Structs\StatisticsResultStruct;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class StatisticsController extends Controller
{
    /**
     * @param \App\Http\Contracts\CovidStatisticsContract $covidStatisticsService
     * @param string|null                                 $country
     * @param string|null                                 $date
     *
     * @return string
     */
    public function getStatsByCountryAndDate(
        CovidStatisticsContract $covidStatisticsService,
        string $country,
        string $date = null
    ): string {
        $today = Carbon::now()->startOfDay();
        $date = $date ?? $today->format('Y-m-d');

        if ($country === Location::WORLD_WIDE) $country = Location::ALL;

        $currentStats = $this->getCurrentStats($today, $date, $country, $covidStatisticsService);
        $lastMonthDate = $today->subMonth()->format('Y-m-d');
        $lastMonthStats = $this->getLastMonthStats($country, $lastMonthDate, $covidStatisticsService);
        $countryCode = $this->getCountryCode($country);

        return json_encode(['current' => $currentStats, 'previous' => $lastMonthStats, 'countryCode' => $countryCode]);
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

    /**
     * @param \Carbon\Carbon                              $today
     * @param string                                      $date
     * @param string                                      $country
     * @param \App\Http\Contracts\CovidStatisticsContract $covidStatisticsService
     *
     * @return \App\Structs\StatisticsResultStruct
     */
    protected function getCurrentStats(Carbon $today, string $date, string $country, CovidStatisticsContract $covidStatisticsService): StatisticsResultStruct
    {
        return new StatisticsResultStruct(
            $this->dateIsInThePast($today, $date) ?
                Cache::rememberForever("stats-history-$country-$date", $covidStatisticsService->getByCountryAndDate($country, $date)) :
                Cache::remember("stats-history-$country-$date", 14400, $covidStatisticsService->getByCountryAndDate($country, $date)),
            $date
        );
    }

    /**
     * @param string                                      $country
     * @param string                                      $lastMonthDate
     * @param \App\Http\Contracts\CovidStatisticsContract $covidStatisticsService
     *
     * @return \App\Structs\StatisticsResultStruct
     */
    protected function getLastMonthStats(string $country, string $lastMonthDate, CovidStatisticsContract $covidStatisticsService): StatisticsResultStruct
    {
        $lastMonthStats =  new StatisticsResultStruct(
            Cache::rememberForever("stats-history-$country-$lastMonthDate", $covidStatisticsService->getByCountryAndDate($country, $lastMonthDate)),
            $lastMonthDate
        );

        if (!isset($lastMonthStats->totalCases)) Cache::forget("stats-history-$country-$lastMonthDate");

        return $lastMonthStats;
    }

    /**
     * @param string $country
     *
     * @return string|null
     */
    protected function getCountryCode(string $country): ?string
    {
        $countryCode = Cache::rememberForever("country-code-$country", function() use ($country) {
            return Location::getCountryCode(str_replace('-', ' ', $country));
        });

        if (!$countryCode) Cache::forget("country-code-$country");

        return $countryCode;
    }
}
