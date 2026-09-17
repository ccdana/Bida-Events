<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DesignSystemController;
use App\Http\Controllers\Admin\GuestController as AdminGuestController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\MapsController;
use App\Http\Controllers\Admin\MediaUploadController;
use App\Http\Controllers\Admin\PreviewController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Client\ContributionController as ClientContributionController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\ExportController;
use App\Http\Controllers\EventLandingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Public\ContributionController;
use App\Http\Controllers\Public\InvitationController as PublicInvitationController;
use App\Http\Controllers\Public\RsvpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Sitio público: la portada y las páginas por tipo de evento recuerdan el origen de campaña (utm_*, ?ref=)
Route::middleware('lead.source')->group(function () {
    Route::get('/', HomeController::class)->name('home');

    // /invitaciones-de-boda, /invitaciones-xv-anos… (contenido en config/bida.php, clave landings)
    Route::get('/{landing}', EventLandingController::class)
        ->whereIn('landing', array_keys(config('bida.landings', [])))
        ->name('landing');
});

Route::get('/sitemap.xml', [EventLandingController::class, 'sitemap'])->name('sitemap');

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
});

// Panel administrativo
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Referencia interna de colores, tipografía y componentes
    Route::get('/sistema-visual', [DesignSystemController::class, 'index'])->name('design-system');
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
