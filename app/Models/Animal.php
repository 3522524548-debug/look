<?php

/**
 * 动物模型 (Animal Model)
 *
 * 代表系统中的一只流浪动物，存储动物的基本信息。
 * 动物可以有多个文件档案(照片/文档)和护理记录。
 * 审核状态控制动物是否在公开领养列表中显示。
 *
 * 数据库表: animals
 * 主要字段: name, species, age, description, review_status, visibility, photo_path, created_by
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    use HasFactory;

    /**
     * 允许批量赋值的字段白名单
     * 注意：created_by 必须在此列表中，否则创建动物时会因外键约束报错
     */
    protected $fillable = [
        'name',          // 动物名称
        'species',       // 物种（如：猫、狗、兔子等）
        'age',           // 年龄
        'description',   // 动物描述/介绍
        'review_status', // 审核状态（pending=待审核, approved=已通过, adopted=已领养）
        'visibility',    // 可见性（控制是否公开显示）
        'photo_path',    // 照片存储路径
        'created_by',    // 创建人ID（关联 users 表）
    ];

    /**
     * 关联：获取该动物的所有文件档案
     * 一只动物可以有多张照片、多个文档记录
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany(FileRecord::class);
    }

    /**
     * 关联：获取该动物的所有护理记录
     * 包括喂养、体检、驱虫、疫苗等护理日志
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function careLogs()
    {
        return $this->hasMany(CareLog::class);
    }
}