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
