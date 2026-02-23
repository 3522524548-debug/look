<?php

/**
 * 数据库迁移：创建 adoption_applications 表（早期版本）
 *
 * 与 2025_12_21_124741 版本结构相同，通过 hasTable 检查避免重复创建。
 * 仅在表不存在时才会执行创建。
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('adoption_applications')) {
            return;
        }
        Schema::create('adoption_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('animal_id')->constrained()->onDelete('cascade');
            $table->text('apply_reason');
            $table->string('contact_phone');
            $table->string('address');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('audit_remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adoption_applications');
    }
};