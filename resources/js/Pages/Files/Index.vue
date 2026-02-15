<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// 接收后端传来的数据
const props = defineProps({
    animal: Object,  // 当前这只动物的信息
    files: Array,    // 这只动物关联的档案列表
    isAdmin: Boolean // 是否是管理员
});

// 删除文件的方法
const deleteFile = (id) => {
    if (confirm('确定要永久删除这份档案吗？')) {
        router.delete(route('files.destroy', id), {
            preserveScroll: true,
        });
    }
};

// 格式化日期
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('zh-CN');
};
</script>

<template>
    <Head :title="`${animal.name} 的档案`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                健康档案管理
            </h2>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- 顶部动物信息 + 操作 -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-lg p-6 mb-8 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 -ml-6 -mb-6 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl"></div>
                    <div class="relative z-10 flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold">{{ animal.name }} 的健康档案</h2>
                                <p class="text-indigo-100 text-sm mt-0.5">{{ animal.species }} · 共 {{ files.length }} 份档案</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link 
                                v-if="isAdmin"
                                :href="route('upload.page', { animal_id: animal.id })" 
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white text-indigo-600 hover:bg-indigo-50 rounded-lg text-sm font-bold shadow-sm transition"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                上传档案
                            </Link>
                            <Link :href="route('animals.index')" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur rounded-lg text-sm font-medium transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                返回列表
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- 档案卡片网格 -->
                <div v-if="files.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="file in files" :key="file.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 p-6 flex flex-col group">
                        
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-gradient-to-br from-rose-50 to-orange-50 text-rose-500 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span 
                                class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-lg font-semibold"
                                :class="{
                                    'bg-green-50 text-green-700': file.review_status === 'approved',
                                    'bg-amber-50 text-amber-700': file.review_status === 'pending',
                                    'bg-rose-50 text-rose-700': file.review_status === 'rejected'
                                }"
                            >
                                <span class="w-1.5 h-1.5 rounded-full" :class="{
                                    'bg-green-500': file.review_status === 'approved',
                                    'bg-amber-500 animate-pulse': file.review_status === 'pending',
                                    'bg-rose-500': file.review_status === 'rejected'
                                }"></span>
                                {{ file.review_status === 'approved' ? '已通过' : (file.review_status === 'pending' ? '待审核' : '已驳回') }}
                            </span>
                        </div>

                        <div class="mb-4 flex-1">
                            <h3 class="font-bold text-gray-800 text-base truncate group-hover:text-indigo-600 transition" :title="file.original_name">
                                {{ file.original_name }}
                            </h3>
                            <p class="text-sm text-gray-400 mt-1.5 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ formatDate(file.created_at) }}
                            </p>
                            <div v-if="file.note" class="mt-3 text-sm text-gray-600 bg-gray-50 p-3 rounded-xl border border-gray-100">
                                {{ file.note }}
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center gap-2">
                            <a 
                                :href="`/storage/${file.file_path}`" 
                                target="_blank"
                                class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50/50 transition"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                预览 PDF
                            </a>

                            <button 
                                v-if="isAdmin" 
                                @click="deleteFile(file.id)"
                                class="p-2.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition"
                                title="删除文件"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 空状态 -->
                <div v-else class="text-center py-20 bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-gray-200 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">暂无档案</h3>
                    <p class="mt-1.5 text-gray-400 text-sm">这只动物还没有上传任何 PDF 资料</p>
                    <Link 
                        v-if="isAdmin"
                        :href="route('upload.page', { animal_id: animal.id })" 
                        class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-medium shadow-sm hover:shadow-md transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        上传第一份档案
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>