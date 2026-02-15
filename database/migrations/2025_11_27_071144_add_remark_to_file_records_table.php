<?php

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
