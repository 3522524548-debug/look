<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
           

            $table->string('name');
            // 动物名称

            $table->string('species');
            // 动物种类

            $table->unsignedInteger('age')->nullable();
            // 年龄
            $table->text('habits')->nullable();
            // 习性描述
            $table->enum('visibility', ['public','private'])->default('public');
            // 可见性：public 公开给普通用户查看；private 仅内部可见

            $table->foreignId('created_by')->constrained('users');
            // 录入这条动物信息的用户 ID（一般是饲养员），外键指向 users 表

            $table->enum('review_status', ['pending','approved','rejected'])->default('pending');
            // 审核状态：pending待审核、approved已通过、rejected已驳回

            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            // 审核人 ID（管理员），允许为空（还没审核时为空）

            $table->timestamp('reviewed_at')->nullable();
            // 审核时间，允许为空

            $table->softDeletes();
            // 软删除字段 deleted_at，用于“假删”，数据还能找回

            $table->timestamps();
            // created_at / updated_at，记录创建和最后更新时间
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animals');
        // 回滚时直接把 animals 表删掉
    }
};
