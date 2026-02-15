<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            // 主键 ID

            $table->foreignId('animal_id')->constrained('animals');
            // 外键，关联哪一只动物
            // 例如：动物 ID = 1 的健康记录全部属于同一只动物

            $table->enum('status', ['健康', '异常']);
            // 健康状态（两个单选项）：
            // 健康：正常
            // 异常：例如有外伤、发烧等

            $table->string('note', 255)->nullable();
            // 备注，可选填写，例如：
            // “左后腿轻微跛行”“吃得少”“精神正常”

            $table->foreignId('created_by')->constrained('users');
            // 记录是谁录入的（一般是饲养员）

            $table->timestamps();
            // created_at：记录创建时间
            // updated_at：记录被修改的时间
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
        // 回滚时删除 health_records 表
    }
};
