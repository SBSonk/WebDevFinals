<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\Product;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::resource('products', ProductController::class);
Route::resource('inventory', InventoryController::class);
Route::resource('suppliers', SupplierController::class);
Route::resource('categories', CategoryController::class);
Route::resource('inventory_transactions', InventoryTransactionController::class);