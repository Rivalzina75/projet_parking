<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\auth\RegisterController;
use App\Http\Controllers\auth\ResetPasswordController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;

// Page d'accueil publique.
Route::get('/', function () {
    return view('home');
})->name('home');

// Pages d'information publiques.
Route::view('/aide', 'help')->name('help');
Route::view('/mentions-legales', 'legal')->name('legal');

// Routes accessibles uniquement aux visiteurs non connectés.
Route::middleware('guest')->group(function () {
    // Formulaire et soumission d'inscription.
    Route::get('/inscription', [RegisterController::class, 'showregister'])->name('register.show');
    Route::post('/inscription', [RegisterController::class, 'register'])->name('register.submit');

    // Formulaire et soumission de connexion.
    Route::get('/login', [LoginController::class, 'showlogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    // Parcours de réinitialisation de mot de passe.
    Route::get('/reset-password', [ResetPasswordController::class, 'showresetpassword'])->name('password.forgot');
    Route::post('/reset-password', [ResetPasswordController::class, 'askResetPassword'])->name('password.ask');
    Route::post('/reset-password/confirm', [ResetPasswordController::class, 'resetPassword'])->name('password.reset');
});

// Déconnexion utilisateur connecté.
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Page de blocage pour les comptes non validés.
Route::middleware('auth')->group(function () {
    Route::view('/compte-non-valide', 'auth.account_pending')->name('account.pending');
});

// Espace utilisateur standard : accès autorisé uniquement après validation du compte.
Route::middleware(['auth', 'validated'])->prefix('utilisateur')->group(function () {
    // Tableau de bord et profil utilisateur.
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/historique', [UserController::class, 'history'])->name('user.history');
    Route::get('/profil', [UserController::class, 'profil'])->name('user.profile');
    Route::post('/profil/password', [UserController::class, 'updatePassword'])->name('user.password.update');

    // Gestion des réservations utilisateur.
    Route::post('/reservation', [ReservationController::class, 'requestReservation'])->name('user.reservation.request');
    Route::post('/reservation/{reservation}/close', [ReservationController::class, 'closeReservation'])->name('user.reservation.close');
});

// Espace administrateur : gestion utilisateurs, places, file d'attente et paramètres.
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Gestion des comptes utilisateurs.
    Route::get('/utilisateurs', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/utilisateurs', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::get('/utilisateurs/{user}', [AdminController::class, 'userDetail'])->name('admin.users.show');
    Route::post('/utilisateurs/{user}/validate', [AdminController::class, 'validateUser'])->name('admin.users.validate');
    Route::post('/utilisateurs/{user}/reset-password', [AdminController::class, 'resetUserPassword'])->name('admin.users.reset-password');

    // Actions admin sur les réservations.
    Route::post('/reservation/force', [ReservationController::class, 'forceAssign'])->name('admin.reservation.force');
    Route::post('/reservation/remove', [ReservationController::class, 'removeAssign'])->name('admin.reservation.remove');
    Route::post('/reservation/{reservation}/close', [ReservationController::class, 'closeReservation'])->name('admin.reservation.close');

    // Gestion des places de parking.
    Route::get('/places', [AdminController::class, 'places'])->name('admin.places');
    Route::get('/places/{spot}/historique', [AdminController::class, 'spotHistory'])->name('admin.places.history');
    Route::post('/places/assign', [AdminController::class, 'assignPlace'])->name('admin.places.assign');
    Route::post('/places', [AdminController::class, 'storePlace'])->name('admin.places.store');
    Route::put('/places/{spot}', [AdminController::class, 'updatePlace'])->name('admin.places.update');
    Route::delete('/places/{spot}', [AdminController::class, 'deletePlace'])->name('admin.places.delete');

    // Gestion de la file d'attente.
    Route::get('/liste-attente', [AdminController::class, 'waitingList'])->name('admin.waiting');
    Route::post('/liste-attente/{entry}/move', [AdminController::class, 'moveWaiting'])->name('admin.waiting.move');

    // Paramètres applicatifs administrateur.
    Route::get('/parametres', [AdminController::class, 'settingsPage'])->name('admin.settings.page');
    Route::post('/settings', [AdminController::class, 'settings'])->name('admin.settings');
});
