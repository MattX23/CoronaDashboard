<?php

namespace App\Models;

use App\Http\Enums\APIEnums;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Stevebauman\Location\Facades\Location as LocationAccessor;
use Stevebauman\Location\Position;

class Location extends Model
{
    /**
     * @return bool|\Stevebauman\Location\Position|null
     */
    public static function getUserLocation(): ?Position
    {
        $ip = Environment::isTesting() ? Environment::randomIP() : request()->ip();

        $location = LocationAccessor::get($ip);

        if (Environment::isTesting() && !$location) {
            return self::getUserLocation();
        }

        return $location ?: null;
    }

    /**
     * @param \Stevebauman\Location\Position|null $location
     *
     * @return bool
     */
    public static function validateCountryName(?Position $location): bool
    {
        if ($location) {
            $countries = self::getCountries();
            $countryName = self::formatCountryName($location->countryName);

            return in_array($countryName, $countries);
        }

        return false;
    }

    /**
     * @return array
     */
    protected static function getCountries(): array
    {
        return Cache::rememberForever("countries", function() {
            return json_decode(
                Http::withHeaders(Config::get('api.corona-api-settings'))
                    ->get(APIEnums::ENDPOINTS[APIEnums::BASE_URL].APIEnums::ENDPOINTS[APIEnums::COUNTRIES])
                    ->body()
            )
                ->response;
        });
    }

    /**
     * @param string $countryName
     *
     * @return string
     */
    public static function formatCountryName(string $countryName): string
    {
        if ($countryName === 'United States') {
            $countryName = 'USA';
        } else if ($countryName === 'United Kingdom') {
            $countryName = 'UK';
        } else if ($countryName === 'United Arab Emirates') {
            $countryName = 'UAE';
        }

        return str_replace(' ', '-', $countryName);
    }
}
