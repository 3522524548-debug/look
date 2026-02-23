<?php

/**
 * 认证路由文件 (auth.php)
 *
 * 由 Laravel Breeze 脚手架自动生成，定义用户认证相关的路由：
 * - 访客路由（guest 中间件）：注册、登录、忘记密码、重置密码
 * - 已登录路由（auth 中间件）：邮箱验证、确认密码、修改密码、登出
 */

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// --- 访客路由（未登录用户） ---
Route::middleware('guest')->group(function () {
    // 注册页面
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    // 注册处理
    Route::post('register', [RegisteredUserController::class, 'store']);

    // 登录页面
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    // 登录处理
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // 忘记密码页面
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    // 发送密码重置链接
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // 重置密码页面
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    // 重置密码处理
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// --- 已登录用户路由 ---
Route::middleware('auth')->group(function () {
    // 邮箱验证提示页
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    // 邮箱验证链接处理（需签名验证 + 频率限制）
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // 重发验证邮件
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // 确认密码页面
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    // 确认密码处理
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // 修改密码
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // 登出
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
