<?php

use App\Http\Controllers\Admin\SymbolGroupController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AlertLogController;
use App\Http\Controllers\AlertRuleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SymbolController;
use App\Http\Controllers\WatchlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| مسیرهای مهمان (بدون نیاز به ورود)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'sendLoginOtp'])
        ->middleware('throttle:5,1')
        ->name('login.send-otp');

    Route::get('/login/verify', [AuthController::class, 'showLoginVerify'])->name('login.verify.show');
    Route::post('/login/verify', [AuthController::class, 'verifyLoginOtp'])
        ->middleware('throttle:8,1')
        ->name('login.verify');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/verify-mobile', [AuthController::class, 'showVerify'])->name('auth.verify.show');
    Route::post('/verify-mobile', [AuthController::class, 'verify'])->name('auth.verify');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::redirect('/', '/dashboard');

/*
|--------------------------------------------------------------------------
| مسیرهای کاربران وارد‌شده (هر دو نقش admin و trader)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('watchlists', WatchlistController::class)->except(['show'])
        ->parameters(['watchlists' => 'watchlist']);
    Route::get('/watchlists/{watchlist}', [WatchlistController::class, 'show'])->name('watchlists.show');

    Route::get('/symbols', [SymbolController::class, 'index'])->name('symbols.index');
    Route::get('/symbols/{symbol}', [SymbolController::class, 'show'])->name('symbols.show');

    Route::resource('alert-rules', AlertRuleController::class)
        ->only(['index', 'create', 'store', 'update', 'destroy'])
        ->parameters(['alert-rules' => 'alertRule']);

    Route::get('/alert-logs', [AlertLogController::class, 'index'])->name('alert-logs.index');

    /*
    |----------------------------------------------------------------------
    | مسیرهای مخصوص ادمین
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)
            ->only(['index', 'edit', 'update']);

        Route::resource('symbol-groups', SymbolGroupController::class)
            ->only(['index', 'create', 'store', 'update', 'destroy'])
            ->parameters(['symbol-groups' => 'symbolGroup']);

        Route::resource('symbols', \App\Http\Controllers\Admin\SymbolController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    });
});
