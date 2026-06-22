<?php

declare(strict_types=1);

it('returns the country name for a known iso code', function () {
    expect(getCountryName('NL'))->toBe('Netherlands');
});

it('is case-insensitive', function () {
    expect(getCountryName('be'))->toBe('Belgium');
});

it('defaults to "Unknown" for an unknown iso code', function () {
    expect(getCountryName('ZZ'))->toBe('Unknown');
});

it('defaults to "Unknown" for an empty value', function () {
    expect(getCountryName(null))->toBe('Unknown')
        ->and(getCountryName(''))->toBe('Unknown');
});

it('returns the given default when the code is unknown', function () {
    expect(getCountryName('ZZ', 'N/A'))->toBe('N/A')
        ->and(getCountryName(null, 'N/A'))->toBe('N/A');
});

it('allows null as an explicit default', function () {
    expect(getCountryName('ZZ', null))->toBeNull()
        ->and(getCountryName(null, null))->toBeNull();
});
