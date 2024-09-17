<?php

use App\Http\Controllers\BasketController;
use Illuminate\Support\Facades\Route;

Route::resource('basket', BasketController::class)->only(['index', 'store', 'destroy']);
Route::get('/basket/removed-items', [BasketController::class, 'removedItems'])->name('basket.removedItems');
Route::get('/sales/removed-items-csv', [BasketController::class, 'downloadRemovedItemsCsv']);
