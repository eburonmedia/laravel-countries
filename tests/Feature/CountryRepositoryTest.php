<?php

declare(strict_types=1);

use Eburonmedia\LaravelCountries\CountryRepository;
use Eburonmedia\LaravelCountries\Facades\Countries;
use Eburonmedia\LaravelCountries\Models\Country;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->repository = app(CountryRepository::class);
    $this->repository->flush();
});

it('returns all countries', function () {
    expect($this->repository->all())
        ->toHaveCount(250)
        ->each->toBeInstanceOf(Country::class);
});

it('returns the countries ordered by name ascending', function () {
    $names = $this->repository->all()->pluck('name');

    expect($names->values()->all())->toBe($names->sort()->values()->all());
});

it('caches the countries after the first read', function () {
    // First read populates the cache.
    $this->repository->all();

    DB::enableQueryLog();

    $this->repository->all();
    $this->repository->all();

    expect(DB::getQueryLog())->toBeEmpty();
});

it('caches a serialization-safe payload of plain arrays', function () {
    $this->repository->all();

    $cached = Cache::store(config('countries.cache.store'))
        ->get(config('countries.cache.key'));

    expect($cached)->toBeArray()
        ->and($cached[0])->toBeArray()
        ->and(unserialize(serialize($cached)))->toBe($cached);
});

it('rehydrates countries from the cache without hitting the database', function () {
    // Populate the cache with one instance.
    $this->repository->all();

    DB::enableQueryLog();

    // A fresh instance must read from the cache, not the database.
    $countries = (new CountryRepository)->all();

    expect(DB::getQueryLog())->toBeEmpty()
        ->and($countries)->toHaveCount(250)
        ->each->toBeInstanceOf(Country::class)
        ->and($countries->firstWhere('iso_3166_2', 'NL')->name)->toBe('Netherlands');
});

it('reads from the database when the cache is empty', function () {
    DB::enableQueryLog();

    $this->repository->all();

    expect(DB::getQueryLog())->not->toBeEmpty();
});

it('reads from the database again after the cache is flushed', function () {
    $this->repository->all();

    $this->repository->flush();

    DB::enableQueryLog();

    $this->repository->all();

    expect(DB::getQueryLog())->not->toBeEmpty();
});

it('finds a country by its primary key', function () {
    $country = $this->repository->find(528);

    expect($country)
        ->not->toBeNull()
        ->and($country->name)->toBe('Netherlands');
});

it('finds a country by its ISO 3166-2 code regardless of case', function () {
    expect($this->repository->findByIso2('nl')?->name)->toBe('Netherlands')
        ->and($this->repository->findByIso2('NL')?->name)->toBe('Netherlands');
});

it('finds a country by its ISO 3166-3 code regardless of case', function () {
    expect($this->repository->findByIso3('nld')?->name)->toBe('Netherlands')
        ->and($this->repository->findByIso3('NLD')?->name)->toBe('Netherlands');
});

it('finds a country by its name regardless of case', function () {
    expect($this->repository->findByName('netherlands')?->iso_3166_2)->toBe('NL');
});

it('returns null when a country cannot be found', function () {
    expect($this->repository->find(999999))->toBeNull()
        ->and($this->repository->findByIso2('ZZ'))->toBeNull()
        ->and($this->repository->findByName('Atlantis'))->toBeNull();
});

it('lookups do not hit the database once cached', function () {
    $this->repository->all();

    DB::enableQueryLog();

    $this->repository->find(528);
    $this->repository->findByIso2('NL');
    $this->repository->findByName('Belgium');

    expect(DB::getQueryLog())->toBeEmpty();
});

it('returns only european union members', function () {
    $euCountries = $this->repository->europeanUnion();

    expect($euCountries)->each->toHaveKey('eu')
        ->and($euCountries->pluck('eu')->unique()->all())->toBe([true]);
});

it('returns only non european union members', function () {
    $nonEuCountries = $this->repository->nonEuropeanUnion();

    expect($nonEuCountries)->each->toHaveKey('eu')
        ->and($nonEuCountries->pluck('eu')->unique()->all())->toBe([false]);
});

it('splits all countries into eu and non-eu members', function () {
    $total = $this->repository->all()->count();
    $eu = $this->repository->europeanUnion()->count();
    $nonEu = $this->repository->nonEuropeanUnion()->count();

    expect($eu + $nonEu)->toBe($total);
});

it('resolves countries through the facade', function () {
    expect(Countries::all())->toHaveCount(250)
        ->and(Countries::findByIso2('NL')?->name)->toBe('Netherlands');
});
