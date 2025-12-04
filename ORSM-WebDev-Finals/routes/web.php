<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/products_inventory_management.php';

// Example role-protected routes (uses the 'role' middleware alias registered in AppServiceProvider)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

Route::middleware(['auth', 'role:staff,admin'])->group(function () {
    Route::get('/staff', function () {
        return view('staff.dashboard');
    })->name('staff.dashboard');
});

require __DIR__ . '/auth.php';
require __DIR__.'/products_inventory_management.php';



require __DIR__ . '/auth.php';
require __DIR__ . '/products_inventory_management.php';

use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

// Store & Cart
Route::get('/store', [CartController::class, 'storeView'])->name('store');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::get('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout & Orders (temporarily no auth requirement - user permissions TBD)
use App\Http\Controllers\CheckoutController;

// TODO: Re-enable auth middleware once user permissions are implemented
// Route::middleware('auth')->group(function () {
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
// });
