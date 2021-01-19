<?php

namespace App\Structs;

/**
 * @package App\Structs
 *
 * @property string      $country
 * @property string|null $newCases
 * @property string|null $activeCases
 * @property string|null $critical
 * @property string|null $deaths
 * @property string|null $population
 * @property string|null $recovered
 * @property string|null $totalCases
 * @property string|null $newDeaths
 * @property string|null $totalDeaths
 * @property string $date
 */
class StatisticsResultStruct
{
    public string $country;
    public ?string $newCases;
    public ?string $activeCases;
    public ?string $critical;
    public ?string $recovered;
    public ?string $population;
    public ?string $totalCases;
    public ?string $newDeaths;
    public ?string $totalDeaths;
    public string $date;

    /**
     * @param string $body
     * @param string $date
     */
    public function __construct(string $body, string $date)
    {
        $result = json_decode($body)->response[0];

        $this->country =  $result->country;
        $this->population = $result->population;
        $this->newCases = $result->cases->new;
        $this->activeCases = $result->cases->active;
        $this->critical = $result->cases->critical;
        $this->recovered = $result->cases->recovered;
        $this->totalCases = $result->cases->total;
        $this->newDeaths = $result->deaths->new;
        $this->totalDeaths = $result->deaths->total;
        $this->date = $date;
    }
}