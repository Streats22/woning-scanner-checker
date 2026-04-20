<?php

use App\Http\Controllers\ListingController;
use Illuminate\Support\Facades\Route;

/*
 * GET / wordt door Nginx naar de Nuxt-server gedeeld (zie deploy/dehuurradar.nl.conf).
 * Legacy POST /analyze blijft voor oude clients; de app gebruikt POST /api/analyze.
 */
Route::post('/analyze', [ListingController::class, 'analyze']);

Route::get('/report/{idOrSlug}/pdf', [ListingController::class, 'reportPdf'])
    ->where('idOrSlug', '[A-Za-z0-9\-\.]+')
    ->name('report.pdf');

Route::get('/report/{idOrSlug}', [ListingController::class, 'showReport'])
    ->where('idOrSlug', '[A-Za-z0-9\-\.]+')
    ->name('report.show');
