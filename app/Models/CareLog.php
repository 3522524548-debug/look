<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id', 
        'care_date',      // 对应数据库 care_date
        'type', 
        'notes',
        'weight', 
        // 'height',      // ⚠️ 如果数据库没加这个字段，建议先注释掉或删掉
        'temperature', 
        'next_visit_at',
        'created_by',     // 对应数据库 created_by
    ];

    protected $casts = [
        'care_date'     => 'date',
        'next_visit_at' => 'datetime',
    ];

    // 关联：创建人
    public function creator()
    {
        // 第二个参数 'created_by' 告诉 Laravel 去找 created_by 列，而不是 user_id
        return $this->belongsTo(User::class, 'created_by');
    }

    // 关联：所属动物
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
