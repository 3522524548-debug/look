<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('adoption_applications')) {
            return; // 表已存在，跳过
        }
        Schema::create('adoption_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('animal_id')->constrained()->onDelete('cascade');
            $table->text('apply_reason'); // 领养理由
            $table->string('contact_phone'); // 联系电话
            $table->string('address'); // 地址
            $table->string('status')->default('pending'); // 状态：pending, approved, rejected
            $table->text('audit_remark')->nullable(); // 审核备注
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adoption_applications');
    }
};