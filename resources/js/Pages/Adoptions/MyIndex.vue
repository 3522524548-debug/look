<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    applications: Array
});
</script>

<template>
    <Head title="我的领养申请" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">我的领养申请记录</h2>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                     <Link :href="route('adopt.index')" class="text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1.5 group transition">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        继续浏览其他动物
                    </Link>
                </div>

                <div v-if="applications.length === 0" class="text-center py-20 bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-5xl mb-4">📋</div>
                    <h3 class="text-lg font-bold text-gray-800">还没有领养申请记录</h3>
                    <p class="mt-2 text-gray-400">前往领养中心挑选你心仪的毛茸茸伙伴吧！</p>
                    <Link :href="route('adopt.index')" class="mt-6 inline-block px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium text-sm hover:shadow-lg transition">
                        去看看 →
                    </Link>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="app in applications" :key="app.id" 
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">{{ app.animal?.species === '猫' ? '🐱' : '🐶' }}</span>
                                <div>
                                    <h3 class="font-bold text-lg text-gray-800">
                                        {{ app.animal ? app.animal.name : '未知动物' }}
                                    </h3>
                                    <span class="text-xs text-gray-400">{{ app.animal ? app.animal.species : '-' }}</span>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ new Date(app.created_at).toLocaleString() }}
                            </div>
                            <p class="text-sm text-gray-400 mt-1.5 truncate max-w-md">{{ app.apply_reason }}</p>
                        </div>

                        <div class="flex flex-col items-end gap-2">
                            <span v-if="app.status === 'approved'" class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-50 text-green-700 rounded-xl font-bold text-sm border border-green-100">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span> 审核通过
                            </span>
                            <span v-else-if="app.status === 'rejected'" class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-50 text-red-700 rounded-xl font-bold text-sm border border-red-100">
                                <span class="w-2 h-2 bg-red-500 rounded-full"></span> 已驳回
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 text-amber-700 rounded-xl font-bold text-sm border border-amber-100">
                                <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span> 审核中
                            </span>
                            
                            <p v-if="app.status === 'approved'" class="text-xs text-green-600 text-right leading-relaxed">
                                请等待工作人员联系您<br>办理后续手续
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>