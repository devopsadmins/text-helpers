<?php

declare(strict_types=1);

use Arbe\TextHelpers\Services\StringToolkit;
use Arbe\TextHelpers\Services\FluentString;

if (! function_exists('text')) {
    /**
     * Helper Proxy para o StringToolkit ou FluentString.
     * 
     * Uso direto (acesso a métodos):
     *   text()->formatName("VINICIUS DE SOUZA") // "Vinícius de Souza"
     * 
     * Uso fluido (method chaining):
     *   text("  VINICIUS DE SOUZA  ")->clean()->formatName()->abbreviate(10)
     * 
     * @param string|null $value Se fornecido, retorna FluentString para chaining
     * @return StringToolkit|FluentString
     */
    function text(?string $value = null): StringToolkit|FluentString
    {
        $toolkit = app(StringToolkit::class);
        
        if ($value !== null) {
            return new FluentString($value, $toolkit);
        }
        
        return $toolkit;
    }
}
