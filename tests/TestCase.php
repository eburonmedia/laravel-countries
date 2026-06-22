<?php

namespace Eburonmedia\LaravelCountries\Tests;

use Eburonmedia\LaravelCountries\Database\Seeders\CountriesSeeder;
use Eburonmedia\LaravelCountries\LaravelCountriesServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Get the package providers.
     *
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelCountriesServiceProvider::class,
        ];
    }

    /**
     * Define the environment setup.
     *
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('cache.default', 'array');
    }

    /**
     * Run the package migrations and seed the countries.
     */
    protected function setUpDatabase(): void
    {
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        (new CountriesSeeder)->run();
    }
}
