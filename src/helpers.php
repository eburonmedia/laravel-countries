<?php

use Eburonmedia\LaravelCountries\Facades\Countries;
use Illuminate\Support\Facades\Lang;

if (! function_exists('getCountryName')) {
    /**
     * Get a country name from its ISO 3166-1 alpha-2 code (e.g. "NL").
     *
     * Without a locale the name follows the active application locale (same as
     * $country->name). Pass a locale to force a specific translation; it falls
     * back to the English name when no translation exists. Returns the given
     * default ("Unknown" by default) when the code is unknown.
     */
    function getCountryName(?string $iso2, ?string $default = 'Unknown', ?string $locale = null): ?string
    {
        if (empty($iso2)) {
            return $default;
        }

        $country = Countries::findByIso2($iso2);

        if ($country === null) {
            return $default;
        }

        if ($locale === null) {
            return $country->name;
        }

        $key = 'laravel-countries::countries.'.$country->iso_3166_2;

        return Lang::has($key, $locale) ? Lang::get($key, [], $locale) : $country->englishName();
    }
}
