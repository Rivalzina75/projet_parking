<?php

use Illuminate\Support\Facades\Route;

// Home page - first page seen by the user (not authenticated)
Route::get('/', function () {
    return view('home');
});
