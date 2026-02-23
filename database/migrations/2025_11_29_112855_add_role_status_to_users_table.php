<?php

/**
 * 数据库迁移：为 users 表添加 role_status 字段
 *
 * 增加数字型角色状态字段：0 = 普通用户，1 = 管理员。
 * 与 is_admin 字段配合使用，User::isAdmin() 会同时检查两个字段。
 */

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
