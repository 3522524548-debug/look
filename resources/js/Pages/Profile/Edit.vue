<!--
  Profile/Edit.vue - 个人资料编辑页
  
  功能说明：
  - 包含三个子表单组件：
    1. UpdateProfileInformationForm - 更新姓名/邮箱
    2. UpdatePasswordForm - 修改密码
    3. DeleteUserForm - 删除账户
  
  路由: GET /profile
-->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';               // 已登录用户布局
import DeleteUserForm from './Partials/DeleteUserForm.vue';                         // 删除账户表单
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';                 // 修改密码表单
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'; // 更新个人信息表单
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
