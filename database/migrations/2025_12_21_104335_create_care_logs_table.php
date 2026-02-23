<?php

/**
 * 数据库迁移：创建 care_logs（护理日志）表
 *
 * 记录每只动物的日常护理信息，包括喂养、体检、驱虫、疫苗接种等。
 * 每条记录关联一只动物，包含护理日期、类型、备注、体重、体温等字段。
 * 删除动物时级联删除其护理日志（cascadeOnDelete）。
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 执行迁移 —— 创建 care_logs 表
     */
    public function up(): void {
        Schema::dropIfExists('care_logs'); // 强力重置，防止已存在报错
        Schema::create('care_logs', function (Blueprint $table) {
            $table->id();                                                          // 自增主键
            $table->foreignId('animal_id')->constrained('animals')->cascadeOnDelete(); // 关联动物（级联删除）
            $table->date('care_date')->index();                                     // 护理日期（建立索引加速查询）
            $table->string('type', 30);                                             // 护理类型：日常喂养/体检/驱虫/疫苗接种等
            $table->text('notes')->nullable();                                      // 护理备注说明
            $table->decimal('weight', 6, 2)->nullable();                            // 体重（kg），最多6位数含2位小数
            $table->decimal('temperature', 4, 1)->nullable();                       // 体温（℃），最多4位数含1位小数
            $table->timestamps();                                                   // created_at / updated_at
        });
    }

    /**
     * 回滚迁移 —— 删除 care_logs 表
     */
    public function down(): void { Schema::dropIfExists('care_logs'); }
};