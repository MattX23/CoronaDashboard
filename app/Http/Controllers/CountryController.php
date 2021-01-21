<?php

namespace App\Http\Controllers;

use App\Models\Location;

class CountryController extends Controller
{
    /**
     * @return string
     */
    public function getCountries(): string
    {
        return json_encode(
            collect(Location::getCountries())
                ->map(fn(string $country) => [
                    'searchTerm' => $country,
                    'name'       => str_replace('-', ' ', $country)
                ])
        );
    }
}
