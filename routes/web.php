<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// if we're in staging
// we can access the cutup
if (env('APP_ENV') == 'staging'){
    Route::get('cutup/{page}', function($page) {
        return view("cutup.{$page}");
    });
}
