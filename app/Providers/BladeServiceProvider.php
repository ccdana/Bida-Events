<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /**
         * @blade
         * Directiva de Blade para cachear fragmentos de vista
         * Uso: @cache('key', 60) content @endcache
         */
        Blade::directive('cache', function ($expression) {
            return "<?php \$__env->startComponent(\App\View\Components\CacheComponent::class, ['key' => {$expression}]); ?>";
        });

        Blade::componentNamespace('App\\View\\Components', 'ui');

        /**
         * Macro para detectar si es invitación post-evento
         */
        Blade::if('postEvent', function ($invitation) {
            return $invitation?->is_post_event ?? false;
        });

        /**
         * Macro para detectar si es invitación activa
         */
        Blade::if('activeInvitation', function ($invitation) {
            return $invitation?->status === 'active' && $invitation?->expires_at?->isFuture();
        });
    }
}
