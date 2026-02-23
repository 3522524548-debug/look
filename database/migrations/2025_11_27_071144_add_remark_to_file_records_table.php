<?php

/**
 * 数据库迁移：为 file_records 表添加 remark（备注）字段
 *
 * 在档案记录表中增加一个可选的备注字段，最长 500 字符。
 * 用于记录档案的额外说明信息。
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_records', function (Blueprint $table) {
            // 新增一个可为空的备注字段
            $table->string('remark', 500)->nullable()->after('mime');
            // 如果你现在的表里没有 mime 字段，就把 after('mime') 删掉，写成：
            // $table->string('remark', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('file_records', function (Blueprint $table) {
            $table->dropColumn('remark');
        });
    }
};
