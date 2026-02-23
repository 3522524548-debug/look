<?php

/**
 * 用户模型 (User Model)
 *
 * 代表系统中的注册用户，继承 Authenticatable 实现身份认证功能。
 * 用户分为普通用户和管理员两种角色，管理员可以审核文件和领养申请。
 *
 * 数据库表: users
 * 主要字段: name, email, password, is_admin, role_status
 */

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // 如需邮箱验证可取消注释
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /**
     * 判断当前用户是否为管理员
     *
     * 兼容两种管理员标识方式：
     * 1. is_admin 字段为 true
     * 2. role_status 字段值为 1
     * 任一条件满足即视为管理员
     *
     * @return bool 是否为管理员
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin || (int) $this->role_status === 1;
    }

    /** @use HasFactory<\Database\Factories\UserFactory> 使用模型工厂（用于测试/填充数据） */
    use HasFactory, Notifiable; // Notifiable: 支持发送通知（如领养状态变更通知）

    /**
     * 允许批量赋值的字段白名单
     * 只有这里列出的字段才能通过 create() / fill() 等方法批量写入
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',      // 用户姓名
        'email',     // 邮箱地址（用于登录）
        'password',  // 密码（自动哈希）
    ];

    /**
     * 序列化时隐藏的字段
     * 当模型转为 JSON/数组时，这些字段不会暴露给前端
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',       // 密码不返回前端
        'remember_token', // 记住我令牌不返回前端
    ];

    /**
     * 定义字段类型转换规则
     * Laravel 读取数据库值时会自动转换为指定的 PHP 类型
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // 邮箱验证时间 → Carbon 日期对象
            'password' => 'hashed',            // 密码写入时自动哈希加密
        ];
    }
}
