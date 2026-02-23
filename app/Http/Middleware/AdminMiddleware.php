<?php

/**
 * 管理员中间件 (AdminMiddleware)
 *
 * 用于保护管理员专属路由，非管理员访问时返回 403 错误。
 * 在 bootstrap/app.php 中注册为 'admin' 别名，
 * 在路由中通过 ->middleware('admin') 使用。
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * 处理传入的请求
     *
     * 检查当前用户是否为管理员：
     * - 未登录或非管理员 → 返回 403
     * - 管理员 → 继续处理请求
     *
     * @param  Request  $request 当前 HTTP 请求
     * @param  Closure  $next    下一个中间件/控制器
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 检查是否登录且为管理员
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, '仅管理员可访问此页面');
        }

        return $next($request);
    }
}
