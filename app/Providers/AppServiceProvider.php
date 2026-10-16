<?php

namespace App\Providers;

use App\EventProfiles\EventProfiles;
use App\Modules\ModuleRegistry;
use App\Support\SiteSettings;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Una sola lista de módulos por proceso (config/modules.php)
        $this->app->singleton(ModuleRegistry::class);
        $this->app->singleton(EventProfiles::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // En producción y en túneles de ngrok las URLs internas van siempre por HTTPS
        if ($this->app->isProduction() || str_contains(request()->getHost(), 'ngrok-free.dev')) {
            URL::forceScheme('https');
        }

        // Precios, promociones y plantillas de temporada que el administrador maneja desde el panel
        SiteSettings::apply();

        $this->configureTrustedProxies();
        $this->configureRateLimiting();
        $this->configureAccountEmails();
        $this->reportFailedJobs();
    }

    /**
     * Los correos de la cuenta (confirmar el correo y crear una contraseña nueva) con la firma de la
     * marca, en el idioma en que se abrió la cuenta (User::preferredLocale). El resto del diseño es el
     * de las notificaciones de Laravel (lang/es.json en español; el inglés ya viene en el framework).
     * Los textos se arman al enviar, no al arrancar: el idioma todavía no está decidido.
     */
    protected function configureAccountEmails(): void
    {
        $signature = fn (): string => __('El equipo de :brand', ['brand' => config('bida.brand')]);
        $greeting = fn (object $notifiable): string => __('Hola, :name:', ['name' => Str::before((string) $notifiable->name, ' ')]);

        VerifyEmail::toMailUsing(fn (object $notifiable, string $url) => (new MailMessage)
            ->subject(__('Confirma tu correo'))
            ->greeting($greeting($notifiable))
            ->line(__('Toca el botón para confirmar que este correo es tuyo. Con eso queda lista tu cuenta.'))
            ->action(__('Confirmar mi correo'), $url)
            ->line(__('El enlace vale 60 minutos. Si no abriste una cuenta en :brand, ignora este mensaje.', ['brand' => config('bida.brand')]))
            ->salutation($signature()));

        ResetPassword::toMailUsing(fn (object $notifiable, string $token) => (new MailMessage)
            ->subject(__('Crea una contraseña nueva'))
            ->greeting($greeting($notifiable))
            ->line(__('Recibimos un pedido para cambiar la contraseña de tu cuenta.'))
            ->action(__('Crear una contraseña nueva'), lroute('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]))
            ->line(__('El enlace vale :minutes minutos y sirve una sola vez. Si no lo pediste, ignora este mensaje: tu contraseña sigue igual.', ['minutes' => config('auth.passwords.users.expire')]))
            ->salutation($signature()));
    }

    /**
     * Un trabajo que agota sus reintentos (por ejemplo un Excel que no se pudo generar) queda
     * en el log de operación en el momento; bida:salud además avisa por correo si se acumulan.
     */
    protected function reportFailedJobs(): void
    {
        Queue::failing(function (JobFailed $event) {
            Log::channel('operations')->error('Trabajo fallido', [
                'job' => $event->job->resolveName(),
                'queue' => $event->job->getQueue(),
                'error' => $event->exception->getMessage(),
            ]);
        });
    }

    /**
     * Solo se confía en los proxies declarados (TRUSTED_PROXIES). De eso depende la IP real del
     * visitante, que es la que usan los límites por minuto de login, RSVP, votos y fotos: confiar
     * en cualquiera permitiría falsear X-Forwarded-For y saltarse esos límites.
     */
    protected function configureTrustedProxies(): void
    {
        $proxies = config('security.trusted_proxies', []);

        if ($proxies !== []) {
            TrustProxies::at($proxies === ['*'] ? '*' : $proxies);
        }
    }

    protected function configureRateLimiting(): void
    {
        $message = __('Demasiados intentos. Espera un momento e inténtalo de nuevo.');

        $tooManyAttempts = fn (Request $request, array $headers) => $request->expectsJson()
            ? response()->json(['success' => false, 'message' => $message], 429, $headers)
            : back()->withErrors(['throttle' => $message]);

        // Endpoints públicos: límite por IP y por invitación (límites en config/optimizations.php)
        foreach (['rsvp', 'songs', 'photos', 'votes', 'replies'] as $key) {
            RateLimiter::for("invitation-{$key}", fn (Request $request) => Limit::perMinute((int) config("optimizations.rate_limits.{$key}"))
                ->by($request->ip().'|'.$request->route('slug'))
                ->response($tooManyAttempts));
        }

        // Editor de los revendedores: usuarios externos, así que la vista previa, los mapas y sobre todo
        // la subida de archivos tienen un tope por cuenta (el editor del administrador no lo necesita)
        RateLimiter::for('reseller-editor', fn (Request $request) => Limit::perMinute(240)
            ->by('reseller-editor|'.$request->user()?->id)
            ->response($tooManyAttempts));

        RateLimiter::for('reseller-uploads', fn (Request $request) => Limit::perMinute(30)
            ->by('reseller-uploads|'.$request->user()?->id)
            ->response($tooManyAttempts));

        // Control de entrada: por teléfono (IP) y por invitación
        RateLimiter::for('door', fn (Request $request) => Limit::perMinute((int) config('optimizations.rate_limits.door', 90))
            ->by('door|'.$request->ip().'|'.($request->route('slug') ?? $request->route('doorToken')))
            ->response($tooManyAttempts));

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute((int) config('optimizations.rate_limits.login'))
            ->by(Str::lower((string) $request->input('username')).'|'.$request->ip())
            ->response(fn () => back()->withErrors(['username' => $message])->onlyInput('username')));

        // Cuentas nuevas: por IP, por minuto y por día (frena a quien las crea en serie)
        $emailThrottled = fn () => back()->withErrors(['email' => $message])->onlyInput('name', 'email');

        RateLimiter::for('register', fn (Request $request) => [
            Limit::perMinute((int) config('optimizations.rate_limits.register'))->by('register|'.$request->ip())->response($emailThrottled),
            Limit::perDay(30)->by('register-day|'.$request->ip())->response($emailThrottled),
        ]);

        // Enlaces para crear una contraseña nueva: por IP y por correo (cada uno manda un correo)
        RateLimiter::for('password-email', fn (Request $request) => [
            Limit::perMinute((int) config('optimizations.rate_limits.password_email'))->by('password-email|'.$request->ip())->response($emailThrottled),
            Limit::perHour(6)->by('password-email|'.Str::lower(trim((string) $request->input('email'))))->response($emailThrottled),
        ]);

        RateLimiter::for('verification-email', fn (Request $request) => Limit::perMinute(2)
            ->by('verification-email|'.$request->user()?->id)
            ->response(fn () => back()->with('status', __('Ya te enviamos el enlace. Espera un minuto antes de pedir otro.'))));

        // Avisos de las pasarelas: generoso (reintentan en ráfagas), pero con techo por si alguien inunda la ruta
        RateLimiter::for('payment-webhooks', fn (Request $request) => Limit::perMinute(300)
            ->by('payment-webhooks|'.$request->route('provider').'|'.$request->ip()));

        // Ida y vuelta a Google: un ingreso son dos peticiones
        RateLimiter::for('google', fn (Request $request) => Limit::perMinute(20)
            ->by('google|'.$request->ip())
            ->response(fn () => redirect()->to(lroute('login'))->withErrors(['username' => $message])));
    }
}
