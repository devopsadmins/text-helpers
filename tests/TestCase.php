<?php

namespace Tests;

use Arbe\TextHelpers\Providers\TextHelperServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            TextHelperServiceProvider::class,
        ];
    }
}
