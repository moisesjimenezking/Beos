<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductPriceController;

Route::prefix('products/{id}/prices')->group(function () {
    Route::get('/', [ProductPriceController::class, 'index']);
    Route::post('/', [ProductPriceController::class, 'store']);
});
