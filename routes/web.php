<?php

use Illuminate\Support\Facades\Route;
use Statamic\View\View;

// if we're in staging
// we can access the cutup
if (app()->environment() == 'staging'){
    Route::get('cutup/{page}', function($page) {
        return View::make("cutup.{$page}")
            ->layout('layout');
    });
}
