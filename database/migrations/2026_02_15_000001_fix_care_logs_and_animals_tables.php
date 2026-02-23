<?php

/**
 * 数据库迁移：修复补齐 care_logs 和 animals 表的缺失字段
 *
 * 本迁移为已有的表补充字段（安全模式，先检查字段是否已存在）：
 * - care_logs 表：补充 log_date、next_visit_at、created_by、user_id、height
 * - animals 表：补充 photo_path（动物照片路径）
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 执行迁移 —— 补齐缺失字段
     */
    public function up(): void
    {
        // 补齐 care_logs 表缺失的列
        Schema::table('care_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('care_logs', 'log_date')) {
                $table->date('log_date')->nullable()->after('care_date');
            }
            if (!Schema::hasColumn('care_logs', 'next_visit_at')) {
                $table->datetime('next_visit_at')->nullable()->after('temperature');
            }
            if (!Schema::hasColumn('care_logs', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('next_visit_at');
            }
            if (!Schema::hasColumn('care_logs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('care_logs', 'height')) {
                $table->decimal('height', 6, 2)->nullable()->after('weight');
            }
        });

        // 补齐 animals 表缺失的 photo_path 列
        if (!Schema::hasColumn('animals', 'photo_path')) {
            Schema::table('animals', function (Blueprint $table) {
                $table->string('photo_path')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        Schema::table('care_logs', function (Blueprint $table) {
            $columns = ['log_date', 'next_visit_at', 'created_by', 'user_id', 'height'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('care_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (Schema::hasColumn('animals', 'photo_path')) {
            Schema::table('animals', function (Blueprint $table) {
                $table->dropColumn('photo_path');
            });
        }
    }
};
