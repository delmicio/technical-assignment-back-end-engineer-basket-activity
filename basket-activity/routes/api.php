<?php

use App\Http\Controllers\BasketController;
use App\Http\Controllers\UserController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

// Endpoint to get a list of users
Route::get('/users', [UserController::class, 'index']);

// Get basket items for a specific user
Route::get('/basket', [BasketController::class, 'getUserBasket']);

// Add product to the basket for a specific user
Route::post('/basket', [BasketController::class, 'store']);

// Remove a product from a user's basket (requires user_id and product_id)
Route::delete('/basket/{user_id}/{product_id}', [BasketController::class, 'removeProductFromBasket']);

// Get removed items for all users
Route::get('/basket/removed-items', [BasketController::class, 'removedItems'])->name('basket.removedItems');

// Download a CSV of removed items with date filters
Route::get('/sales/removed-items-csv', [BasketController::class, 'downloadRemovedItemsCsv']);

// Get all products
Route::get('/products', function () {
    return Product::all();
});
