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
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\ContributionController as ClientContributionController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\DoorAccessController;
use App\Http\Controllers\Client\ExportController;
use App\Http\Controllers\Client\GuestController as ClientGuestController;
use App\Http\Controllers\Client\InvitationController as ClientInvitationController;
use App\Http\Controllers\Client\ResellerClientController;
use App\Http\Controllers\EventLandingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Public\ContributionController;
use App\Http\Controllers\Public\DoorController;
use App\Http\Controllers\Public\InvitationController as PublicInvitationController;
use App\Http\Controllers\Public\RsvpController;
use App\Http\Controllers\PublicPagesController;
use App\Http\Controllers\SeoController;
use App\Models\Invitation;
use App\Support\LegalPages;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// Sitio público: la portada y las páginas por tipo de evento recuerdan el origen de campaña (utm_*, ?ref=)
Route::middleware('lead.source')->group(function () {
    Route::get('/', HomeController::class)->name('home');

    // «Hazlo tú»: planes mensuales para armar invitaciones propias (revendedores con suscripción)
    Route::get('/hazlo-tu', [PublicPagesController::class, 'diy'])->name('diy');

    // Guía pública sobre invitaciones digitales (contenido pensado para buscadores y respuestas de IA)
    Route::get('/guia-invitaciones-digitales', [PublicPagesController::class, 'guide'])->name('guide');
    Route::permanentRedirect('/para-profesionales', '/hazlo-tu');

    // Privacidad, cookies y términos (App\Support\LegalPages)
    Route::get('/legal/{page}', [PublicPagesController::class, 'legal'])
        ->whereIn('page', LegalPages::PAGES)
        ->name('legal');

    // /invitaciones-de-boda, /invitaciones-xv-anos… (contenido en config/bida.php, clave landings)
    Route::get('/{landing}', EventLandingController::class)
        ->whereIn('landing', array_keys(config('bida.landings', [])))
        ->name('landing');
});

// Para buscadores y motores de respuesta: se arman con la configuración de hoy (SeoController). Sin
// sesión ni cookies: los pide un rastreador y así cualquier caché intermedia puede guardarlos.
// robots.txt es estático (public/robots.txt)
Route::withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, ValidateCsrfToken::class])->group(function () {
    Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
    Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');
});

// Control de entrada el día del evento (teléfono del personal de la puerta). Fuera de /p para que
// ninguna caché de invitaciones guarde una respuesta que depende del teléfono que la pide
Route::middleware('throttle:door')->name('door.')->group(function () {
    Route::get('/puerta/{doorToken}', [DoorController::class, 'open'])->name('open');
    Route::post('/puerta/{doorToken}/codigo', [DoorController::class, 'lookup'])->name('lookup');
    Route::get('/entrada/{slug}/{token}', [DoorController::class, 'entry'])->name('entry');
    Route::post('/entrada/{slug}/{token}', [DoorController::class, 'checkIn'])->name('check-in');
    Route::post('/entrada/{slug}/{token}/deshacer', [DoorController::class, 'undo'])->name('undo');
});

// Invitaciones de muestra de la home: se pueden recorrer y probar, pero nada se guarda
Route::get('/muestra/{slug}', [PublicInvitationController::class, 'demo'])->name('invitation.demo');

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    if (! $user) {
        return redirect()->route('login');
    }

    return $user->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('client.dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
    Route::get('/client/login', fn () => redirect()->route('login'))->name('client.login');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Invitación pública
Route::prefix('p')->name('invitation.')->middleware('cache.public.invitations')->group(function () {
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

    // Revendedores: alta y pagos de su suscripción (el cobro es manual, no hay pasarela)
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

    // Revendedores: arman sus propias invitaciones con el mismo editor que el administrador.
    // «reseller» deja pasar solo a revendedores; la policy exige además la suscripción al día.
    Route::middleware('reseller')->group(function () {
        // Su cuenta: cambia la contraseña que le dio el administrador por una propia
        Route::get('/cuenta', [AccountController::class, 'edit'])->name('account');
        Route::put('/cuenta/contrasena', [AccountController::class, 'updatePassword'])->middleware('throttle:6,1')->name('account.password');

        Route::get('/invitaciones/nueva', [ClientInvitationController::class, 'create'])->can('create', Invitation::class)->name('invitations.create');
        Route::post('/invitaciones', [ClientInvitationController::class, 'store'])->can('create', Invitation::class)->name('invitations.store');
        Route::get('/invitations/{invitation}/editar', [ClientInvitationController::class, 'edit'])->can('update', 'invitation')->name('invitations.edit');
        Route::put('/invitations/{invitation}', [ClientInvitationController::class, 'update'])->can('update', 'invitation')->name('invitations.update');

        // El acceso del cliente de cada evento: uno por evento, se elimina si se creó mal
        Route::post('/invitations/{invitation}/cliente', [ResellerClientController::class, 'store'])->can('update', 'invitation')->name('invitations.client.store');
        // O el evento es del propio revendedor: él mismo es el cliente y no gasta un acceso
        Route::post('/invitations/{invitation}/cliente/yo', [ResellerClientController::class, 'assignSelf'])->can('update', 'invitation')->name('invitations.client.self');
        Route::delete('/invitations/{invitation}/cliente', [ResellerClientController::class, 'destroy'])->can('update', 'invitation')->name('invitations.client.destroy');

        // Herramientas del editor: los mismos controladores que usa el administrador, con límite por minuto
        Route::prefix('editor')->name('editor.')->middleware('can:create,'.Invitation::class)->group(function () {
            Route::post('/preview', [PreviewController::class, 'store'])->middleware('throttle:reseller-editor')->name('preview.store');
            Route::get('/preview/frame', [PreviewController::class, 'frame'])->middleware('throttle:reseller-editor')->name('preview.frame');
            Route::post('/media/upload', [MediaUploadController::class, 'store'])->middleware('throttle:reseller-uploads')->name('media.upload');
            Route::get('/maps/search', [MapsController::class, 'search'])->middleware('throttle:reseller-editor')->name('maps.search');
            Route::post('/maps/resolve', [MapsController::class, 'resolve'])->middleware('throttle:reseller-editor')->name('maps.resolve');
        });
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
