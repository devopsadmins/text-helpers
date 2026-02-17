<?php

declare(strict_types=1);

namespace Arbe\TextHelpers\Providers;

use Illuminate\Support\ServiceProvider;
use Arbe\TextHelpers\Services\StringToolkit;

class TextHelperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StringToolkit::class, fn () => new StringToolkit());
    }
}
