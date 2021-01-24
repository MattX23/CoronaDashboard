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
    const COUNTRY_CODE_ENDPOINT = "https://restcountries.eu/rest/v2/name/";
    const FULL_TEXT_SEARCH = '?fullText=true';
    const WORLD_WIDE = 'World Wide';
    const ALL = 'all';

    /**
     * @return bool|\Stevebauman\Location\Position|null
     */
    public static function getUserLocation(): ?Position
    {
//        $ip = Environment::isTesting() ? Environment::randomIP() : request()->ip();
        $ip = Environment::randomIP();

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
    public static function getCountries(): array
    {
        return Cache::rememberForever("countries", function() {
            return collect(
                json_decode(
                    Http::withHeaders(Config::get('api.corona-api-settings'))
                        ->get(APIEnums::ENDPOINTS[APIEnums::BASE_URL] . APIEnums::ENDPOINTS[APIEnums::COUNTRIES])
                        ->body()
                )
                    ->response
            )
                ->map(fn(string $country) => str_contains($country, '&') ? '' : $country)
                ->reject(fn(string $country) => !$country)
                ->reject(fn(string $country) => str_ends_with($country, '-'))
                ->push(self::WORLD_WIDE)
                ->toArray();
        });
    }

    /**
     * @param string $country
     *
     * @return string|null
     */
    public static function getCountryCode(string $country): ?string
    {
        if ($country === self::ALL) return null;

        $country = Location::normaliseCountryName($country);
        $countryCode = Cache::get("country-code-$country");

        if (!$countryCode) {
            $countryInfo = Http::get(self::COUNTRY_CODE_ENDPOINT.str_replace(' ', '%20', $country).self::FULL_TEXT_SEARCH)
                ->body();

            if ($countryInfo) {
                $countryCode = is_array(json_decode($countryInfo)) ? json_decode($countryInfo)[0]->alpha2Code : null;
            }
        }

        return $countryCode;
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
        } else if ($countryName === 'South Korea') {
            $countryName = 'Korea';
        }

        return str_replace(' ', '-', $countryName);
    }

    /**
     * @param string $countryName
     *
     * @return string|null
     */
    public static function normaliseCountryName(string $countryName): ?string
    {
        if ($countryName === 'UK') {
            $countryName = 'United Kingdom of Great Britain and Northern Ireland';
        } else if ($countryName === 'World Wide') {
            $countryName = null;
        }

        return $countryName;
    }
}
