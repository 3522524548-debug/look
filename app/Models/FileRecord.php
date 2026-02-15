<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Animal;
use App\Models\User;

class FileRecord extends Model
{
    use HasFactory;

    // 允许批量写入的字段（注意要包含 remark / uploaded_by 之类的）
    protected $fillable = [
        'animal_id',
        'type',
        'path',
        'original_name',
        'size_kb',
        'mime',
        'remark',
        'uploaded_by',
        'review_status',
        'reviewed_by',
        'reviewed_at',
    ];
      public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    /**
     * 上传人（关联到 users 表）
     */
    public function uploader()
    {
        // file_records.uploaded_by -> users.id
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
