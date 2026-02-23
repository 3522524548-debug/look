<?php

/**
 * 个人资料控制器 (ProfileController)
 *
 * 负责用户个人资料的查看、编辑和账户删除。
 * 由 Laravel Breeze 脚手架自动生成，提供基础的用户资料管理功能。
 *
 * 路由前缀: /profile
 */

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest; // 资料更新表单请求验证
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * 显示用户个人资料编辑页面
     *
     * GET /profile
     * 传递是否需要邮箱验证和当前 session 状态信息
     *
     * @param  Request $request
     * @return Response 渲染 Profile/Edit 页面
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail, // 是否需要验证邮箱
            'status' => session('status'), // session 状态消息
        ]);
    }

    /**
     * 更新用户个人资料信息
     *
     * PATCH /profile
     * 如果邮箱变更，则重置邮箱验证状态
     *
     * @param  ProfileUpdateRequest $request 经过验证的请求
     * @return RedirectResponse 跳转回编辑页
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // 填充已验证的数据
        $request->user()->fill($request->validated());

        // 如果邮箱被修改，清除邮箱验证时间戳
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * 删除用户账户
     *
     * DELETE /profile
     * 需要确认当前密码才能删除。删除后自动登出并清理会话。
     *
     * @param  Request $request
     * @return RedirectResponse 跳转回首页
     */
    public function destroy(Request $request): RedirectResponse
    {
        // 验证当前密码
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // 登出当前用户
        Auth::logout();

        // 删除用户数据
        $user->delete();

        // 清理会话并重新生成 CSRF 令牌
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
