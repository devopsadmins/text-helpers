<?php

declare(strict_types=1);

use Arbe\TextHelpers\Services\StringToolkit;

if (! function_exists('text')) {
    /**
     * Helper Proxy para o StringToolkit resolvido via Container.
     */
    function text(): StringToolkit
    {
        return app(StringToolkit::class);
    }
}
