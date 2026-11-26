<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BackupController;

// --- GUEST ROUTES  ---
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate']);
    Route::post('/login/2fa', [AuthController::class, 'verifyTwoFactor'])->name('login.2fa.verify');
    Route::post('/login/2fa/cancel', [AuthController::class, 'cancelTwoFactor'])->name('login.2fa.cancel');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::get('/captcha/refresh', [AuthController::class, 'refreshCaptcha'])->name('captcha.refresh');
});

// --- PROTECTED ROUTES  ---
Route::middleware(['auth', PreventBackHistory::class])->group(function () {
    Route::get('/api/dashboard/charts', [DashboardController::class, 'chartData'])->name('dashboard.charts');
    Route::get('/dashboard/home', [DashboardController::class, 'home']);
    Route::post('/dashboard/process-stock', [DashboardController::class, 'processStock'])->middleware('role:Administrator,Owner,Staff,Mechanic');
    Route::get('/api/subcategories', [App\Http\Controllers\ProductController::class, 'getSubcategories'])->middleware('role:Administrator,Owner,Staff,Mechanic');

    Route::get('/dashboard/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/dashboard/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.updateInfo');
    Route::put('/dashboard/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::post('/dashboard/profile/logout-sessions', [ProfileController::class, 'logoutOtherSessions'])->name('profile.logoutSessions');
    Route::post('/dashboard/profile/2fa/setup', [ProfileController::class, 'setupTwoFactor'])->name('profile.2fa.setup');
    Route::post('/dashboard/profile/2fa/enable', [ProfileController::class, 'enableTwoFactor'])->name('profile.2fa.enable');
    Route::post('/dashboard/profile/2fa/disable', [ProfileController::class, 'disableTwoFactor'])->name('profile.2fa.disable');

    Route::get('/dashboard/products', [ProductController::class, 'index'])->middleware('role:Administrator,Owner,Staff,Mechanic');
    Route::post('/dashboard/products', [ProductController::class, 'store'])->middleware('role:Administrator,Owner');
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
        Route::get('/dashboard/accounts', [AccountController::class, 'index']);
        Route::post('/dashboard/accounts', [AccountController::class, 'store']);
        Route::put('/dashboard/accounts/{account}', [AccountController::class, 'update']);
        Route::post('/dashboard/accounts/{account}/reset-2fa', [AccountController::class, 'resetTwoFactor'])->name('accounts.reset2fa');
        Route::delete('/dashboard/accounts', [AccountController::class, 'destroy']);

        Route::get('/dashboard/backup', [BackupController::class, 'index'])->name('backup.index');
        Route::post('/dashboard/backup/create', [BackupController::class, 'create'])->name('backup.create');
        Route::get('/dashboard/backup/download/{filename}', [BackupController::class, 'download'])->name('backup.download');
        Route::delete('/dashboard/backup/{filename}', [BackupController::class, 'destroy'])->name('backup.destroy');
        Route::post('/dashboard/backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
    });

    Route::get('/logout', [AuthController::class, 'logout']);
});