<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // login_historiesテーブルの作成
        if (!Schema::hasTable('login_histories')) {
            Schema::create('login_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('ip_address', 45);
                $table->text('user_agent')->nullable();
                $table->string('device_type')->nullable();
                $table->timestamp('logged_in_at');
                $table->timestamp('logged_out_at')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'logged_in_at']);
            });
        }

        // sessionsテーブルにdevice_typeカラムを追加
        if (Schema::hasTable('sessions') && !Schema::hasColumn('sessions', 'device_type')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->string('device_type')->nullable()->after('user_agent');
            });
        }

        // usersテーブルに認証関連のカラムを追加
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // UUID追加
                if (!Schema::hasColumn('users', 'uuid')) {
                    $table->char('uuid', 36)->after('id')->unique();
                }
                
                if (!Schema::hasColumn('users', 'login_attempts')) {
                    $table->integer('login_attempts')->default(0)->after('password');
                }
                if (!Schema::hasColumn('users', 'last_login_attempt')) {
                    $table->timestamp('last_login_attempt')->nullable()->after('login_attempts');
                }
                if (!Schema::hasColumn('users', 'last_login_at')) {
                    $table->timestamp('last_login_at')->nullable()->after('last_login_attempt');
                }
                if (!Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('last_login_at');
                }
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }
                if (!Schema::hasColumn('users', 'preferred_language')) {
                    $table->enum('preferred_language', ['en', 'ja', 'zh'])->default('ja')->after('phone');
                }

                // ソフトデリート用のカラム追加
                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 追加したカラムを削除
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'uuid',
                    'login_attempts',
                    'last_login_attempt',
                    'last_login_at',
                    'is_active',
                    'phone',
                    'preferred_language',
                    'deleted_at'
                ]);
            });
        }

        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'device_type')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropColumn('device_type');
            });
        }

        // テーブルを削除
        Schema::dropIfExists('login_histories');
    }
};