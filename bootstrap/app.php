<?php

use App\Http\Middleware\CachePublicInvitations;
use App\Http\Middleware\CaptureLeadSource;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsClient;
use App\Http\Middleware\EnsureUserIsReseller;
use App\Http\Middleware\LogSlowRequests;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetInvitationLocale;
use App\Support\AccessDeniedRedirect;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
            'reseller' => EnsureUserIsReseller::class,
            'cache.public.invitations' => CachePublicInvitations::class,
            'lead.source' => CaptureLeadSource::class,
            'invitation.locale' => SetInvitationLocale::class,
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
            LogSlowRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // En los paneles, un 403 no muestra una página de error: vuelve con un aviso
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 403 && AccessDeniedRedirect::handles($request)) {
                return AccessDeniedRedirect::respond($request, $e->getMessage());
            }

            return null;
        });
    })->create();
