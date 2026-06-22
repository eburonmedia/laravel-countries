<?php

namespace Eburonmedia\LaravelCountries\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;

/**
 * @property int $id
 * @property string|null $capital
 * @property string|null $citizenship
 * @property string $country_code
 * @property string|null $currency
 * @property string|null $currency_code
 * @property string|null $currency_sub_unit
 * @property string|null $currency_symbol
 * @property int|null $currency_decimals
 * @property string|null $full_name
 * @property string $iso_3166_2
 * @property string $iso_3166_3
 * @property string $name
 * @property string|null $region_code
 * @property string|null $sub_region_code
 * @property bool $eea
 * @property string|null $calling_code
 * @property string|null $flag
 * @property float|null $lat
 * @property float|null $lng
 * @property bool $eu
 */
class Country extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'countries';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'currency_decimals' => 'integer',
            'eea' => 'boolean',
            'eu' => 'boolean',
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    /**
     * Get the localized country name based on the active application locale.
     *
     * Falls back to the English name when no translation exists for the locale.
     * Use getRawOriginal('name') or englishName() to read the untranslated name.
     */
    public function getNameAttribute(?string $value): string
    {
        $key = 'laravel-countries::countries.'.$this->attributes['iso_3166_2'];

        if (Lang::has($key)) {
            return Lang::get($key);
        }

        return (string) $value;
    }

    /**
     * Get the untranslated, English country name.
     */
    public function englishName(): string
    {
        return (string) $this->getRawOriginal('name');
    }

    /**
     * Get the flag filename for this country (e.g. "NL.svg").
     */
    public function flagFilename(): string
    {
        $extension = (string) config('countries.flags.extension', 'svg');

        return $this->iso_3166_2.'.'.$extension;
    }

    /**
     * Get the public URL to this country's flag.
     */
    public function flagUrl(): string
    {
        $path = trim((string) config('countries.flags.path', 'vendor/laravel-countries/flags'), '/');

        return asset($path.'/'.$this->flagFilename());
    }

    /**
     * Get the absolute filesystem path to this country's published flag.
     */
    public function flagPath(): string
    {
        $path = trim((string) config('countries.flags.path', 'vendor/laravel-countries/flags'), '/');

        return public_path($path.'/'.$this->flagFilename());
    }
}
