<?php

declare(strict_types=1);

use Eburonmedia\LaravelCountries\Concerns\HasCountry;
use Eburonmedia\LaravelCountries\Models\Country;
use Illuminate\Database\Eloquent\Model;

/**
 * A throwaway model that uses the trait with the default "country_code" column.
 */
class TraitUser extends Model
{
    use HasCountry;

    protected $guarded = [];

    public $timestamps = false;
}

/**
 * A throwaway model that overrides the country code column.
 */
class TraitAddress extends Model
{
    use HasCountry;

    protected $guarded = [];

    public $timestamps = false;

    public function countryCodeColumn(): string
    {
        return 'iso';
    }
}

it('resolves the country from the default column via method', function () {
    $user = new TraitUser(['country_code' => 'NL']);

    expect($user->country())
        ->toBeInstanceOf(Country::class)
        ->and($user->country()->name)->toBe('Netherlands');
});

it('resolves the country via the country attribute', function () {
    $user = new TraitUser(['country_code' => 'be']);

    expect($user->country)
        ->toBeInstanceOf(Country::class)
        ->and($user->country->name)->toBe('Belgium');
});

it('resolves the country from a custom column', function () {
    $address = new TraitAddress(['iso' => 'FR']);

    expect($address->country()?->name)->toBe('France');
});

it('returns null when the country code is empty', function () {
    expect((new TraitUser(['country_code' => null]))->country())->toBeNull()
        ->and((new TraitUser(['country_code' => '']))->country())->toBeNull();
});

it('returns null when the country code does not exist', function () {
    expect((new TraitUser(['country_code' => 'ZZ']))->country())->toBeNull();
});
