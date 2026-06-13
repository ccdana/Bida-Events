<?php

use App\Http\Controllers\Admin\MapsController;
use App\Http\Controllers\Admin\MediaUploadController;
use App\Http\Controllers\Admin\PreviewController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GuestController as AdminGuestController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\ExportController;
use App\Http\Controllers\Public\ContributionController;
use App\Http\Controllers\Public\InvitationController as PublicInvitationController;
use App\Http\Controllers\Public\RsvpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return $request->user()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

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
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/client/login', fn () => redirect()->route('login'))->name('client.login');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Invitación pública
Route::prefix('p')->name('invitation.')->middleware('cache.public.invitations')->group(function () {
    Route::get('/{slug}', [PublicInvitationController::class, 'show'])->name('show');
    Route::get('/{slug}/i/{token}', [PublicInvitationController::class, 'show'])->name('guest');
    Route::post('/{slug}/i/{token}/confirm', [RsvpController::class, 'confirm'])->name('rsvp');
    Route::get('/{slug}/playlist', [ContributionController::class, 'listSongs'])->name('playlist.list');
    Route::post('/{slug}/playlist', [ContributionController::class, 'storeSong'])->name('playlist');
    Route::get('/{slug}/fotomural', [ContributionController::class, 'listPhotos'])->name('fotomural.list');
    Route::post('/{slug}/fotomural', [ContributionController::class, 'storePhoto'])->name('fotomural');
    Route::post('/{slug}/polls/{pollId}/vote', [ContributionController::class, 'votePoll'])->name('poll.vote');
});

// Panel administrativo
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/invitations/create', [AdminInvitationController::class, 'create'])->name('invitations.create');
    Route::post('/invitations', [AdminInvitationController::class, 'store'])->name('invitations.store');
    Route::post('/clients', [AdminInvitationController::class, 'storeClient'])->name('clients.store');
    Route::post('/preview', [PreviewController::class, 'store'])->name('preview.store');
    Route::get('/preview/frame', [PreviewController::class, 'frame'])->name('preview.frame');
    Route::post('/media/upload', [MediaUploadController::class, 'store'])->name('media.upload');
    Route::get('/maps/search', [MapsController::class, 'search'])->name('maps.search');
    Route::post('/maps/resolve', [MapsController::class, 'resolve'])->name('maps.resolve');
    Route::get('/invitations/{invitation}/edit', [AdminInvitationController::class, 'edit'])->name('invitations.edit');
    Route::put('/invitations/{invitation}', [AdminInvitationController::class, 'update'])->name('invitations.update');
    Route::get('/invitations/{invitation}/guests', [AdminGuestController::class, 'index'])->name('guests.index');
    Route::post('/invitations/{invitation}/guests', [AdminGuestController::class, 'store'])->name('guests.store');
    Route::put('/invitations/{invitation}/guests/{guest}', [AdminGuestController::class, 'update'])->name('guests.update');
    Route::delete('/invitations/{invitation}/guests/{guest}', [AdminGuestController::class, 'destroy'])->name('guests.destroy');
});

// Portal cliente
Route::prefix('client')->name('client.')->middleware(['auth', 'client'])->group(function () {
    Route::get('/', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/invitations/{invitation}', [ClientDashboardController::class, 'show'])->name('invitation.show');
    Route::get('/invitations/{invitation}/export/excel', [ExportController::class, 'guestsExcel'])->name('export.excel');
    Route::get('/invitations/{invitation}/export/pdf', [ExportController::class, 'guestsPdf'])->name('export.pdf');
    Route::get('/invitations/{invitation}/export/invitation-pdf', [ExportController::class, 'invitationPdf'])->name('export.invitation-pdf');
});
