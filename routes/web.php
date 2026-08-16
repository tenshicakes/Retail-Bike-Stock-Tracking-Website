<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ProductController;

// --- GUEST ROUTES (Not logged in) ---
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate']);
});

// --- PROTECTED ROUTES (Must be logged in) ---
Route::middleware(['auth', PreventBackHistory::class])->group(function () {
    Route::get('/dashboard/home', [DashboardController::class, 'home']);
    Route::post('/dashboard/process-stock', [DashboardController::class, 'processStock'])->middleware('role:Administrator,Owner,Staff,Mechanic');
    Route::get('/api/subcategories', [App\Http\Controllers\ProductController::class, 'getSubcategories'])->middleware('role:Administrator,Owner,Staff,Mechanic');

    Route::get('/dashboard/products', [ProductController::class, 'index'])->middleware('role:Administrator,Owner,Staff,Mechanic');
    Route::get('/api/products/search', [ProductController::class, 'search'])->middleware('role:Administrator,Owner,Staff,Mechanic');
    Route::post('/dashboard/products/bulk-update', [ProductController::class, 'bulkUpdate'])->middleware('role:Administrator,Owner,Mechanic');

    Route::middleware('role:Administrator,Owner,Staff')->group(function () {
        Route::get('/dashboard/logs', [LogController::class, 'index']);
        Route::post('/dashboard/logs/export', [LogController::class, 'exportPdf']);
    });

    Route::middleware('role:Administrator,Owner')->group(function () {
        Route::get('/dashboard/lowstock', [ProductController::class, 'lowStockIndex']);
        Route::get('/api/lowstock/search', [ProductController::class, 'lowStockSearch']);
        Route::get('/dashboard/nostock', [ProductController::class, 'noStockIndex']);
        Route::get('/api/nostock/search', [ProductController::class, 'noStockSearch']);
    });

    Route::middleware('role:Administrator')->group(function () {
        Route::get('/dashboard/accounts', fn () => view('dashboard.accounts'));
    });

    Route::get('/logout', [AuthController::class, 'logout']);
});