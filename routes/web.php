<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\auth\RegisterController;
use App\Http\Controllers\auth\ResetPasswordController;

// Home page - first page seen by the user (not authenticated)
Route::get('/', function () {
    return view('home');
});

// Authentication routes

// register show
Route::get('/register', [RegisterController::class, 'showregister']);
// register form
Route::post('/register', [RegisterController::class, 'register']);

// login show
Route::get('/login', [LoginController::class, 'showlogin']);
// login form
Route::post('/login', [LoginController::class, 'login']);

// ask reset password show
Route::get('/reset_password', [ResetPasswordController::class, 'showresetpassword']);
// ask reset password form
Route::post('/reset_password', [ResetPasswordController::class, 'askResetPassword']);

// reset password form
Route::post('/reset_password/confirm', [ResetPasswordController::class, 'resetPassword']);
