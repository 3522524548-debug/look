<?php

/**
 * 应用服务提供者 (AppServiceProvider)
 *
 * Laravel 应用的核心服务提供者，用于注册和启动应用级服务。
 * register(): 注册服务容器绑定
 * boot(): 应用启动时的初始化操作
 */

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 注册应用服务
     * 在此绑定接口与实现、注册单例等
     */
    public function register(): void
    {
        //
    }

    /**
     * 启动应用服务
     * 所有服务提供者注册完成后执行
     */
    public function boot(): void
    {
        // Vite 预加载：并发数为3，加速前端资源加载
        Vite::prefetch(concurrency: 3);
    }
}
