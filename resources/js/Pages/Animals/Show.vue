<!--
  Animals/Show.vue - 动物详情页
  
  功能说明：
  - 展示动物的完整信息：照片、名称、物种、年龄、状态、描述
  - 提供操作按钮：查看文件档案、护理记录、申请领养
  - 响应式设计，包含英雄图片、状态徽章、物种标签
  
  后端数据: animal
  路由: GET /animals/{animal}
-->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // 已登录用户布局
import { Head, Link, usePage } from '@inertiajs/vue3';               // Inertia 组件
import { computed } from 'vue';                                      // Vue 计算属性

/** 接收后端传递的页面属性 */
const props = defineProps({
    animal: Object,       // 动物详细信息
    auth: Object,         // 当前登录用户信息
    flash: Object,        // Flash 提示消息
});

const page = usePage();
const isAdmin = computed(() => {
    const u = page.props.auth?.user;
    return u && (!!u.is_admin || u.role_status === 1);
});

const statusText = {
    pending: '待审核',
    approved: '已通过',
    rejected: '已驳回',
    adopted: '已领养',
};

const statusClass = {
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    approved: 'bg-green-50 text-green-700 border-green-200',
    rejected: 'bg-red-50 text-red-700 border-red-200',
    adopted: 'bg-indigo-50 text-indigo-700 border-indigo-200',
};
</script>

<template>
    <Head :title="animal.name + ' - 动物详情'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <span class="text-2xl">🐾</span> 动物详情
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <!-- Flash 消息 -->
                <div v-if="flash?.success" class="mb-6 flex items-center gap-2 px-5 py-3 rounded-xl bg-green-50 text-green-700 border border-green-200 text-sm font-medium">
                    <span>✅</span> {{ flash.success }}
                </div>
                <div v-if="flash?.error" class="mb-6 flex items-center gap-2 px-5 py-3 rounded-xl bg-red-50 text-red-700 border border-red-200 text-sm font-medium">
                    <span>❌</span> {{ flash.error }}
                </div>

                <!-- 动物信息卡片 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- 头部图片区域 -->
                    <div class="relative h-64 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-400">
                        <img
                            v-if="animal.photo_path"
                            :src="`/storage/${animal.photo_path}`"
                            :alt="animal.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <span class="text-8xl opacity-50">🐾</span>
                        </div>
                        <!-- 状态徽章 -->
                        <div class="absolute top-4 right-4">
                            <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border backdrop-blur-sm', statusClass[animal.review_status] || 'bg-gray-50 text-gray-700 border-gray-200']">
                                {{ statusText[animal.review_status] || animal.review_status }}
                            </span>
                        </div>
                    </div>

                    <!-- 详细信息 -->
                    <div class="p-8">
                        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">{{ animal.name }}</h1>
                                <p class="text-gray-500 mt-1 flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-medium border border-indigo-100">
                                        {{ animal.species }}
                                    </span>
                                    <span v-if="animal.age" class="text-sm">{{ animal.age }} 岁</span>
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium border', animal.visibility === 'public' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-gray-50 text-gray-600 border-gray-200']">
                                    {{ animal.visibility === 'public' ? '🌐 公开' : '🔒 私密' }}
                                </span>
                            </div>
                        </div>

                        <!-- 描述 -->
                        <div v-if="animal.description" class="mb-8">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">描述</h3>
                            <p class="text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-4 border border-gray-100">
                                {{ animal.description }}
                            </p>
                        </div>

                        <!-- 操作按钮 -->
                        <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-100">
                            <Link
                                :href="route('files.index', animal.id)"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-xl text-sm font-medium hover:bg-blue-100 transition"
                            >
                                📂 查看档案
                            </Link>
                            <Link
                                :href="route('carelogs.index', animal.id)"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-sm font-medium hover:bg-emerald-100 transition"
                            >
                                💊 护理记录
                            </Link>
                            <Link
                                v-if="animal.review_status === 'approved' && animal.visibility === 'public'"
                                :href="route('adoptions.create', animal.id)"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-medium hover:from-indigo-700 hover:to-purple-700 transition shadow-sm"
                            >
                                💜 申请领养
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>