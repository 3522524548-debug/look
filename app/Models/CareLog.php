<?php

/**
 * 护理日志模型 (CareLog Model)
 *
 * 记录动物的日常护理信息，包括喂养、体检、驱虫、疫苗等。
 * 每条记录关联一只动物和一个创建人(护理员)。
 * 可记录体重、身高、体温等健康指标，便于追踪动物健康状况。
 *
 * 数据库表: care_logs
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareLog extends Model
{
    use HasFactory;

    /**
     * 允许批量赋值的字段白名单
     */
    protected $fillable = [
        'animal_id',     // 关联的动物ID
        'care_date',     // 护理日期
        'log_date',      // 记录日期
        'type',          // 护理类型（如：feeding=喂养, checkup=体检, deworming=驱虫, vaccine=疫苗）
        'notes',         // 护理备注/详细描述
        'weight',        // 体重(kg)
        'height',        // 身高(cm)
        'temperature',   // 体温(℃)
        'next_visit_at', // 下次复查时间
        'created_by',    // 创建人ID（对应 users.id）
        'user_id',       // 用户ID（兼容字段）
    ];

    /**
     * 字段类型转换规则
     * 数据库读取时自动转换为对应的 PHP 类型
     */
    protected $casts = [
        'care_date'     => 'date',     // 自动转为 Carbon 日期对象
        'next_visit_at' => 'datetime', // 自动转为 Carbon 日期时间对象
    ];

    /**
     * 关联：获取护理记录的创建人（护理员）
     * 通过 created_by 字段关联 users 表，而非默认的 user_id
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联：获取该护理记录所属的动物
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
