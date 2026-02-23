<?php

/**
 * 数据库迁移：创建 file_records（档案记录）表
 *
 * 存储动物相关的 PDF 档案文件记录，如检疫证明、疫苗本、体检报告等。
 * 每条记录关联一只动物，包含文件路径、类型、审核状态等信息。
 * 注：未使用外键约束，避免 SQLite 兼容性问题。
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {   
        Schema::create('file_records', function (Blueprint $table) {
            $table->id(); // 主键

            // 先不用外键约束，单纯存一个数字，后面再加外键
            $table->unsignedBigInteger('animal_id');
            // 这个 PDF 属于哪只动物（动物 ID）

            // 这里不用 enum，改成字符串，减少 SQLite 的 CHECK 约束问题
            $table->string('type', 20)->default('其他');
            // 常用值：检疫报告 / 疫苗证明 / 其他

            $table->string('path');
            // 文件在 storage 中的路径，例如：pdfs/xxxxxx.pdf

            $table->string('original_name');
            // 上传时的原始文件名

            $table->integer('size_kb');
            // 文件大小（KB）

            $table->string('mime', 100)->nullable();
            // 文件 MIME 类型，例如 application/pdf

            // === 下面这些先保留字段，但全部改成“普通字段 + 可为空”，先不加外键限制 ===
            $table->unsignedBigInteger('uploaded_by')->nullable();
            // 谁上传的，可以先为空，将来再用

            $table->string('review_status', 20)->default('pending');
            // 审核状态：pending / approved / rejected

            $table->unsignedBigInteger('reviewed_by')->nullable();
            // 审核人，可以为空

            $table->timestamp('reviewed_at')->nullable();
            // 审核时间，可以为空

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('file_records');
        // 回滚时删除 file_records 表
    }
};
