<?php

namespace Eburonmedia\LaravelCountries\Facades;

use Eburonmedia\LaravelCountries\CountryRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection<int, \Eburonmedia\LaravelCountries\Models\Country> all()
 * @method static \Eburonmedia\LaravelCountries\Models\Country|null find(int $id)
 * @method static \Eburonmedia\LaravelCountries\Models\Country|null findByIso2(string $code)
 * @method static \Eburonmedia\LaravelCountries\Models\Country|null findByIso3(string $code)
 * @method static \Eburonmedia\LaravelCountries\Models\Country|null findByName(string $name)
 * @method static \Illuminate\Support\Collection<int, \Eburonmedia\LaravelCountries\Models\Country> europeanUnion()
 * @method static \Illuminate\Support\Collection<int, \Eburonmedia\LaravelCountries\Models\Country> nonEuropeanUnion()
 * @method static void flush()
 *
 * @see CountryRepository
 */
class Countries extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return CountryRepository::class;
    }
}
