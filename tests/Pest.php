<?php

use Eburonmedia\LaravelCountries\Tests\TestCase;

uses(TestCase::class)
    ->beforeEach(function () {
        $this->setUpDatabase();
    })
    ->in(__DIR__);
