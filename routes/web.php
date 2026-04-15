<?php

use App\Http\Controllers\PanelScreenshotController;
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

// Panel screenshot route — renders a single panel in isolation using dummy data
// generated from its fieldset. Only available outside production.
if (! app()->environment('production')) {
    Route::get('__panel-screenshot/{handle}', [PanelScreenshotController::class, 'show']);
}
