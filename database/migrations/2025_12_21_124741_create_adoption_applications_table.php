<?php

/**
 * 数据库迁移：创建 adoption_applications（领养申请）表
 *
 * 存储用户提交的领养申请信息，每条申请关联一个用户和一只动物。
 * 申请状态流转：pending（待审核）→ approved（通过）/ rejected（驳回）→ completed（完成领养）
 * 删除用户或动物时级联删除相关申请。
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 执行迁移 —— 创建 adoption_applications 表
     */
    public function up(): void
    {
        if (Schema::hasTable('adoption_applications')) {
            return; // 防止重复创建：表已存在则跳过
        }
        Schema::create('adoption_applications', function (Blueprint $table) {
            $table->id();                                                    // 自增主键
            $table->foreignId('user_id')->constrained()->onDelete('cascade');  // 申请人（关联 users 表，级联删除）
            $table->foreignId('animal_id')->constrained()->onDelete('cascade');// 申请领养的动物（关联 animals 表）
            $table->text('apply_reason');                                     // 领养理由（用户填写）
            $table->string('contact_phone');                                  // 联系电话
            $table->string('address');                                        // 联系地址
            $table->string('status')->default('pending');                     // 申请状态：pending/approved/rejected/completed
            $table->text('audit_remark')->nullable();                         // 管理员审核备注（可选）
            $table->timestamps();                                             // created_at / updated_at
        });
    }

    /**
     * 回滚迁移 —— 删除 adoption_applications 表
     */
    public function down(): void
    {
        Schema::dropIfExists('adoption_applications');
    }
};