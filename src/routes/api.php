<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\SleepRecordController;
use App\Http\Controllers\HealthGoalController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API Version 1
Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | 認証関連のルート
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        // 認証不要のルート
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [PasswordController::class, 'forgotPassword']);
        Route::post('/reset-password', [PasswordController::class, 'resetPassword']);
        
        // メール認証関連
        Route::post('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
            ->name('verification.verify');
        Route::post('/email/resend', [VerificationController::class, 'resend'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('verification.resend');

        // 認証が必要なルート
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutFromAllDevices']);
            Route::get('/refresh', [AuthController::class, 'refresh']);
            
            // パスワード変更
            Route::post('/password/change', [PasswordController::class, 'changePassword']);
        });
    });
}