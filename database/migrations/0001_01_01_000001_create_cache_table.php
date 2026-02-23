<?php

/**
 * 数据库迁移：创建缓存相关表
 *
 * 当 CACHE_DRIVER 设为 database 时，Laravel 会使用这两张表存储缓存数据：
 * - cache        缓存键值对存储表
 * - cache_locks  缓存原子锁表（防止并发写入冲突）
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 执行迁移 —— 创建 cache 和 cache_locks 表
     */
    public function up(): void
    {
        // ========== 缓存数据表 ==========
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();   // 缓存键（主键）
            $table->mediumText('value');         // 缓存值（序列化后的数据）
            $table->integer('expiration');       // 过期时间戳（UNIX 时间）
        });

        // ========== 缓存锁表 ==========
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();   // 锁名称（主键）
            $table->string('owner');             // 锁的持有者标识
            $table->integer('expiration');       // 锁过期时间戳
        });
    }

    /**
     * 回滚迁移 —— 删除缓存表
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
