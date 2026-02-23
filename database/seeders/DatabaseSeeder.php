<?php

/**
 * 数据库填充器：主填充入口
 *
 * 运行 `php artisan db:seed` 时默认执行的填充类。
 * 创建一个测试用户用于开发环境登录。
 * 如需批量生成演示数据，请使用 DemoDataSeeder。
 */

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // 禁用模型事件，提升批量插入性能
    use WithoutModelEvents;

    /**
     * 执行数据库填充
     *
     * 创建一个默认测试用户，用于开发调试。
     * 邮箱：test@example.com，密码由 UserFactory 生成。
     */
    public function run(): void
    {
        // User::factory(10)->create(); // 可取消注释批量生成 10 个随机用户

        // 创建一个指定邮箱的测试用户
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
