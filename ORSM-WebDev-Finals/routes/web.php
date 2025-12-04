<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
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

// Admin Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminController::class, 'listUsers'])->name('index');
        Route::get('/create', [AdminController::class, 'createUser'])->name('create');
        Route::post('/', [AdminController::class, 'storeUser'])->name('store');
        Route::get('/{user}/edit', [AdminController::class, 'editUser'])->name('edit');
        Route::patch('/{user}', [AdminController::class, 'updateUser'])->name('update');
        Route::post('/{user}/deactivate', [AdminController::class, 'deactivateUser'])->name('deactivate');
        Route::post('/{user}/activate', [AdminController::class, 'activateUser'])->name('activate');
        Route::delete('/{user}', [AdminController::class, 'deleteUser'])->name('delete');
    });

    // System Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [AdminController::class, 'editSettings'])->name('edit');
        Route::patch('/', [AdminController::class, 'updateSettings'])->name('update');
    });

    // Activity Logs
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [AdminController::class, 'activityLogs'])->name('index');
        Route::get('/{log}', [AdminController::class, 'viewLogDetails'])->name('show');
        Route::get('/export/csv', [AdminController::class, 'exportLogs'])->name('export');
    });
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
