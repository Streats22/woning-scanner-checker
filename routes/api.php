<?php

use App\Http\Controllers\ListingController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['ok' => true]));

Route::post('/analyze', [ListingController::class, 'analyze']);
