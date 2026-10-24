<?php

use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GuestController as AdminGuestController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\MapsController;
use App\Http\Controllers\Admin\MediaUploadController;
use App\Http\Controllers\Admin\PreviewController;
use App\Http\Controllers\Admin\ResellerController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\ContributionController as ClientContributionController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\DiyInvitationController;
use App\Http\Controllers\Client\DoorAccessController;
use App\Http\Controllers\Client\ExportController;
use App\Http\Controllers\Client\GuestController as ClientGuestController;
use App\Http\Controllers\Client\InvitationController as ClientInvitationController;
use App\Http\Controllers\Client\ResellerClientController;
use App\Http\Controllers\Client\SubscriptionController;
use App\Http\Controllers\EventLandingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Public\ContributionController;
use App\Http\Controllers\Public\DoorController;
use App\Http\Controllers\Public\InvitationController as PublicInvitationController;
use App\Http\Controllers\Public\RsvpController;
use App\Http\Controllers\PublicPagesController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\Webhooks\PaymentWebhookController;
use App\Http\Middleware\SetLocale;
use App\Models\Invitation;
use App\Support\Localization;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/*
| Sitio público e ingreso, una vez por idioma (config/localization.php): el español en la raíz con las
| rutas de siempre (home, pricing…) y el inglés bajo /en con el prefijo «en.» en su nombre (en.home,
| en.pricing…). Las vistas llegan a la versión del idioma de la página con lroute(). Las invitaciones
| (/p/…), los paneles y los avisos de pago no se repiten: no dependen de la dirección.
*/
$siteRoutes = function (string $locale): void {
    $path = fn (string $key): string => Localization::path($key, $locale);

    // La portada y las páginas por tipo de evento recuerdan el origen de campaña (utm_*, ?ref=)
    Route::middleware('lead.source')->group(function () use ($path, $locale) {
        Route::get('/', HomeController::class)->name('home');

        // «Únete»: planes mensuales para armar invitaciones propias (revendedores con suscripción)
        Route::get('/'.$path('unete'), [PublicPagesController::class, 'diy'])->name('diy');

        // Precios: Hazlo tú (gratis o pago único), Planes, lado a lado (App\Support\PricingPage)
        Route::get('/'.$path('precios'), [PublicPagesController::class, 'pricing'])->name('pricing');

        // Guía pública sobre invitaciones digitales (contenido pensado para buscadores y respuestas de IA)
        Route::get('/'.$path('guia-invitaciones-digitales'), [PublicPagesController::class, 'guide'])->name('guide');

        // Privacidad, cookies y términos (App\Support\LegalPages)
        Route::get('/legal/{page}', [PublicPagesController::class, 'legal'])
            ->whereIn('page', Localization::legalSlugs($locale))
            ->name('legal');

        // /invitaciones-de-boda, /en/wedding-invitations… (contenido en config/bida.php, clave landings)
        Route::get('/{landing}', EventLandingController::class)
            ->whereIn('landing', Localization::landingSlugs($locale))
            ->name('landing');
    });

    // Resumen para motores de respuesta (sin sesión ni cookies, como el sitemap)
    Route::withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, ValidateCsrfToken::class])
        ->get('/llms.txt', [SeoController::class, 'llms'])->name('llms');

    Route::middleware('guest')->group(function () use ($path) {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login')->name('login.attempt');

        // Cuenta nueva desde el sitio (se apaga con AUTH_REGISTRATION=false)
        Route::get('/'.$path('registro'), [RegisterController::class, 'create'])->name('register');
        Route::post('/'.$path('registro'), [RegisterController::class, 'store'])->middleware('throttle:register')->name('register.store');

        // «¿Olvidaste tu contraseña?»: enlace por correo para crear una nueva
        Route::get('/'.$path('contrasena/olvide'), [ForgotPasswordController::class, 'create'])->name('password.request');
        Route::post('/'.$path('contrasena/olvide'), [ForgotPasswordController::class, 'store'])->middleware('throttle:password-email')->name('password.email');

        // Entrar con Google (solo si hay credenciales en .env). El regreso va fuera: es una sola
        // dirección, la que se registra en Google Cloud
        Route::get('/auth/google', [GoogleController::class, 'redirect'])->middleware('throttle:google')->name('auth.google');
    });

    // La contraseña nueva desde el enlace del correo. Fuera de «guest»: también lo abre quien lo pidió
    // desde «Mi cuenta» con la sesión iniciada (al guardar, se cierran todas sus sesiones)
    Route::get('/'.$path('contrasena/nueva').'/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/'.$path('contrasena/nueva'), [ResetPasswordController::class, 'store'])->middleware('throttle:6,1')->name('password.store');
};

