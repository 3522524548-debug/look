<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    use HasFactory;

    // ⭐⭐⭐ 关键点：这里是“白名单” ⭐⭐⭐
    // 如果这里没有 'created_by'，控制器里写了也没用，会被系统过滤掉！
    protected $fillable = [
        'name',
        'species',
        'age',
        'description',
        'review_status',
        'visibility',
        'photo_path',
        'created_by', // <--- 必须有这一行，否则永远报 "NOT NULL" 错误
    ];

    // 关联：一只动物可以有多个文件档案
    public function files()
    {
        return $this->hasMany(FileRecord::class);
    }

    // 关联：一只动物可以有多个护理记录
    public function careLogs()
    {
        return $this->hasMany(CareLog::class);
    }
}