<!--
  GuestLayout.vue - 访客/未登录用户布局
  
  功能说明：
  - 登录、注册、忘记密码等页面的统一布局
  - 居中展示 Logo 和内容卡片
  - 由 Laravel Breeze 脚手架自动生成
-->
<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue'; // 应用 Logo 组件
import { Link } from '@inertiajs/vue3';                          // Inertia 路由链接组件
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
