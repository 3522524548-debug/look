<?php

/**
 * 文件档案模型 (FileRecord Model)
 *
 * 管理动物相关的文件记录，包括照片、文档等。
 * 每个文件关联一只动物，并记录上传人信息。
 * 文件需经管理员审核后才会公开展示（review_status字段控制）。
 *
 * 数据库表: file_records
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Animal;
use App\Models\User;

class FileRecord extends Model
{
    use HasFactory;

    /**
     * 允许批量赋值的字段白名单
     */
    protected $fillable = [
        'animal_id',     // 关联的动物ID
        'type',          // 文件类型（如：photo=照片, document=文档）
        'path',          // 文件在服务器上的存储路径
        'original_name', // 文件原始名称（上传时的文件名）
        'size_kb',       // 文件大小（KB）
        'mime',          // MIME 类型（如 image/jpeg, application/pdf）
        'remark',        // 备注说明
        'uploaded_by',   // 上传人ID（关联 users.id）
        'review_status', // 审核状态（pending=待审核, approved=已通过, rejected=已拒绝）
        'reviewed_by',   // 审核人ID（关联 users.id）
        'reviewed_at',   // 审核时间
    ];

    /**
     * 关联：获取该文件所属的动物
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    /**
     * 关联：获取文件的上传人
     * 通过 uploaded_by 字段关联 users 表
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
