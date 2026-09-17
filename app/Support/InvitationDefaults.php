<?php

namespace App\Support;

use App\Modules\ModuleRegistry;

class InvitationDefaults
{
    /** Códigos de módulo en el orden en que se guardan (config/modules.php). */
    public static function moduleCodes(): array
    {
        return app(ModuleRegistry::class)->codes();
    }

    /**
     * Pestaña del editor de cada módulo. Los módulos nuevos usan su propio código como pestaña;
     * aquí solo quedan los que históricamente tienen otro nombre.
     */
    public static function moduleTabMap(): array
    {
        $aliases = [
            'bienvenida' => 'hero',
            'dress_code' => 'dress',
            'cuenta_regresiva' => 'countdown',
        ];

        $map = [];

        foreach (self::moduleCodes() as $code) {
            if ($code !== 'config') {
                $map[$code] = $aliases[$code] ?? $code;
            }
        }

        return $map;
    }

    public static function moduleVisibilityDefaults(): array
    {
        return app(ModuleRegistry::class)->visibilityDefaults();
    }

    /** Forma vacía de todos los módulos para una invitación nueva. */
    public static function emptyModules(): array
    {
        return app(ModuleRegistry::class)->emptyModules();
    }

    public static function templates(): array
    {
        return InvitationTemplates::labels();
    }

    /**
     * Nombre de vista de la plantilla. Acepta los nombres antiguos con prefijo "pages." (JSON o sesión)
     * y cae en la plantilla por defecto si la vista no existe.
     */
    public static function resolveTemplate(?string $template): string
    {
        $default = InvitationTemplates::DEFAULT;
        $template = $template ?: $default;

        if (str_starts_with($template, 'pages.')) {
            $template = substr($template, strlen('pages.'));
        }

        return view()->exists($template) ? $template : $default;
    }

    public static function itineraryIcons(): array
    {
        return ['users', 'glass', 'candle', 'dance', 'dinner', 'music', 'star'];
    }
}
