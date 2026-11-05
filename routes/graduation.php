<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\RsvpController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/rsvp');

Route::prefix('rsvp')->name('rsvp.')->group(function () {
    Route::get('/', [RsvpController::class, 'landing'])->name('landing');
    Route::get('calendar.ics', [RsvpController::class, 'calendar'])->name('calendar');
    Route::post('lookup', [RsvpController::class, 'lookup'])->middleware('throttle:rsvp-lookup')->name('lookup');
    Route::get('respond', [RsvpController::class, 'form'])->name('form');
    Route::post('respond', [RsvpController::class, 'submit'])->middleware('throttle:rsvp-submit')->name('submit');
    Route::get('confirmation', [RsvpController::class, 'confirmation'])->name('confirmation');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [Admin\AuthController::class, 'show'])->name('login')->middleware('guest');
    Route::post('login', [Admin\AuthController::class, 'login'])->middleware(['guest', 'throttle:5,1']);
    Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::resource('invitations', Admin\InvitationController::class);
        Route::patch('invitations/{invitation}/restore', [Admin\InvitationController::class, 'restore'])->name('invitations.restore');
        Route::put('invitations/{invitation}/rsvp', [Admin\InvitationController::class, 'respond'])->name('invitations.respond');

        Route::get('export', [Admin\ExportImportController::class, 'export'])->name('export');
        Route::get('import', [Admin\ExportImportController::class, 'importForm'])->name('import');
        Route::post('import', [Admin\ExportImportController::class, 'import'])->name('import.store');

        Route::get('register', [Admin\EventDayController::class, 'register'])->name('register');
        Route::get('check-in', [Admin\EventDayController::class, 'checkin'])->name('checkin');
        Route::patch('check-in/{guest}', [Admin\EventDayController::class, 'toggle'])->name('checkin.toggle');

        Route::get('qr', [Admin\QrController::class, 'show'])->name('qr');
        Route::get('qr.svg', [Admin\QrController::class, 'svg'])->name('qr.svg');

        Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
    });
});
