<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\SleepRecordController;
use App\Http\Controllers\HealthGoalController;
use App\Http\Controllers\AnalysisController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// 認証用
Route::middleware('auth:sanctum')->group(function () {
    // ログイン機能
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);

    // 健康記録
    Route::post('/health-records', [HealthRecordController::class, 'store']);
    Route::get('/health-records', [HealthRecordController::class, 'index']);
    Route::get('/health-records/{id}', [HealthRecordController::class, 'show']);

    // 活動
    Route::post('/activities', [ActivityController::class, 'store']);
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::get('/activities/{id}', [ActivityController::class, 'show']);

    // 饮食
    Route::post('/meals', [MealController::class, 'store']);
    Route::get('/meals', [MealController::class, 'index']);
    Route::get('/meals/{id}', [MealController::class, 'show']);

    // 睡眠
    Route::post('/sleep-records', [SleepRecordController::class, 'store']);
    Route::get('/sleep-records', [SleepRecordController::class, 'index']);
    Route::get('/sleep-records/{id}', [SleepRecordController::class, 'show']);

    // 健康目标
    Route::post('/health-goals', [HealthGoalController::class, 'store']);
    Route::get('/health-goals', [HealthGoalController::class, 'index']);
    Route::put('/health-goals/{id}', [HealthGoalController::class, 'update']);
    Route::delete('/health-goals/{id}', [HealthGoalController::class, 'destroy']);

    // 数据分析和建议
    Route::get('/analysis/health-summary', [AnalysisController::class, 'healthSummary']);
    Route::get('/analysis/activity-trends', [AnalysisController::class, 'activityTrends']);
    Route::get('/analysis/nutrition-insights', [AnalysisController::class, 'nutritionInsights']);
    Route::get('/recommendations', [AnalysisController::class, 'recommendations']);
});