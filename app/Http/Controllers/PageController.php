<?php

namespace App\Http\Controllers;

use App\Models\Location;

class PageController extends Controller
{
    const GLOBAL = 'ALL';

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke()
    {
        $location = Location::getUserLocation();
        $countryName = Location::validateCountryName($location) ? $location->countryName : self::GLOBAL;
        $searchName = $countryName !== self::GLOBAL ? Location::formatCountryName($countryName) : self::GLOBAL;

        $code = $location ? $location->countryCode : null;

        return view('index')
            ->withCountry($countryName)
            ->withCode($code)
            ->withSearchName($searchName);
    }
}
