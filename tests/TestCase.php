<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Vinit\LaravelAiPhoenix\PhoenixServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [PhoenixServiceProvider::class];
    }
}
