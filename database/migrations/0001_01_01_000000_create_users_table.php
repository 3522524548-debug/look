<?php

/**
 * 数据库迁移：创建用户相关基础表
 *
 * 本迁移由 Laravel 框架默认生成，创建三张核心表：
 * - users            用户表（存储账号基本信息）
 * - password_reset_tokens  密码重置令牌表
 * - sessions         会话表（用于 session 驱动为 database 时存储会话数据）
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 执行迁移 —— 创建 users、password_reset_tokens、sessions 三张表
     */
    public function up(): void
    {
        // ========== 1. 用户表 ==========
        Schema::create('users', function (Blueprint $table) {
            $table->id();                                      // 自增主键
            $table->string('name');                            // 用户昵称
            $table->string('email')->unique();                 // 邮箱（唯一索引，用于登录）
            $table->timestamp('email_verified_at')->nullable();// 邮箱验证时间，null 表示未验证
            $table->string('password');                        // 加密后的密码
            $table->rememberToken();                           // "记住我" 功能所需的 token
            $table->timestamps();                              // created_at / updated_at
        });

        // ========== 2. 密码重置令牌表 ==========
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();   // 邮箱作为主键
            $table->string('token');              // 重置令牌
            $table->timestamp('created_at')->nullable(); // 令牌创建时间
        });

        // ========== 3. 会话表 ==========
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();                   // 会话 ID
            $table->foreignId('user_id')->nullable()->index();  // 关联用户（未登录时为 null）
            $table->string('ip_address', 45)->nullable();       // 客户端 IP（兼容 IPv6）
            $table->text('user_agent')->nullable();              // 浏览器 User-Agent
            $table->longText('payload');                         // 序列化后的会话数据
            $table->integer('last_activity')->index();           // 最后活跃时间戳（用于过期清理）
        });
    }

    /**
     * 回滚迁移 —— 按依赖顺序删除表
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
