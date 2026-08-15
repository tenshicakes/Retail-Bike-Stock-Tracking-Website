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

    Route::post('/dashboard/process-stock', [DashboardController::class, 'processStock']);

    Route::get('/dashboard/logs', [LogController::class, 'index']);
   Route::get('/api/subcategories', [App\Http\Controllers\ProductController::class, 'getSubcategories']); // For cascading dropdown
    Route::post('/dashboard/logs/export', [LogController::class, 'exportPdf']);

    Route::get('/dashboard/products', [ProductController::class, 'index']);
    Route::get('/api/products/search', [ProductController::class, 'search']);
    Route::get('/dashboard/lowstock', [ProductController::class, 'lowStockIndex']);
    Route::get('/api/lowstock/search', [ProductController::class, 'lowStockSearch']);
    Route::get('/dashboard/nostock', [ProductController::class, 'noStockIndex']);
    Route::get('/api/nostock/search', [ProductController::class, 'noStockSearch']);
    Route::post('/dashboard/products/bulk-update', [ProductController::class, 'bulkUpdate']);
    Route::get('/logout', [AuthController::class, 'logout']);

}); 