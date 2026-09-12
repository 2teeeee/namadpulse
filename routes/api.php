<?php

use App\Http\Controllers\Api\Admin\ImportController as AdminImportController;
use App\Http\Controllers\Api\Admin\SymbolController as AdminSymbolController;
use App\Http\Controllers\Api\Admin\SymbolGroupController as AdminSymbolGroupController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AlertLogController;
use App\Http\Controllers\Api\AlertRuleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ScreenerController;
use App\Http\Controllers\Api\SymbolController;
use App\Http\Controllers\Api\WatchlistController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| مسیرهای مهمان
|--------------------------------------------------------------------------
*/
Route::post('/auth/login/send-otp', [AuthController::class, 'sendLoginOtp'])
    ->middleware('throttle:5,1');

Route::post('/auth/login/verify-otp', [AuthController::class, 'verifyLoginOtp'])
    ->middleware('throttle:8,1');

/*
|--------------------------------------------------------------------------
| مسیرهای محافظت‌شده (نیازمند Bearer Token معتبر)
|--------------------------------------------------------------------------
| مسیرهای فازهای بعدی (نمادها، واچ‌لیست‌ها، آلرت‌ها، پنل ادمین) به همین
| گروه اضافه می‌شوند.
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/symbols', [SymbolController::class, 'index']);
    Route::get('/symbols/pivots', [SymbolController::class, 'pivots']);
    Route::get('/symbols/{symbol}', [SymbolController::class, 'show']);

    Route::get('/screener', [ScreenerController::class, 'index']);
    Route::post('/screener/add-to-watchlist', [ScreenerController::class, 'addToWatchlist']);

    Route::apiResource('watchlists', WatchlistController::class);
    Route::post('/watchlists/{watchlist}/symbols', [WatchlistController::class, 'attachSymbol']);
    Route::delete('/watchlists/{watchlist}/symbols/{symbolId}', [WatchlistController::class, 'detachSymbol']);

    Route::apiResource('alert-rules', AlertRuleController::class)->except(['show']);
    Route::get('/alert-logs', [AlertLogController::class, 'index']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);

        Route::apiResource('symbol-groups', AdminSymbolGroupController::class)->except(['show']);
        Route::apiResource('symbols', AdminSymbolController::class);

        Route::get('/imports', [AdminImportController::class, 'index']);
        Route::post('/imports', [AdminImportController::class, 'store']);
        Route::get('/imports/{importRun}/status', [AdminImportController::class, 'status']);
    });
});
