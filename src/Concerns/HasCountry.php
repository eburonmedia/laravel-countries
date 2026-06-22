<?php

namespace Eburonmedia\LaravelCountries\Concerns;

use Eburonmedia\LaravelCountries\Facades\Countries;
use Eburonmedia\LaravelCountries\Models\Country;

/**
 * Adds a cached "country" accessor to any model that stores a country code.
 *
 * By default the trait reads the ISO 3166-1 alpha-2 code from a "country_code"
 * attribute. Override countryCodeColumn() to point at a different column, or use
 * countryFromColumn() to resolve a country from any column (handy when a model
 * stores more than one country code, e.g. billing and shipping).
 */
trait HasCountry
{
    /**
     * The model's country, resolved from the cached countries collection.
     */
    public function country(): ?Country
    {
        return $this->countryFromColumn($this->countryCodeColumn());
    }

    /**
     * Resolve a country from the ISO 3166-1 alpha-2 code held in the given column.
     *
     * Useful when a model stores multiple country codes (e.g. a billing and a
     * shipping country) and you want a dedicated accessor for each.
     */
    public function countryFromColumn(string $column): ?Country
    {
        $code = $this->getAttribute($column);

        if (empty($code)) {
            return null;
        }

        return Countries::findByIso2((string) $code);
    }

    /**
     * Access the related country via a "country" attribute (e.g. $model->country).
     */
    public function getCountryAttribute(): ?Country
    {
        return $this->country();
    }

    /**
     * The column holding the ISO 3166-1 alpha-2 country code.
     */
    public function countryCodeColumn(): string
    {
        return 'country_code';
    }
}
