<?php

use App\Http\Middleware\CachePublicInvitations;
use App\Http\Middleware\CaptureLeadSource;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsClient;
use App\Http\Middleware\LogSlowRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Los proxies de confianza se declaran en AppServiceProvider, donde ya está cargada la configuración
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'client' => EnsureUserIsClient::class,
            'cache.public.invitations' => CachePublicInvitations::class,
            'lead.source' => CaptureLeadSource::class,
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
            LogSlowRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
