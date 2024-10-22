<?php
// app/Http/Controllers/Auth/AuthController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * ユーザー登録
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'preferred_language' => $request->preferred_language ?? 'en',
            ]);

            // プロフィール情報の作成
            $user->profile()->create($request->validated());

            // メール認証の送信
            $user->sendEmailVerificationNotification();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully',
                'user' => new UserResource($user),
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ログイン
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                $this->incrementLoginAttempts($user);
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            if ($this->isAccountLocked($user)) {
                return response()->json([
                    'message' => 'Account is locked. Please try again later.'
                ], 423);
            }

            // ログイン成功時の処理
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->update([
                'login_attempts' => 0,
                'last_login_at' => now(),
            ]);

            // ログイン履歴の保存
            $user->loginHistories()->create([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_in_at' => now(),
                'device_type' => $this->detectDeviceType($request->userAgent()),
            ]);

            return response()->json([
                'message' => 'Login successful',
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ログアウト
     */
    public function logout(Request $request)
    {
        try {
            // 現在のログイン履歴を更新
            $user = $request->user();
            $user->loginHistories()->latest()->first()?->update([
                'logged_out_at' => now()
            ]);

            // 現在のトークンを削除
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * すべてのデバイスからログアウト
     */
    public function logoutFromAllDevices(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Successfully logged out from all devices'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * トークンのリフレッシュ
     */
    public function refresh(Request $request)
    {
        try {
            $user = $request->user();
            $user->tokens()->delete();
            
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * アカウントがロックされているかチェック
     */
    private function isAccountLocked(User $user): bool
    {
        $maxAttempts = 5;
        $lockoutDuration = 30; // minutes

        if ($user->login_attempts >= $maxAttempts && 
            $user->last_login_attempt && 
            Carbon::parse($user->last_login_attempt)->addMinutes($lockoutDuration)->isFuture()) {
            return true;
        }

        return false;
    }

    /**
     * ログイン試行回数を増やす
     */
    private function incrementLoginAttempts(?User $user): void
    {
        if ($user) {
            $user->increment('login_attempts');
            $user->last_login_attempt = now();
            $user->save();
        }
    }

    /**
     * デバイスタイプを検出
     */
    private function detectDeviceType(string $userAgent): string
    {
        if (preg_match('/(android|iphone|ipad|windows phone)/i', $userAgent)) {
            return 'mobile';
        }
        return 'desktop';
    }
}