foreach (Localization::locales() as $locale) {
    Route::prefix(Localization::prefix($locale))
        ->name(Localization::namePrefix($locale))
        ->middleware(SetLocale::class.':'.$locale)
        ->group(fn () => $siteRoutes($locale));
}

// Las direcciones anteriores de «Únete» siguen funcionando (enlaces compartidos, buscadores)
Route::permanentRedirect('/para-profesionales', '/unete');
Route::permanentRedirect('/hazlo-tu', '/unete');

// Para buscadores y motores de respuesta: se arman con la configuración de hoy (SeoController). Sin
// sesión ni cookies: los pide un rastreador y así cualquier caché intermedia puede guardarlos.
// robots.txt es estático (public/robots.txt)
Route::withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, ValidateCsrfToken::class])->group(function () {
    Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

    // Avisos de las pasarelas de pago (los manda su servidor, no un navegador: sin sesión ni CSRF).
    // Los valida cada pasarela: por su firma (Lemon Squeezy, POST) o consultándole el pago (Libélula, GET)
    Route::match(['get', 'post'], '/webhooks/{provider}', PaymentWebhookController::class)
        ->where('provider', '[a-z0-9]+')
        ->middleware('throttle:payment-webhooks')
        ->name('webhooks.payments');
});

// Control de entrada el día del evento (teléfono del personal de la puerta). Fuera de /p para que
// ninguna caché de invitaciones guarde una respuesta que depende del teléfono que la pide
Route::middleware(['invitation.locale', 'throttle:door'])->name('door.')->group(function () {
    Route::get('/puerta/{doorToken}', [DoorController::class, 'open'])->name('open');
    Route::post('/puerta/{doorToken}/codigo', [DoorController::class, 'lookup'])->name('lookup');
    Route::get('/entrada/{slug}/{token}', [DoorController::class, 'entry'])->name('entry');
    Route::post('/entrada/{slug}/{token}', [DoorController::class, 'checkIn'])->name('check-in');
    Route::post('/entrada/{slug}/{token}/deshacer', [DoorController::class, 'undo'])->name('undo');
});

// Invitaciones de muestra de la home: se pueden recorrer y probar, pero nada se guarda
Route::get('/muestra/{slug}', [PublicInvitationController::class, 'demo'])->middleware('invitation.locale')->name('invitation.demo');

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    if (! $user) {
        return redirect()->route('login');
    }

    return $user->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('client.dashboard');
})->middleware('auth')->name('dashboard');

// Regreso de Google: una sola dirección para todos los idiomas (el idioma viaja en la sesión)
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])
    ->middleware(['guest', 'throttle:google'])
    ->name('auth.google.callback');

// Quien entra por «/client/login» (enlaces de antes) llega al ingreso de siempre
Route::get('/client/login', fn () => redirect()->route('login'))->middleware('guest')->name('client.login');

