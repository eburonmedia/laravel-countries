<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | The countries table rarely changes, so its contents are cached to avoid
    | hitting the database on every lookup. Configure the cache store, key and
    | time-to-live below. A null "ttl" caches the countries forever (until the
    | cache is flushed manually via Countries::flush()).
    |
    */

    'cache' => [

        // The cache store to use. Null uses the application's default store.
        'store' => env('COUNTRIES_CACHE_STORE'),

        // The cache key used to store the countries collection.
        'key' => env('COUNTRIES_CACHE_KEY', 'eburonmedia.laravel-countries'),

        // Time-to-live in seconds. Null caches forever.
        'ttl' => env('COUNTRIES_CACHE_TTL'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Flag Settings
    |--------------------------------------------------------------------------
    |
    | The package ships an SVG flag for every country, named after its ISO
    | 3166-1 alpha-2 code (e.g. "NL.svg"). Publish them to your public folder
    | with "php artisan vendor:publish --tag=laravel-countries-flags". The
    | "path" below is relative to the public directory and is used by the
    | Country::flagUrl() helper to build the public URL.
    |
    */

    'flags' => [

        // Public path (relative to public/) where the flags are published.
        'path' => env('COUNTRIES_FLAGS_PATH', 'vendor/laravel-countries/flags'),

        // The file extension of the bundled flags.
        'extension' => 'svg',

    ],

];
