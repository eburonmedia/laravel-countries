<?php

declare(strict_types=1);

use Eburonmedia\LaravelCountries\Facades\Countries;
use Illuminate\Support\Facades\Lang;

it('localizes the name to dutch when the app locale is nl', function () {
    app()->setLocale('nl');

    expect(Countries::findByIso2('NL')->name)->toBe('Nederland')
        ->and(Countries::findByIso2('DE')->name)->toBe('Duitsland')
        ->and(Countries::findByIso2('GB')->name)->toBe('Verenigd Koninkrijk');
});

it('uses the english name by default', function () {
    expect(Countries::findByIso2('NL')->name)->toBe('Netherlands')
        ->and(Countries::findByIso2('BE')->name)->toBe('Belgium');
});

it('falls back to the english name for an untranslated locale', function () {
    app()->setLocale('xx');

    expect(Countries::findByIso2('NL')->name)->toBe('Netherlands');
});

it('always exposes the raw english name regardless of locale', function () {
    app()->setLocale('nl');

    $country = Countries::findByIso2('NL');

    expect($country->englishName())->toBe('Netherlands')
        ->and($country->getRawOriginal('name'))->toBe('Netherlands');
});

it('finds a country by its english name regardless of locale', function () {
    app()->setLocale('nl');

    expect(Countries::findByName('Netherlands')?->iso_3166_2)->toBe('NL');
});

it('ships a dutch name for every seeded country', function () {
    Countries::all()->each(function ($country) {
        $key = 'laravel-countries::countries.'.$country->iso_3166_2;

        expect(Lang::has($key, 'nl'))
            ->toBeTrue("Missing dutch name for {$country->iso_3166_2}");
    });
});

it('returns the localized name through the helper using the app locale', function () {
    app()->setLocale('nl');

    expect(getCountryName('NL'))->toBe('Nederland');
});

it('returns a specific locale through the helper', function () {
    expect(getCountryName('NL', 'Unknown', 'nl'))->toBe('Nederland')
        ->and(getCountryName('ZZ', 'Onbekend', 'nl'))->toBe('Onbekend');
});
