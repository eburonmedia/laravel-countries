<?php

declare(strict_types=1);

use Eburonmedia\LaravelCountries\Facades\Countries;

it('builds the flag filename from the iso code', function () {
    expect(Countries::findByIso2('NL')->flagFilename())->toBe('NL.svg');
});

it('builds the public flag url', function () {
    expect(Countries::findByIso2('NL')->flagUrl())
        ->toContain('vendor/laravel-countries/flags/NL.svg');
});

it('ships a bundled svg flag for every seeded country', function () {
    $flagDir = __DIR__.'/../../resources/flags';

    Countries::all()->each(function ($country) use ($flagDir) {
        expect(file_exists($flagDir.'/'.$country->flagFilename()))
            ->toBeTrue("Missing flag for {$country->iso_3166_2}");
    });
});
