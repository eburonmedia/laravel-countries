# Laravel Countries

A reusable Laravel package that ships a `countries` reference table with a migration, an
Eloquent model, and a seeder containing 250 countries (ISO 3166 codes, currencies,
calling codes, capitals, coordinates, EU/EEA membership and more).

## Table of contents

- [Installation](#installation)
- [Seeding the data](#seeding-the-data)
- [How to use](#how-to-use)
  - [1. Quick start](#1-quick-start)
  - [2. Cached lookups (recommended)](#2-cached-lookups-recommended)
  - [3. Querying with the Eloquent model](#3-querying-with-the-eloquent-model)
  - [4. Working with a country](#4-working-with-a-country)
  - [5. Building a country dropdown](#5-building-a-country-dropdown)
  - [6. Validating a submitted country](#6-validating-a-submitted-country)
  - [7. Using it in a controller](#7-using-it-in-a-controller)
  - [8. Refreshing the cache](#8-refreshing-the-cache)
  - [9. Displaying flags](#9-displaying-flags)
  - [10. Linking a model to a country](#10-linking-a-model-to-a-country)
  - [11. The getCountryName() helper](#11-the-getcountryname-helper)
  - [12. Translated country names](#12-translated-country-names)
- [Cache configuration](#cache-configuration)
- [Flags](#flags)
- [Country model reference](#country-model-reference)
- [API reference](#api-reference)
- [Publishing (optional)](#publishing-optional)
- [Credits](#credits)
- [License](#license)

## Installation

Require the package via Composer:

```bash
composer require eburonmedia/laravel-countries
```

The service provider is auto-discovered. The package migration is loaded automatically,
so you can run:

```bash
php artisan migrate
```

## Seeding the data

Call the bundled seeder from your own `DatabaseSeeder`:

```php
use Eburonmedia\LaravelCountries\Database\Seeders\CountriesSeeder;

public function run(): void
{
    $this->call(CountriesSeeder::class);
}
```

Or run it directly:

```bash
php artisan db:seed --class="Eburonmedia\\LaravelCountries\\Database\\Seeders\\CountriesSeeder"
```

## How to use

### 1. Quick start

After installing, migrating and seeding, you can resolve a country in a single line
using the `Countries` facade. Lookups are served from the cache, so they don't hit
the database after the first read.

```php
use Eburonmedia\LaravelCountries\Facades\Countries;

$country = Countries::findByIso2('NL');

echo $country->name;          // "Netherlands"
echo $country->capital;       // "Amsterdam (NL2)"
echo $country->currency_code; // "EUR"
echo $country->calling_code;  // "31"
```

### 2. Cached lookups (recommended)

The `countries` table rarely changes, so the package caches the full collection on
first read and serves every subsequent lookup from the cache instead of the database.
Use the `Countries` facade for cache-backed helpers:

```php
use Eburonmedia\LaravelCountries\Facades\Countries;

// All 250 countries as a Collection of Country models (cached).
$all = Countries::all();

// Look up a single country.
$byId   = Countries::find(528);             // by primary key
$byIso2 = Countries::findByIso2('nl');      // ISO 3166-1 alpha-2 (case-insensitive)
$byIso3 = Countries::findByIso3('nld');     // ISO 3166-1 alpha-3 (case-insensitive)
$byName = Countries::findByName('belgium'); // by name (case-insensitive)

// Only European Union members.
$euMembers = Countries::europeanUnion();

// All countries outside the European Union.
$nonEuMembers = Countries::nonEuropeanUnion();
```

Prefer dependency injection over the facade? Type-hint the repository and let the
container resolve it (it's registered as a singleton):

```php
use Eburonmedia\LaravelCountries\CountryRepository;

class ShippingService
{
    public function __construct(private CountryRepository $countries) {}

    public function callingCodeFor(string $iso2): ?string
    {
        return $this->countries->findByIso2($iso2)?->calling_code;
    }
}
```

Because everything is a standard Laravel `Collection`, you can keep chaining
collection methods on the results:

```php
use Eburonmedia\LaravelCountries\Facades\Countries;

// Map ISO codes to names for a select list.
$options = Countries::all()->pluck('name', 'iso_3166_2');

// Group countries by their region code.
$byRegion = Countries::all()->groupBy('region_code');

// Every country that uses the euro, sorted by name.
$eurozone = Countries::all()
    ->where('currency_code', 'EUR')
    ->sortBy('name')
    ->values();
```

### 3. Querying with the Eloquent model

When you need to query the database directly (for example with `WHERE`, pagination
or more advanced constraints), use the `Country` model. These calls bypass the cache.

```php
use Eburonmedia\LaravelCountries\Models\Country;

// Single lookups.
$netherlands = Country::where('iso_3166_2', 'NL')->first();
$belgium     = Country::find(56);

// Filtered queries.
$euCountries  = Country::where('eu', true)->orderBy('name')->get();
$eeaCountries = Country::where('eea', true)->pluck('name');

// Search by partial name.
$matches = Country::where('name', 'like', 'United%')->get();
```

### 4. Working with a country

A resolved `Country` exposes every column as a typed attribute:

```php
use Eburonmedia\LaravelCountries\Facades\Countries;

$country = Countries::findByIso2('NL');

$country->name;            // "Netherlands" (localized to the app locale)
$country->full_name;       // "Kingdom of the Netherlands"
$country->capital;         // "Amsterdam (NL2)"
$country->citizenship;     // "Dutch"
$country->iso_3166_2;      // "NL"
$country->iso_3166_3;      // "NLD"
$country->currency;        // "euro"
$country->currency_code;   // "EUR"
$country->currency_symbol; // "€"
$country->calling_code;    // "31"
$country->flag;            // "NL.svg"
$country->lat;             // 52.132633 (float)
$country->lng;             // 5.291266 (float)
$country->eu;              // true (bool)
$country->eea;             // true (bool)
```

### 5. Building a country dropdown

```php
use Eburonmedia\LaravelCountries\Facades\Countries;

$options = Countries::all()
    ->sortBy('name')
    ->pluck('name', 'iso_3166_2');
```

```blade
<select name="country">
    @foreach ($options as $code => $name)
        <option value="{{ $code }}">{{ $name }}</option>
    @endforeach
</select>
```

### 6. Validating a submitted country

Validate that an incoming ISO code actually exists in the countries table:

```php
use Eburonmedia\LaravelCountries\Models\Country;
use Illuminate\Validation\Rule;

$request->validate([
    'country' => [
        'required',
        Rule::exists(Country::class, 'iso_3166_2'),
    ],
]);
```

### 7. Using it in a controller

```php
use Eburonmedia\LaravelCountries\Facades\Countries;
use Illuminate\Http\Request;

class CountryController
{
    public function index()
    {
        return view('countries.index', [
            'countries' => Countries::all(),
        ]);
    }

    public function show(string $iso2)
    {
        $country = Countries::findByIso2($iso2);

        abort_if($country === null, 404);

        return view('countries.show', ['country' => $country]);
    }
}
```

### 8. Refreshing the cache

If you ever change the underlying data (for example after re-seeding), flush the
cached collection so the next lookup reads fresh from the database:

```php
use Eburonmedia\LaravelCountries\Facades\Countries;

Countries::flush();
```

### 9. Displaying flags

The package ships an SVG flag for every country. Publish them once to your public
folder, then use the helpers on the `Country` model to render them:

```bash
php artisan vendor:publish --tag=laravel-countries-flags
```

```php
use Eburonmedia\LaravelCountries\Facades\Countries;

$country = Countries::findByIso2('NL');

$country->flagFilename(); // "NL.svg"
$country->flagUrl();      // "https://your-app.test/vendor/laravel-countries/flags/NL.svg"
$country->flagPath();     // "/path/to/public/vendor/laravel-countries/flags/NL.svg"
```

```blade
<img src="{{ $country->flagUrl() }}" alt="{{ $country->name }} flag" width="32">
```

### 10. Linking a model to a country

If one of your own models stores a country code, add the `HasCountry` trait
instead of repeating `Countries::findByIso2(...)` everywhere. It resolves the
country from the cache, so it adds no extra database queries.

By default the trait reads the ISO 3166-1 alpha-2 code from a `country_code`
column:

```php
use Eburonmedia\LaravelCountries\Concerns\HasCountry;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasCountry;
}
```

```php
$user = User::find(1); // country_code = "NL"

$user->country;          // Country model (via accessor)
$user->country();        // Country model (via method)
$user->country->name;    // "Netherlands"
$user->country->flagUrl();
```

Using a different column? Override `countryCodeColumn()`:

```php
class Address extends Model
{
    use HasCountry;

    public function countryCodeColumn(): string
    {
        return 'iso_3166_2';
    }
}
```

When the code is empty or unknown, the accessor returns `null`.

### 11. The getCountryName() helper

When you just need a country name from an ISO 3166-1 alpha-2 code, use the global
`getCountryName()` helper. It reads from the cache, is case-insensitive, and falls
back to `"Unknown"` for unknown codes (pass your own default to override):

```php
getCountryName('NL');             // "Netherlands"
getCountryName('be');             // "Belgium" (case-insensitive)
getCountryName('ZZ');             // "Unknown" (default fallback)
getCountryName(null);             // "Unknown"
getCountryName('ZZ', 'N/A');      // "N/A" (custom fallback)
getCountryName('ZZ', null);       // null (opt out of the fallback)
getCountryName('NL', 'Unknown', 'nl'); // "Nederland" (translated)
```

```blade
<span>{{ getCountryName($user->country_code) }}</span>
```

### 12. Translated country names

The package ships Dutch (`nl`) translations for all country names, keyed by ISO
3166-1 alpha-2 code. The `name` attribute is **localized automatically** based on
the active application locale, falling back to English when no translation exists:

```php
use Eburonmedia\LaravelCountries\Facades\Countries;

app()->setLocale('nl');
Countries::findByIso2('NL')->name; // "Nederland"

app()->setLocale('en');
Countries::findByIso2('NL')->name; // "Netherlands"
```

Need the untranslated, English name regardless of locale? Use `englishName()`:

```php
$country = Countries::findByIso2('NL');

$country->englishName();          // "Netherlands"
$country->getRawOriginal('name'); // "Netherlands"
```

> Lookups by name (`findByName()`) always match the English name, so they keep
> working no matter which locale is active.

The `getCountryName()` helper follows the app locale too, and accepts an explicit
locale as its third argument to force a specific translation:

```php
app()->setLocale('nl');
getCountryName('NL');                    // "Nederland" (follows app locale)

getCountryName('NL', 'Onbekend', 'nl');  // "Nederland" (explicit locale)
```

Translations are loaded under the `laravel-countries` namespace, so you can also
use Laravel's translator directly:

```php
trans('laravel-countries::countries.NL', [], 'nl'); // "Nederland"
```

Want to add another language or tweak a name? Publish the lang files and edit the
published copy (e.g. add `lang/vendor/laravel-countries/fr/countries.php`):

```bash
php artisan vendor:publish --tag=laravel-countries-translations
```

## Cache configuration

Publish the config to customize the cache store, key and TTL:

```bash
php artisan vendor:publish --tag=laravel-countries-config
```

```php
// config/countries.php
'cache' => [
    'store' => env('COUNTRIES_CACHE_STORE'), // null = default store
    'key' => env('COUNTRIES_CACHE_KEY', 'eburonmedia.laravel-countries'),
    'ttl' => env('COUNTRIES_CACHE_TTL'),     // null = cache forever
],
```

Or configure it entirely through your `.env` file:

```dotenv
COUNTRIES_CACHE_STORE=redis
COUNTRIES_CACHE_KEY=eburonmedia.laravel-countries
COUNTRIES_CACHE_TTL=86400
```

## Flags

The package bundles an SVG flag for all 250 countries under `resources/flags`,
each named after its ISO 3166-1 alpha-2 code (e.g. `NL.svg`). The flags come from
the MIT-licensed [flag-icons](https://github.com/lipis/flag-icons) project.

Publish them to your public directory:

```bash
php artisan vendor:publish --tag=laravel-countries-flags
```

This copies the flags to `public/vendor/laravel-countries/flags`. Customize the
public path via config or `.env`:

```dotenv
COUNTRIES_FLAGS_PATH=vendor/laravel-countries/flags
```

| Helper             | Returns  | Example                                                        |
| ------------------ | -------- | ------------------------------------------------------------- |
| `flagFilename()`   | `string` | `NL.svg`                                                      |
| `flagUrl()`        | `string` | `https://your-app.test/vendor/laravel-countries/flags/NL.svg` |
| `flagPath()`       | `string` | `/path/to/public/vendor/laravel-countries/flags/NL.svg`       |

## Country model reference

Each `Country` record exposes the following attributes:

| Attribute           | Type           | Example                          |
| ------------------- | -------------- | -------------------------------- |
| `id`                | `int`          | `528`                            |
| `name`              | `string`       | `Netherlands` (localized)        |
| `full_name`         | `string\|null` | `Kingdom of the Netherlands`     |
| `capital`           | `string\|null` | `Amsterdam (NL2)`                |
| `citizenship`       | `string\|null` | `Dutch`                          |
| `country_code`      | `string`       | `528`                            |
| `iso_3166_2`        | `string`       | `NL`                             |
| `iso_3166_3`        | `string`       | `NLD`                            |
| `currency`          | `string\|null` | `euro`                           |
| `currency_code`     | `string\|null` | `EUR`                            |
| `currency_sub_unit` | `string\|null` | `cent`                           |
| `currency_symbol`   | `string\|null` | `€`                              |
| `currency_decimals` | `int\|null`    | `2`                              |
| `region_code`       | `string\|null` | `150`                            |
| `sub_region_code`   | `string\|null` | `155`                            |
| `calling_code`      | `string\|null` | `31`                             |
| `flag`              | `string\|null` | `NL.svg`                         |
| `lat`               | `float\|null`  | `52.132633`                      |
| `lng`               | `float\|null`  | `5.291266`                       |
| `eu`                | `bool`         | `true`                           |
| `eea`               | `bool`         | `true`                           |

## API reference

All methods are available on both the `Countries` facade and the injected
`CountryRepository`:

| Method                       | Returns              | Description                                               |
| ---------------------------- | -------------------- | -------------------------------------------------------- |
| `all()`                      | `Collection`         | Every country, sorted by name (cached).                  |
| `find(int $id)`              | `Country\|null`      | A country by its primary key.                            |
| `findByIso2(string $code)`   | `Country\|null`      | A country by ISO 3166-1 alpha-2 code (case-insensitive). |
| `findByIso3(string $code)`   | `Country\|null`      | A country by ISO 3166-1 alpha-3 code (case-insensitive). |
| `findByName(string $name)`   | `Country\|null`      | A country by name (case-insensitive).                    |
| `europeanUnion()`            | `Collection`         | Only EU member countries.                                |
| `nonEuropeanUnion()`         | `Collection`         | All countries outside the EU.                            |
| `flush()`                    | `void`               | Clear the cached collection.                             |

## Publishing (optional)

Publish the migration and/or seeder into your application to customize them:

```bash
php artisan vendor:publish --tag=laravel-countries-config
php artisan vendor:publish --tag=laravel-countries-migrations
php artisan vendor:publish --tag=laravel-countries-seeders
php artisan vendor:publish --tag=laravel-countries-flags
php artisan vendor:publish --tag=laravel-countries-translations
```

## Credits

- The bundled SVG flags come from the MIT-licensed
  [flag-icons](https://github.com/lipis/flag-icons) project by Panayiotis
  Lipiridis. See [CREDITS.md](CREDITS.md) for the full license.
- The translated country names are derived from the Unicode CLDR dataset via
  [umpirsky/country-list](https://github.com/umpirsky/country-list). See
  [CREDITS.md](CREDITS.md) for details.

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
