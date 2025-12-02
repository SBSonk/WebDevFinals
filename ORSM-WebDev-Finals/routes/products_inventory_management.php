<?php

use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Product;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::resource('products', ProductController::class);
Route::resource('inventory', InventoryController::class);