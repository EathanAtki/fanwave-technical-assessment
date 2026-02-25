<?php

use App\Http\Controllers\Api\CoinController;
use App\Http\Controllers\Api\MarketController;
use Illuminate\Support\Facades\Route;

Route::middleware(['request.id', 'throttle:crypto-api'])->group(function (): void {
    Route::get('/markets', [MarketController::class, 'index']);
    Route::get('/coins/{id}', [CoinController::class, 'show'])->where('id', '[a-z0-9-]+');
});
