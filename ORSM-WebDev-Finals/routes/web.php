<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Auth & module routes
require __DIR__.'/auth.php';
require __DIR__.'/products_inventory_management.php';

// Store & Cart (public)
Route::get('/store', [CartController::class, 'storeView'])->name('store');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::get('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout & Orders (auth only)
Route::middleware(['auth'])->group(function () {
    // In production, block admin checkout/order creation via middleware
    Route::get('/checkout', [CheckoutController::class, 'index'])->middleware('restrict_admin_checkout')->name('checkout.index');
    Route::post('/orders', [OrderController::class, 'store'])->middleware('restrict_admin_checkout')->name('orders.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
});

// Admin reporting routes (sales dashboard, CSV/PDF export)
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->group(function () {
    // Admin dashboard alias (for links expecting admin.dashboard)
    Route::get('/dashboard', function () {
        return redirect()->route('admin.sales');
    })->name('admin.dashboard');

    Route::get('/sales', [ReportsController::class, 'index'])->name('admin.sales');
    Route::get('/sales/export/csv', [ReportsController::class, 'exportCsv'])->name('admin.sales.export.csv');
    Route::get('/sales/export/pdf', [ReportsController::class, 'exportPdf'])->name('admin.sales.export.pdf');
    Route::get('/sales/export/check/{batch}', [ReportsController::class, 'exportCheck'])->name('admin.sales.export.check');
    Route::get('/sales/export/download/{batch}', [ReportsController::class, 'exportDownload'])->name('admin.sales.export.download');

    // Payment simulation routes
    Route::get('/payments', [PaymentController::class, 'index'])->name('admin.payments.index');
    Route::post('/payments/{orderId}/simulate-cod', [PaymentController::class, 'simulateCod'])->name('admin.payments.simulate-cod');
    Route::post('/payments/{orderId}/simulate-success', [PaymentController::class, 'simulatePaymentSuccess'])->name('admin.payments.simulate-success');
    Route::post('/payments/{orderId}/simulate-failed', [PaymentController::class, 'simulatePaymentFailed'])->name('admin.payments.simulate-failed');
    Route::post('/payments/bulk-update', [PaymentController::class, 'bulkUpdatePaymentStatus'])->name('admin.payments.bulk-update');
    Route::get('/payments/stats', [PaymentController::class, 'paymentStats'])->name('admin.payments.stats');
    Route::post('/payments/create-test', [PaymentController::class, 'createTestOrder'])->name('admin.payments.create-test');
});
