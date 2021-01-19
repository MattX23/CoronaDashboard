<?php

namespace App\Http\Controllers;

use App\Http\Contracts\CovidStatisticsContract;
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

        $currentStats = new StatisticsResultStruct(
            $this->dateIsInThePast($today, $date) ?
                Cache::rememberForever("stats-history-$country-$date", $covidStatisticsService->getByCountryAndDate($country, $date)) :
                Cache::remember("stats-history-$country-$date", 14400, $covidStatisticsService->getByCountryAndDate($country, $date)),
                $date
        );

        $lastMonthDate = $today->subMonth()->format('Y-m-d');

        $lastMonthStats = new StatisticsResultStruct(
            Cache::rememberForever("stats-history-$country-$lastMonthDate", $covidStatisticsService->getByCountryAndDate($country, $lastMonthDate)),
            $lastMonthDate
        );

        return json_encode(['current' => $currentStats, 'previous' => $lastMonthStats]);
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
