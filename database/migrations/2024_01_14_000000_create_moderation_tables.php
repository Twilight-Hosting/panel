<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bans')) {
            Schema::dropIfExists('bans');
        }
        if (Schema::hasTable('warnings')) {
            Schema::dropIfExists('warnings');
        }
        if (Schema::hasTable('blocks')) {
            Schema::dropIfExists('blocks');
        }
        if (Schema::hasTable('moderation_settings')) {
            Schema::dropIfExists('moderation_settings');
        }
        if (Schema::hasTable('moderation_logs')) {
            Schema::dropIfExists('moderation_logs');
        }
        if (Schema::hasTable('failed_login_attempts')) {
            Schema::dropIfExists('failed_login_attempts');
        }
        if (Schema::hasTable('login_history')) {
            Schema::dropIfExists('login_history');
        }

        Schema::create('bans', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('banned_by');
            $table->text('reason');
            $table->string('ip_address')->nullable();
            $table->boolean('ip_ban')->default(false);
            $table->timestamp('banned_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('warnings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('warned_by');
            $table->text('reason');
            $table->timestamp('warned_at')->nullable();
            $table->timestamps();
        });

        Schema::create('blocks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('blocked_by');
            $table->text('reason');
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('moderation_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('vpn_blocker_enabled')->default(false);
            $table->text('discord_webhook_url')->nullable();
            $table->boolean('discord_webhook_enabled')->default(false);
            $table->integer('max_failed_attempts')->default(5);
            $table->integer('lockout_duration_minutes')->default(5);
            $table->boolean('track_login_history')->default(true);
            $table->boolean('block_on_ban')->default(true);
            $table->timestamps();
        });

        Schema::create('moderation_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('action_by')->nullable();
            $table->string('action_type', 50);
            $table->string('target_type', 50)->nullable();
            $table->unsignedInteger('target_id')->nullable();
            $table->text('description');
            $table->text('reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('failed_login_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('attempted_at');
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('blocked_until')->nullable();
            $table->timestamps();
        });

        Schema::create('login_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('email');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('successful')->default(false);
            $table->text('failure_reason')->nullable();
            $table->timestamp('logged_in_at');
            $table->timestamps();
        });

        Schema::table('bans', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('banned_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_active']);
        });

        Schema::table('warnings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('warned_by')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });

        Schema::table('blocks', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('blocked_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_active']);
        });

        Schema::table('moderation_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('action_by')->references('id')->on('users')->onDelete('set null');
            $table->index('action_type');
            $table->index('target_type');
            $table->index('created_at');
            $table->index(['target_type', 'target_id']);
        });

        Schema::table('failed_login_attempts', function (Blueprint $table) {
            $table->index('email');
            $table->index('ip_address');
            $table->index('attempted_at');
            $table->index('is_blocked');
            $table->index(['email', 'attempted_at']);
            $table->index(['ip_address', 'attempted_at']);
        });

        Schema::table('login_history', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('user_id');
            $table->index('ip_address');
            $table->index('successful');
            $table->index('logged_in_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_history');
        Schema::dropIfExists('failed_login_attempts');
        Schema::dropIfExists('moderation_logs');
        Schema::dropIfExists('moderation_settings');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('warnings');
        Schema::dropIfExists('bans');
    }
};

