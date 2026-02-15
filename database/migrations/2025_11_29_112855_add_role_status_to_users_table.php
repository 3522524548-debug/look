<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 0 = 普通用户，1 = 管理员
            $table->tinyInteger('role_status')
                  ->default(0)
                  ->after('email'); // 放在 email 后面，位置随意
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_status');
        });
    }
};
