<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyController;

Route::prefix('currency')->group(function () {
    Route::get('/', [CurrencyController::class, 'index']);
    Route::post('/', [CurrencyController::class, 'store']);
    Route::get('/{id}', [CurrencyController::class, 'show']);
    Route::put('/{id}', [CurrencyController::class, 'update']);
    Route::delete('/{id}', [CurrencyController::class, 'destroy']);
});