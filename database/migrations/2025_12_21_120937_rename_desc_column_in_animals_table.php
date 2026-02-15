<?php

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