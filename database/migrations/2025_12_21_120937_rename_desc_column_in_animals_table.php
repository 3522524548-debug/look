<?php

/**
 * 数据库迁移：重命名 animals 表中的描述字段
 *
 * 将 animals 表中的中文字段名 '描述' 重命名为英文 'description'。
 * 兼容 SQLite 数据库，先检查字段是否存在。
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 针对 SQLite 数据库的特殊兼容处理
        if (Schema::hasColumn('animals', '描述')) {
            Schema::table('animals', function (Blueprint $table) {
                $table->renameColumn('描述', 'description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('animals', 'description')) {
            Schema::table('animals', function (Blueprint $table) {
                $table->renameColumn('description', '描述');
            });
        }
    }
};