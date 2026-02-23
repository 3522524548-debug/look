<?php

/**
 * 数据库迁移：创建队列任务相关表
 *
 * 当 QUEUE_CONNECTION 设为 database 时，Laravel 使用这三张表管理异步队列任务：
 * - jobs          待执行的队列任务
 * - job_batches   批量任务（Batch）的汇总信息
 * - failed_jobs   执行失败的任务记录（用于排查和重试）
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 执行迁移 —— 创建队列任务三张表
     */
    public function up(): void
    {
        // ========== 1. 队列任务表 ==========
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();                                        // 自增主键
            $table->string('queue')->index();                    // 队列名称（如 default）
            $table->longText('payload');                          // 序列化后的任务数据
            $table->unsignedTinyInteger('attempts');              // 已尝试执行次数
            $table->unsignedInteger('reserved_at')->nullable();   // 被 Worker 锁定的时间
            $table->unsignedInteger('available_at');               // 可执行时间（延迟队列用）
            $table->unsignedInteger('created_at');                 // 入队时间
        });

        // ========== 2. 批量任务表 ==========
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();                      // 批次 ID
            $table->string('name');                               // 批次名称
            $table->integer('total_jobs');                         // 总任务数
            $table->integer('pending_jobs');                       // 待完成数
            $table->integer('failed_jobs');                        // 失败数
            $table->longText('failed_job_ids');                    // 失败的任务 ID 列表
            $table->mediumText('options')->nullable();             // 批次选项配置
            $table->integer('cancelled_at')->nullable();           // 取消时间
            $table->integer('created_at');                         // 创建时间
            $table->integer('finished_at')->nullable();            // 完成时间
        });

        // ========== 3. 失败任务表 ==========
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();                                         // 自增主键
            $table->string('uuid')->unique();                     // 唯一标识 UUID
            $table->text('connection');                            // 队列连接名
            $table->text('queue');                                 // 队列名称
            $table->longText('payload');                           // 任务数据
            $table->longText('exception');                         // 异常信息
            $table->timestamp('failed_at')->useCurrent();          // 失败时间
        });
    }

    /**
     * 回滚迁移 —— 删除队列相关表
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
