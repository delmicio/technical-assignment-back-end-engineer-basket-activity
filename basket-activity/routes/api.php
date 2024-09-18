<?php

use App\Http\Controllers\BasketController;
use App\Http\Controllers\UserController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/baskets', [BasketController::class, 'getUserBasket']);
    Route::post('/baskets', [BasketController::class, 'store']);
    Route::patch('/baskets/{user_id}/products/{product_id}', [BasketController::class, 'removeProductFromBasket']);
    Route::get('/removed-items', [BasketController::class, 'removedItems']);

    Route::middleware('throttle:5,1')->group(function () {
        Route::get('/removed-items/export-csv', [BasketController::class, 'downloadRemovedItemsCsv']);
    });

    Route::get('/products', function () {
        return Product::all();
    });
});
