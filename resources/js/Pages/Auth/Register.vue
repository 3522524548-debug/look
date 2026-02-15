<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="用户注册" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4">
        <div class="w-full max-w-md">
            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl text-white shadow-lg shadow-indigo-200">
                    🐾
                </div>
                <h2 class="text-2xl font-bold text-gray-900">创建新账号</h2>
                <p class="text-sm text-gray-500 mt-1">加入我们，一起守护流浪动物</p>
            </div>

            <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
                        <input
                            id="name"
                            type="text"
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="请输入您的姓名"
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">邮箱地址</label>
                        <input
                            id="email"
                            type="email"
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="请输入邮箱地址"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">密码</label>
                        <input
                            id="password"
                            type="password"
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="请设置密码（至少8位）"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">确认密码</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="请再次输入密码"
                        />
                        <InputError class="mt-1" :message="form.errors.password_confirmation" />
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-md hover:shadow-lg hover:shadow-indigo-200 transition-all duration-300 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? '正在注册...' : '注册账号' }}
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-500">
                    已有账号？
                    <Link
                        :href="route('login')"
                        class="text-indigo-600 hover:text-indigo-500 font-medium"
                    >
                        去登录
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
