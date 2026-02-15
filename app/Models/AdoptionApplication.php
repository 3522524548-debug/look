<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdoptionApplication extends Model
{
    use HasFactory;

    // ⭐ 必须把这些字段加入白名单，否则无法保存申请
    protected $fillable = [
        'user_id', 
        'animal_id', 
        'status', 
        'apply_reason', 
        'contact_phone', 
        'address'
    ];

    // 关联用户
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 关联动物
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}