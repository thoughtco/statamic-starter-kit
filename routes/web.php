<?php

use Illuminate\Support\Facades\Route;

// if we're in staging
// we can access the cutup
if (app()->environment() == 'staging'){
    Route::get('cutup/{page}', function($page) {
        return view("cutup.{$page}");
    });
}
