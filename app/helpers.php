<?php

use App\Support\Localization;

if (! function_exists('lroute')) {
    /**
     * URL de una ruta del sitio en el idioma de la página (o en el que se pida): route() de siempre
     * llevaría al español. Los parámetros de página (landing, page) van con su clave en español.
     */
    function lroute(string $name, mixed $parameters = [], bool $absolute = true, ?string $locale = null): string
    {
        return Localization::route($name, $parameters, $absolute, $locale);
    }
}
