<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GuestController as AdminGuestController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\ExportController;
use App\Http\Controllers\Public\ContributionController;
use App\Http\Controllers\Public\InvitationController as PublicInvitationController;
use App\Http\Controllers\Public\RsvpController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/client/login', fn () => redirect()->route('login'))->name('client.login');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Invitación pública
Route::prefix('p')->name('invitation.')->group(function () {
    Route::get('/{slug}', [PublicInvitationController::class, 'show'])->name('show');
    Route::get('/{slug}/i/{token}', [PublicInvitationController::class, 'show'])->name('guest');
    Route::post('/{slug}/i/{token}/confirm', [RsvpController::class, 'confirm'])->name('rsvp');
    Route::post('/{slug}/playlist', [ContributionController::class, 'storeSong'])->name('playlist');
    Route::post('/{slug}/fotomural', [ContributionController::class, 'storePhoto'])->name('fotomural');
    Route::post('/{slug}/polls/{pollId}/vote', [ContributionController::class, 'votePoll'])->name('poll.vote');
});

// Panel administrativo
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
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
