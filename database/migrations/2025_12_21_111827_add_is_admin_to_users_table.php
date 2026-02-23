<?php

/**
 * 数据库迁移：为 users 表添加 is_admin 字段
 *
 * 新增布尔型字段 is_admin，用于快速判断用户是否为管理员。
 * 与 User 模型的 isAdmin() 方法配合使用。
 * 默认值为 false（非管理员）。
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 执行迁移 —— 添加 is_admin 字段
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 增加 is_admin 字段，默认为 0
            $table->boolean('is_admin')->default(false)->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};