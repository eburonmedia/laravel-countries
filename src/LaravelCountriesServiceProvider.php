<?php

namespace Eburonmedia\LaravelCountries;

use Illuminate\Support\ServiceProvider;

class LaravelCountriesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravel-countries');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/countries.php' => config_path('countries.php'),
            ], 'laravel-countries-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'laravel-countries-migrations');

            $this->publishes([
                __DIR__.'/../database/seeders' => database_path('seeders'),
            ], 'laravel-countries-seeders');

            $this->publishes([
                __DIR__.'/../resources/flags' => public_path('vendor/laravel-countries/flags'),
            ], 'laravel-countries-flags');

            $this->publishes([
                __DIR__.'/../lang' => $this->app->langPath('vendor/laravel-countries'),
            ], 'laravel-countries-translations');
        }
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/countries.php', 'countries');

        $this->app->singleton(CountryRepository::class);
    }
}
