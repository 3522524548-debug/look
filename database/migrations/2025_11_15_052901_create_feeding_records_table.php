<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeding_records', function (Blueprint $table) {
            $table->id();
            // 主键 ID，自增

            $table->foreignId('animal_id')->constrained('animals');
            // 关联的动物 ID，外键指向 animals 表
            // 表示这条记录是给哪一只动物的

            $table->date('date');
            // 记录日期（哪一天的喂养情况）
            // 只关注年月日，不需要具体时分秒

            $table->enum('feed', ['正常','减少','未喂']);
            // 喂食情况（下拉单选）：
            // 正常：按计划喂食
            // 减少：少量喂食（比如生病）
            // 未喂：当天没有喂食

            $table->enum('clean', ['已清洁','未清洁']);
            // 清洁情况（下拉单选）

            $table->string('note', 255)->nullable();
            // 备注信息（可选），比如“下午加餐”“有点挑食”等

            $table->foreignId('created_by')->constrained('users');
            // 录入这条记录的用户 ID（一般是饲养员）

            $table->timestamps();
            // created_at / updated_at：记录创建和最后修改时间
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeding_records');
        // 回滚时直接删除 feeding_records 表
    }
};
