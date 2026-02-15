<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 匿名迁移类，Laravel 会自动处理，不需要你管类名
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            // 添加手机号字段，允许为空，放在 email 后面

            $table->enum('role', ['user','keeper','admin'])->default('user')->after('phone');
            // 添加角色字段：普通用户 user，饲养员 keeper，管理员 admin
            // 默认值是 user，放在 phone 字段后面

            $table->enum('status', ['pending','active','disabled'])->default('active')->after('role');
            // 添加账号状态字段：
            // pending = 待审核，active = 启用，disabled = 禁用
            // 默认 active 表示账号正常启用
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone','role','status']);
            // 回滚时删除我们新增的三个字段
        });
    }
};
