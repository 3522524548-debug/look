<!--
  Auth/Login.vue - 用户登录页面
  
  功能说明：
  - 提供邮箱+密码登录表单
  - 支持“记住我”功能
  - 提供“忘记密码”和“注册账号”链接
  - 表单验证错误实时显示
  
  路由: GET /login
-->
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3' // Inertia 组件

/** 创建登录表单 */
const form = useForm({
  email: '',              // 邮箱
  password: '',
  remember: false,
})

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="用户登录" />

  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4">
    <div class="w-full max-w-md">
      <!-- Logo & Title -->
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl text-white shadow-lg shadow-indigo-200">
          🐾
        </div>
        <h2 class="text-2xl font-bold text-gray-900">
          流浪动物救助与领养管理系统
        </h2>
        <p class="text-sm text-gray-500 mt-1">
          请输入账号和密码登录系统
        </p>
      </div>

      <!-- 卡片 -->
      <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">
        <!-- 错误提示 -->
        <div v-if="form.errors.email || form.errors.password" class="mb-5 p-3 bg-red-50 border border-red-100 rounded-xl">
          <div class="text-sm text-red-600 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ form.errors.email || form.errors.password }}
          </div>
        </div>

        <!-- 表单 -->
        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              登录邮箱
            </label>
            <input
              v-model="form.email"
              type="email"
              required
              autofocus
              class="block w-full rounded-xl border-gray-300 shadow-sm
                     focus:border-indigo-500 focus:ring-indigo-500 transition"
              placeholder="请输入注册时使用的邮箱"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              登录密码
            </label>
            <input
              v-model="form.password"
              type="password"
              required
              autocomplete="current-password"
              class="block w-full rounded-xl border-gray-300 shadow-sm
                     focus:border-indigo-500 focus:ring-indigo-500 transition"
              placeholder="请输入密码"
            />
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center cursor-pointer">
              <input
                v-model="form.remember"
                type="checkbox"
                class="rounded border-gray-300 text-indigo-600 shadow-sm
                       focus:border-indigo-300 focus:ring focus:ring-indigo-200
                       focus:ring-opacity-50"
              />
              <span class="ml-2 text-gray-600">记住我</span>
            </label>

            <Link
              v-if="route().has('password.request')"
              :href="route('password.request')"
              class="text-indigo-600 hover:text-indigo-500 font-medium"
            >
              忘记密码？
            </Link>
          </div>

          <button
            type="submit"
            class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-md hover:shadow-lg hover:shadow-indigo-200 transition-all duration-300 disabled:opacity-50"
            :disabled="form.processing"
          >
            {{ form.processing ? '登录中...' : '登录' }}
          </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-500">
          还没有账号？
          <Link
            v-if="route().has('register')"
            :href="route('register')"
            class="text-indigo-600 hover:text-indigo-500 font-medium"
          >
            去注册
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>
