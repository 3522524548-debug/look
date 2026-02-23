<?php

/**
 * 登录请求验证 (LoginRequest)
 *
 * 处理用户登录请求的验证和认证逻辑：
 * - 验证邮箱和密码格式
 * - 尝试身份认证
 * - 登录频率限制（防止暴力破解）
 * 由 Laravel Breeze 脚手架自动生成。
 */

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * 判断用户是否有权发起此请求
     * 登录请求对所有人开放，无需授权
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 定义登录表单验证规则
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],     // 邮箱（必填）
            'password' => ['required', 'string'],            // 密码（必填）
        ];
    }

    /**
     * 尝试验证用户录入的凭证
     *
     * 先检查是否被限流，再尝试登录。
     * 登录失败则增加失败计数，成功则清除计数。
     *
     * @throws \Illuminate\Validation\ValidationException 登录失败时抛出
     */
    public function authenticate(): void
    {
        // 检查是否被限流
        $this->ensureIsNotRateLimited();

        // 尝试登录，remember 参数控制“记住我”功能
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // 登录失败，增加失败计数
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'), // 返回登录失败错误消息
            ]);
        }

        // 登录成功，清除失败计数
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * 确保登录请求未被频率限制
     *
     * 每分钟最多允许5次失败尝试，超过则锁定一段时间
     *
     * @throws \Illuminate\Validation\ValidationException 被限流时抛出
     */
    public function ensureIsNotRateLimited(): void
    {
        // 检查失败次数是否超过限5次
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        // 触发锁定事件
        event(new Lockout($this));

        // 计算剩余等待时间
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * 生成频率限制的唯一标识 key
     * 基于邮箱+IP地址生成，确保每个用户+IP组合独立计数
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
