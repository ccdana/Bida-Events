<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //$this->app->register(BladeServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Si la petición web incluye la palabra "ngrok", fuerza las URLs internas a HTTPS
        if (str_contains(request()->getHost(), 'ngrok-free.dev')) {
            URL::forceScheme('https');
        }

        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        $message = 'Demasiados intentos. Espera un momento e inténtalo de nuevo.';

        $tooManyAttempts = fn (Request $request, array $headers) => $request->expectsJson()
            ? response()->json(['success' => false, 'message' => $message], 429, $headers)
            : back()->withErrors(['throttle' => $message]);

        // Endpoints públicos: límite por IP y por invitación (límites en config/optimizations.php)
        foreach (['rsvp', 'songs', 'photos', 'votes'] as $key) {
            RateLimiter::for("invitation-{$key}", fn (Request $request) => Limit::perMinute((int) config("optimizations.rate_limits.{$key}"))
                ->by($request->ip().'|'.$request->route('slug'))
                ->response($tooManyAttempts));
        }

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute((int) config('optimizations.rate_limits.login'))
            ->by(Str::lower((string) $request->input('email')).'|'.$request->ip())
            ->response(fn () => back()->withErrors(['email' => $message])->onlyInput('email')));
    }
}
