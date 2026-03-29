<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\auth\RegisterController;
use App\Http\Controllers\auth\ResetPasswordController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::view('/aide', 'help')->name('help');
Route::view('/mentions-legales', 'legal')->name('legal');

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [RegisterController::class, 'showregister'])->name('register.show');
    Route::post('/inscription', [RegisterController::class, 'register'])->name('register.submit');

    // CRITIQUE : doit s'appeler 'login' pour que le middleware auth fonctionne
    Route::get('/login', [LoginController::class, 'showlogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    Route::get('/reset-password', [ResetPasswordController::class, 'showresetpassword'])->name('password.forgot');
    Route::post('/reset-password', [ResetPasswordController::class, 'askResetPassword'])->name('password.ask');
    Route::post('/reset-password/confirm', [ResetPasswordController::class, 'resetPassword'])->name('password.reset');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::view('/compte-non-valide', 'auth.account_pending')->name('account.pending');
});

Route::middleware(['auth', 'validated'])->prefix('utilisateur')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/profil', [UserController::class, 'profil'])->name('user.profile');
    Route::post('/profil/password', [UserController::class, 'updatePassword'])->name('user.password.update');

    Route::post('/reservation', [ReservationController::class, 'requestReservation'])->name('user.reservation.request');
    Route::post('/reservation/{reservation}/close', [ReservationController::class, 'closeReservation'])->name('user.reservation.close');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/utilisateurs', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/utilisateurs/{user}', [AdminController::class, 'userDetail'])->name('admin.users.show');
    Route::post('/utilisateurs/{user}/validate', [AdminController::class, 'validateUser'])->name('admin.users.validate');
    Route::post('/utilisateurs/{user}/reset-password', [AdminController::class, 'resetUserPassword'])->name('admin.users.reset-password');

    Route::post('/reservation/force', [ReservationController::class, 'forceAssign'])->name('admin.reservation.force');
    Route::post('/reservation/{reservation}/close', [ReservationController::class, 'closeReservation'])->name('admin.reservation.close');

    Route::get('/places', [AdminController::class, 'places'])->name('admin.places');
    Route::post('/places/assign', [AdminController::class, 'assignPlace'])->name('admin.places.assign');
    Route::post('/places', [AdminController::class, 'storePlace'])->name('admin.places.store');
    Route::put('/places/{spot}', [AdminController::class, 'updatePlace'])->name('admin.places.update');
    Route::delete('/places/{spot}', [AdminController::class, 'deletePlace'])->name('admin.places.delete');

    Route::get('/liste-attente', [AdminController::class, 'waitingList'])->name('admin.waiting');
    Route::post('/liste-attente/{entry}/move', [AdminController::class, 'moveWaiting'])->name('admin.waiting.move');

    Route::post('/settings', [AdminController::class, 'settings'])->name('admin.settings');
});
