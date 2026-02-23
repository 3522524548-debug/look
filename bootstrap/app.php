<?php

/**
 * Laravel 应用引导配置文件
 *
 * 这是 Laravel 11 的核心引导文件，负责：
 * 1. 配置路由文件（web.php、console.php）
 * 2. 注册全局中间件和中间件别名
 * 3. 配置异常处理
 *
 * @see https://laravel.com/docs/11.x/configuration
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    // ========== 路由配置 ==========
    ->withRouting(
        web: __DIR__.'/../routes/web.php',          // Web 路由文件
        commands: __DIR__.'/../routes/console.php',  // Artisan 命令路由
        health: '/up',                               // 健康检查端点（用于运维监控）
    )
    // ========== 中间件配置 ==========
    ->withMiddleware(function (Middleware $middleware): void {
        // 向 web 中间件组追加两个中间件
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,              // Inertia.js 请求处理（共享数据、版本控制）
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class, // 预加载资源的 Link 头（提升页面加载速度）
        ]);

        // 注册中间件别名，可在路由中通过 ->middleware('admin') 使用
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class, // 管理员权限验证中间件
        ]);
    })
    // ========== 异常处理配置 ==========
    ->withExceptions(function (Exceptions $exceptions): void {
        // 可在此自定义异常处理逻辑（如自定义错误页面、日志等）
    })->create();
