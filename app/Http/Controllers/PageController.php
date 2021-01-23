<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    const ALL = 'All';

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke()
    {
        $location = Location::getUserLocation();
        $countryName = Location::validateCountryName($location) ? $location->countryName : Location::WORLD_WIDE;
        $searchName = $countryName !== Location::WORLD_WIDE ? Location::formatCountryName($countryName) : Location::ALL;
        $code = $location && $countryName !== Location::WORLD_WIDE ? $location->countryCode : self::ALL;

        if ($location && $countryName) {
            Cache::rememberForever("country-code-$countryName", function() use ($location) {
                return $location->countryCode;
            });
        }

        return view('index')
            ->withCountry($countryName)
            ->withCode($code)
            ->withSearchName($searchName);
    }
}