// Confirmar el correo de una cuenta abierta desde el sitio
Route::middleware('auth')->group(function () {
    Route::get('/correo/confirmar', [VerifyEmailController::class, 'notice'])->name('verification.notice');
    Route::get('/correo/confirmar/{id}/{hash}', [VerifyEmailController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/correo/confirmar', [VerifyEmailController::class, 'send'])->middleware('throttle:verification-email')->name('verification.send');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Invitación pública
Route::prefix('p')->name('invitation.')->middleware(['invitation.locale', 'cache.public.invitations'])->group(function () {
    Route::get('/{slug}', [PublicInvitationController::class, 'show'])->name('show');
    Route::get('/{slug}/i/{token}', [PublicInvitationController::class, 'show'])->name('guest');
    Route::post('/{slug}/i/{token}/confirm', [RsvpController::class, 'confirm'])->middleware('throttle:invitation-rsvp')->name('rsvp');
    Route::get('/{slug}/playlist', [ContributionController::class, 'listSongs'])->name('playlist.list');
    Route::post('/{slug}/playlist', [ContributionController::class, 'storeSong'])->middleware('throttle:invitation-songs')->name('playlist');
    Route::get('/{slug}/fotomural', [ContributionController::class, 'listPhotos'])->name('fotomural.list');
    Route::post('/{slug}/fotomural', [ContributionController::class, 'storePhoto'])->middleware('throttle:invitation-photos')->name('fotomural');
    Route::post('/{slug}/polls/{pollId}/vote', [ContributionController::class, 'votePoll'])->middleware('throttle:invitation-votes')->name('poll.vote');
    // Respuesta del destinatario de una tarjeta estacional
    Route::post('/{slug}/respuesta', [ContributionController::class, 'storeReply'])->middleware('throttle:invitation-replies')->name('reply');
});

// Panel administrativo
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    // Las invitaciones de muestra del sitio, aparte de las de clientes
    Route::get('/muestras', [AdminDashboardController::class, 'showcase'])->name('showcase');

    // Precios, promociones y plantillas de temporada, sin tocar el código
    Route::get('/ajustes', [SettingsController::class, 'edit'])->name('settings');
    Route::put('/ajustes', [SettingsController::class, 'update'])->name('settings.update');

    // Clientes: los del equipo y los que crea cada revendedor
    Route::get('/clientes', [AdminClientController::class, 'index'])->name('clients.index');

    // Revendedores: alta y pagos de su suscripción registrados a mano (los de tarjeta o QR llegan solos)
    Route::get('/revendedores', [ResellerController::class, 'index'])->name('resellers.index');
    Route::post('/revendedores', [ResellerController::class, 'store'])->name('resellers.store');
    Route::post('/revendedores/{reseller}/pagos', [ResellerController::class, 'registerPayment'])->name('resellers.payments.store');
    Route::delete('/revendedores/{reseller}/pagos/{payment}', [ResellerController::class, 'destroyPayment'])->name('resellers.payments.destroy');

    Route::get('/invitations/create', [AdminInvitationController::class, 'create'])->name('invitations.create');
    Route::post('/invitations', [AdminInvitationController::class, 'store'])->name('invitations.store');
    Route::post('/clients', [AdminInvitationController::class, 'storeClient'])->name('clients.store');
    Route::post('/clients/{client}/password', [AdminInvitationController::class, 'regenerateClientPassword'])->name('clients.password');
    Route::post('/preview', [PreviewController::class, 'store'])->name('preview.store');
    Route::get('/preview/frame', [PreviewController::class, 'frame'])->name('preview.frame');
    Route::post('/preview/render', [PreviewController::class, 'render'])->name('preview.render');
    Route::post('/media/upload', [MediaUploadController::class, 'store'])->name('media.upload');
    Route::get('/maps/search', [MapsController::class, 'search'])->name('maps.search');
    Route::post('/maps/resolve', [MapsController::class, 'resolve'])->name('maps.resolve');
    Route::get('/invitations/{invitation}/edit', [AdminInvitationController::class, 'edit'])->can('update', 'invitation')->name('invitations.edit');
    Route::put('/invitations/{invitation}', [AdminInvitationController::class, 'update'])->can('update', 'invitation')->name('invitations.update');
    Route::delete('/invitations/{invitation}', [AdminInvitationController::class, 'destroy'])->can('delete', 'invitation')->name('invitations.destroy');

    // scopeBindings: el invitado debe pertenecer a la invitación de la URL (404 si no)
    Route::scopeBindings()->middleware('can:manageGuests,invitation')->group(function () {
        Route::get('/invitations/{invitation}/guests', [AdminGuestController::class, 'index'])->name('guests.index');
        Route::post('/invitations/{invitation}/guests', [AdminGuestController::class, 'store'])->name('guests.store');
        Route::put('/invitations/{invitation}/guests/{guest}', [AdminGuestController::class, 'update'])->name('guests.update');
        Route::delete('/invitations/{invitation}/guests/{guest}', [AdminGuestController::class, 'destroy'])->name('guests.destroy');
        Route::post('/invitations/{invitation}/guests/{guest}/token', [AdminGuestController::class, 'regenerateToken'])->name('guests.token');
    });
});

// Portal cliente
Route::prefix('client')->name('client.')->middleware(['auth', 'client'])->group(function () {
    Route::get('/', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/invitations/{invitation}', [ClientDashboardController::class, 'show'])->can('view', 'invitation')->name('invitation.show');

    // Su cuenta: el correo (para recuperar la contraseña y recibir avisos) y una contraseña propia
    Route::get('/cuenta', [AccountController::class, 'edit'])->name('account');
    Route::put('/cuenta/usuario', [AccountController::class, 'updateUsername'])->middleware('throttle:6,1')->name('account.username');
    Route::put('/cuenta/correo', [AccountController::class, 'updateEmail'])->middleware('throttle:6,1')->name('account.email');
    Route::put('/cuenta/contrasena', [AccountController::class, 'updatePassword'])->middleware('throttle:6,1')->name('account.password');
    Route::post('/cuenta/contrasena/enlace', [AccountController::class, 'sendPasswordLink'])->middleware('throttle:3,1')->name('account.password-link');

    // Pagar un plan de «Únete» en línea (PAYMENTS_ENABLED): elegir, ir a la pasarela y esperar su aviso
    Route::get('/suscripcion', [SubscriptionController::class, 'show'])->name('subscription');
    Route::post('/suscripcion', [SubscriptionController::class, 'store'])->middleware('throttle:6,1')->name('subscription.store');
    Route::get('/suscripcion/orden/{order:public_id}', [SubscriptionController::class, 'order'])->whereUuid('order')->name('subscription.order');
    Route::get('/suscripcion/portal', [SubscriptionController::class, 'portal'])->middleware('throttle:10,1')->name('subscription.portal');

    // Revendedores: arman sus propias invitaciones con el mismo editor que el administrador.
    // «reseller» deja pasar solo a revendedores; la policy exige además la suscripción al día.
    Route::middleware('reseller')->group(function () {
        Route::get('/invitaciones/nueva', [ClientInvitationController::class, 'create'])->can('create', Invitation::class)->name('invitations.create');
        Route::post('/invitaciones', [ClientInvitationController::class, 'store'])->can('create', Invitation::class)->name('invitations.store');
        Route::get('/invitations/{invitation}/editar', [ClientInvitationController::class, 'edit'])->can('update', 'invitation')->name('invitations.edit');
        Route::put('/invitations/{invitation}', [ClientInvitationController::class, 'update'])->can('update', 'invitation')->name('invitations.update');

        // El acceso del cliente de cada evento: uno por evento, se elimina si se creó mal
        Route::post('/invitations/{invitation}/cliente', [ResellerClientController::class, 'store'])->can('update', 'invitation')->name('invitations.client.store');
        // O el evento es del propio revendedor: él mismo es el cliente y no gasta un acceso
        Route::post('/invitations/{invitation}/cliente/yo', [ResellerClientController::class, 'assignSelf'])->can('update', 'invitation')->name('invitations.client.self');
        Route::delete('/invitations/{invitation}/cliente', [ResellerClientController::class, 'destroy'])->can('update', 'invitation')->name('invitations.client.destroy');
    });

    // «Hazlo tú»: cualquier cuenta arma su propia invitación sin pagar antes; al publicar elige
    // gratis (con lo básico) o el nivel que piden sus partes (App\Support\DiyInvitations)
    Route::prefix('mi-invitacion')->name('diy.')->group(function () {
        Route::get('/nueva', [DiyInvitationController::class, 'create'])->can('createOwn', Invitation::class)->name('create');
        Route::post('/', [DiyInvitationController::class, 'store'])->can('createOwn', Invitation::class)->name('store');
        Route::get('/{invitation}/editar', [DiyInvitationController::class, 'edit'])->can('editOwn', 'invitation')->name('edit');
        Route::put('/{invitation}', [DiyInvitationController::class, 'update'])->can('editOwn', 'invitation')->name('update');
        Route::delete('/{invitation}', [DiyInvitationController::class, 'destroy'])->can('deleteOwn', 'invitation')->name('destroy');
        Route::get('/{invitation}/publicar', [DiyInvitationController::class, 'publish'])->can('editOwn', 'invitation')->name('publish');
        Route::post('/{invitation}/publicar', [DiyInvitationController::class, 'storePublish'])->middleware('throttle:10,1')->can('editOwn', 'invitation')->name('publish.store');
        // Pagar el nivel (o la diferencia al subir de nivel) en línea y esperar el aviso del cobro
        Route::post('/{invitation}/pagar', [DiyInvitationController::class, 'checkout'])->middleware('throttle:6,1')->can('editOwn', 'invitation')->name('checkout');
        Route::get('/orden/{order:public_id}', [DiyInvitationController::class, 'order'])->whereUuid('order')->name('order');
    });

    // Herramientas del editor (revendedor al día o «Hazlo tú»): los mismos controladores que usa el
    // administrador, con límite por minuto
    Route::prefix('editor')->name('editor.')->middleware('can:useEditor,'.Invitation::class)->group(function () {
        Route::post('/preview', [PreviewController::class, 'store'])->middleware('throttle:reseller-editor')->name('preview.store');
        Route::get('/preview/frame', [PreviewController::class, 'frame'])->middleware('throttle:reseller-editor')->name('preview.frame');
        Route::post('/preview/render', [PreviewController::class, 'render'])->middleware('throttle:reseller-editor')->name('preview.render');
        Route::post('/media/upload', [MediaUploadController::class, 'store'])->middleware('throttle:reseller-uploads')->name('media.upload');
        Route::get('/maps/search', [MapsController::class, 'search'])->middleware('throttle:reseller-editor')->name('maps.search');
        Route::post('/maps/resolve', [MapsController::class, 'resolve'])->middleware('throttle:reseller-editor')->name('maps.resolve');
    });

    // El cliente arma su lista de invitados; el invitado debe pertenecer a su invitación (scopeBindings)
    Route::scopeBindings()->middleware('can:manageOwnGuests,invitation')->group(function () {
        Route::post('/invitations/{invitation}/guests', [ClientGuestController::class, 'store'])->name('guests.store');
        Route::delete('/invitations/{invitation}/guests/{guest}', [ClientGuestController::class, 'destroy'])->name('guests.destroy');
    });

    // Control de entrada: el enlace de puerta que se comparte con quien recibe a los invitados
    Route::post('/invitations/{invitation}/puerta', [DoorAccessController::class, 'store'])->can('manageOwnGuests', 'invitation')->name('door.store');
    Route::delete('/invitations/{invitation}/puerta', [DoorAccessController::class, 'destroy'])->can('manageOwnGuests', 'invitation')->name('door.destroy');

    // Ocultar o volver a mostrar una foto o una canción de invitados (no se borra nada)
    Route::patch('/invitations/{invitation}/contributions/{contribution}', [ClientContributionController::class, 'update'])
        ->scopeBindings()
        ->can('moderateContributions', 'invitation')
        ->name('contributions.update');

    // Los archivos se arman en segundo plano: se piden, se consulta el estado y se descargan
    Route::post('/invitations/{invitation}/export/{type}', [ExportController::class, 'store'])
        ->middleware('can:export,invitation')
        ->name('export.store');

    Route::get('/exports/{export}/estado', [ExportController::class, 'status'])->name('export.status');
    Route::get('/exports/{export}/descargar', [ExportController::class, 'download'])->name('export.download');
});
