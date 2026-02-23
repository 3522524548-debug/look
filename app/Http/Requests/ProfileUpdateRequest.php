<?php

/**
 * 个人资料更新请求验证 (ProfileUpdateRequest)
 *
 * 专用于验证用户更新个人资料时提交的数据。
 * 确保姓名和邮箱符合格式要求，且邮箱不与其他用户重复。
 */

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * 定义验证规则
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], // 姓名：必填，最长255字符
            'email' => [
                'required',
                'string',
                'lowercase',                                       // 必须小写
                'email',                                            // 必须是合法邮箱格式
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id), // 邮箱唯一（排除当前用户）
            ],
        ];
    }
}
