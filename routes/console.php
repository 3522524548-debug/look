<?php

/**
 * 控制台路由文件 (console.php)
 *
 * 定义 Artisan 命令行命令。
 * 可以通过 `php artisan inspire` 运行下方的示例命令。
 */

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// 示例命令：显示一条鼓舞人心的名言
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
