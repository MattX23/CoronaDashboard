<?php

namespace App\Http\Contracts;

use Closure;

interface CovidStatisticsContract
{
    /**
     * @param string $country
     * @param string $date
     *
     * @return \Closure
     */
    public function getByCountryAndDate(string $country, string $date): Closure;
}