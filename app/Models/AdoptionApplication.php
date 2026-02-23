<?php

/**
 * 领养申请模型 (AdoptionApplication Model)
 *
 * 记录用户提交的动物领养申请。
 * 包含申请人信息、申请理由、联系方式等。
 * 管理员可审核申请并更新状态（通过/拒绝）。
 *
 * 数据库表: adoption_applications
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdoptionApplication extends Model
{
    use HasFactory;

    /**
     * 允许批量赋值的字段白名单
     * 这些字段可通过 create() / fill() 方法批量写入
     */
    protected $fillable = [
        'user_id',       // 申请人ID（关联 users.id）
        'animal_id',     // 申请领养的动物ID（关联 animals.id）
        'status',        // 申请状态（pending=待审核, approved=已通过, rejected=已拒绝）
        'apply_reason',  // 领养理由
        'contact_phone', // 联系电话
        'address',       // 联系地址
    ];

    /**
     * 关联：获取申请人（用户）信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联：获取申请领养的动物信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}