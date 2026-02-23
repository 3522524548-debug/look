<?php

/**
 * 数据库迁移：创建 notifications（通知）表
 *
 * Laravel 通知系统的数据库驱动表。存储发给用户的系统通知，
 * 如领养申请审核结果通知（AdoptionStatusNotification）。
 * 使用 UUID 作为主键，支持多态关联（morphs）。
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 执行迁移 —— 创建通知表
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
