<?php

/**
 * Inertia 请求处理中间件 (HandleInertiaRequests)
 *
 * 负责处理 Inertia.js 服务端渲染配置：
 * 1. 设置根模板（Blade 视图）
 * 2. 管理前端资源版本（用于缓存失效）
 * 3. 定义所有页面共享的数据（如用户信息、Flash消息）
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * 首次访问时加载的根 Blade 模板
     * 对应 resources/views/app.blade.php
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * 获取当前前端资源版本号
     * Inertia 通过版本号判断是否需要刷新页面
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * 定义所有 Inertia 页面默认共享的数据
     *
     * 这些数据在每个 Vue 页面中都可以通过 $page.props 访问：
     * - auth.user: 当前登录用户信息（未登录则为 null）
     * - flash.success: 成功提示消息（用于 Toast 通知）
     * - flash.error: 错误提示消息（用于 Toast 通知）
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(), // 当前登录用户（包含 is_admin 等字段）
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'), // 成功 Flash 消息
                'error'   => fn () => $request->session()->get('error'),   // 错误 Flash 消息
            ],
        ];
    }
}
