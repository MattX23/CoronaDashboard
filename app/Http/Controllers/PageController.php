<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    const ALL = 'ALL';
    const GLOBAL = 'World Wide';

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke()
    {
        $location = Location::getUserLocation();
        $countryName = Location::validateCountryName($location) ? $location->countryName : self::GLOBAL;
        $searchName = $countryName !== self::GLOBAL ? Location::formatCountryName($countryName) : self::ALL;
        $code = $location && $countryName !== self::GLOBAL ? $location->countryCode : null;

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
