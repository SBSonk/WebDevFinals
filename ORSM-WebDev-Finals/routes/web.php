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

require __DIR__ . '/auth.php';
require __DIR__ . '/products_inventory_management.php';

use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PaymentController;

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

// Admin reporting routes (sales dashboard, CSV/PDF export)
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->group(function () {
    // --- ADDED ADMIN REPORTING ROUTES ---
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('admin.reports.dashboard');
        Route::get('sales', [ReportsController::class, 'sales'])->name('admin.reports.sales');
        Route::get('inventory', [ReportsController::class, 'inventory'])->name('admin.reports.inventory');
        Route::get('performance', [ReportsController::class, 'productPerformance'])->name('admin.reports.productPerformance');
        Route::get('movements', [ReportsController::class, 'inventoryMovements'])->name('admin.reports.inventoryMovements');

        // Exports
        Route::get('sales/export-csv', [ReportsController::class, 'exportSalesCSV'])->name('admin.reports.export.csv');
        Route::get('sales/export-excel', [ReportsController::class, 'exportSalesExcel'])->name('admin.reports.export.excel');
        Route::get('sales/export-pdf', [ReportsController::class, 'exportSalesPDF'])->name('admin.reports.export.pdf');
    });

    // Payment simulation routes
    Route::get('/payments', [PaymentController::class, 'index'])->name('admin.payments.index');
    Route::post('/payments/{orderId}/simulate-cod', [PaymentController::class, 'simulateCod'])->name('admin.payments.simulate-cod');
    Route::post('/payments/{orderId}/simulate-success', [PaymentController::class, 'simulatePaymentSuccess'])->name('admin.payments.simulate-success');
    Route::post('/payments/{orderId}/simulate-failed', [PaymentController::class, 'simulatePaymentFailed'])->name('admin.payments.simulate-failed');
    Route::post('/payments/bulk-update', [PaymentController::class, 'bulkUpdatePaymentStatus'])->name('admin.payments.bulk-update');
    Route::get('/payments/stats', [PaymentController::class, 'paymentStats'])->name('admin.payments.stats');
    Route::post('/payments/create-test', [PaymentController::class, 'createTestOrder'])->name('admin.payments.create-test');
});
