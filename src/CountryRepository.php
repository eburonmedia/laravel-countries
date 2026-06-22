<?php

namespace Eburonmedia\LaravelCountries;

use Eburonmedia\LaravelCountries\Models\Country;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CountryRepository
{
    /**
     * In-memory copy of the hydrated countries for the current request.
     *
     * @var Collection<int, Country>|null
     */
    protected ?Collection $countries = null;

    /**
     * Get all countries, served from the cache when available.
     *
     * The countries are loaded from the database on the first call and cached
     * afterwards, so subsequent lookups never touch the database. Only plain
     * attribute arrays are cached (never hydrated models), which keeps the
     * cached payload safe to serialize across any cache store.
     *
     * @return Collection<int, Country>
     */
    public function all(): Collection
    {
        if ($this->countries !== null) {
            return $this->countries;
        }

        return $this->countries = Country::hydrate($this->cachedRows());
    }

    /**
     * Get the raw country rows from the cache, reading the database on a miss.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function cachedRows(): array
    {
        $ttl = $this->cacheTtl();

        if ($ttl === null) {
            return $this->store()->rememberForever(
                $this->cacheKey(),
                fn (): array => $this->query()
            );
        }

        return $this->store()->remember(
            $this->cacheKey(),
            $ttl,
            fn (): array => $this->query()
        );
    }

    /**
     * Find a country by its primary key.
     */
    public function find(int $id): ?Country
    {
        return $this->all()->firstWhere('id', $id);
    }

    /**
     * Find a country by its ISO 3166-1 alpha-2 code (e.g. "NL").
     */
    public function findByIso2(string $code): ?Country
    {
        $code = strtoupper($code);

        return $this->all()->firstWhere('iso_3166_2', $code);
    }

    /**
     * Find a country by its ISO 3166-1 alpha-3 code (e.g. "NLD").
     */
    public function findByIso3(string $code): ?Country
    {
        $code = strtoupper($code);

        return $this->all()->firstWhere('iso_3166_3', $code);
    }

    /**
     * Find a country by its (case-insensitive) English name.
     */
    public function findByName(string $name): ?Country
    {
        return $this->all()->first(
            fn (Country $country): bool => strcasecmp($country->englishName(), $name) === 0
        );
    }

    /**
     * Get all countries that are members of the European Union.
     *
     * @return Collection<int, Country>
     */
    public function europeanUnion(): Collection
    {
        return $this->all()->where('eu', true)->values();
    }

    /**
     * Get all countries that are not members of the European Union.
     *
     * @return Collection<int, Country>
     */
    public function nonEuropeanUnion(): Collection
    {
        return $this->all()->where('eu', false)->values();
    }

    /**
     * Flush the cached countries, forcing a fresh database read next time.
     */
    public function flush(): void
    {
        $this->countries = null;

        $this->store()->forget($this->cacheKey());
    }

    /**
     * Read the countries fresh from the database as plain attribute arrays.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function query(): array
    {
        return Country::query()
            ->orderBy('name', 'asc')
            ->get()
            ->map
            ->getAttributes()
            ->all();
    }

    /**
     * Resolve the configured cache store.
     */
    protected function store(): CacheRepository
    {
        return Cache::store(config('countries.cache.store'));
    }

    /**
     * Resolve the configured cache key.
     */
    protected function cacheKey(): string
    {
        return (string) config('countries.cache.key', 'eburonmedia.laravel-countries');
    }

    /**
     * Resolve the configured cache time-to-live in seconds, or null for forever.
     */
    protected function cacheTtl(): ?int
    {
        $ttl = config('countries.cache.ttl');

        return $ttl === null ? null : (int) $ttl;
    }
}
