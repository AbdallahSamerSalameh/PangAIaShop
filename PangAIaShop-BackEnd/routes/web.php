<?php

use Illuminate\Support\Facades\Route;

// Default welcome page
Route::get('/', function () {
    return view('welcome');
});

// Redirect all frontend routes to the SPA
Route::get('/{any}', function () {
    return view('app'); // This would be your React app entry point
})->where('any', '.*')->name('react-app');
