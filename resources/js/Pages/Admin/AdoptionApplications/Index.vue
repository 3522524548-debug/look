<!--
  Admin/AdoptionApplications/Index.vue - 管理员领养申请审核页
  
  功能说明：
  - 显示所有领养申请的列表（分页）
  - 展示申请人、动物、状态、申请理由、联系信息
  - 提供审核操作：通过/驳回/确认交接
  - 状态徽章显示不同颜色
  
  后端数据: applications(分页)
  路由: GET /admin/adoptions
-->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // 已登录用户布局
import { Head, useForm, Link } from '@inertiajs/vue3';               // Inertia 组件

/** 接收后端传递的分页数据 */
const props = defineProps({
    applications: Object, // 领养申请分页数据（包含 data, links, meta 等）
});

/** 使用 Inertia useForm 创建审核操作表单 */
const form = useForm({
    status: '',           // 审核状态（approved/rejected/completed）
});

// 处理审核动作
const handleReview = (id, action) => {
    const statusTextMap = {
        'approved': '通过',
        'rejected': '驳回',
        'completed': '确认交接（标记已出库）',
    };
    const statusText = statusTextMap[action] || action;
    if (confirm(`确定要${statusText}这条领养申请吗？`)) {
        form.status = action;
        form.patch(route('admin.adoptions.update', id), {
            preserveScroll: true,
            onSuccess: () => alert('处理成功！'),
        });
    }
};
</script>

<template>
    <Head title="领养申请审核" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <span class="text-2xl">📋</span> 领养申请审核
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100/50 border-b border-gray-100">
                            <tr>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">申请人</th>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">申请动物</th>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">联系电话</th>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">申请理由</th>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider text-center">状态</th>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="app in applications.data" :key="app.id" class="hover:bg-indigo-50/30 transition-colors duration-150">
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 text-white text-xs flex items-center justify-center font-bold">
                                            {{ app.user?.name?.charAt(0)?.toUpperCase() }}
                                        </span>
                                        <span class="font-medium text-gray-800">{{ app.user?.name }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="font-medium text-gray-800">{{ app.animal?.name }}</span>
                                    <span class="text-xs text-gray-400 ml-1">{{ app.animal?.species }}</span>
                                </td>
                                <td class="p-4 text-gray-600">{{ app.contact_phone }}</td>
                                <td class="p-4">
                                    <p class="text-gray-600 max-w-[200px] truncate" :title="app.apply_reason">
                                        {{ app.apply_reason }}
                                    </p>
                                </td>
                                <td class="p-4 text-center">
                                    <span v-if="app.status === 'pending'" class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-medium border border-amber-100">
                                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span> 待审核
                                    </span>
                                    <span v-else-if="app.status === 'approved'" class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium border border-blue-100">
                                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span> 待交接
                                    </span>
                                    <span v-else-if="app.status === 'completed'" class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-700 rounded-lg text-xs font-medium border border-green-100">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> 已完成
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-xs font-medium border border-red-100">
                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> 已驳回
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div v-if="app.status === 'pending'" class="flex gap-2">
                                        <button @click="handleReview(app.id, 'approved')" class="px-3.5 py-1.5 bg-green-50 text-green-700 border border-green-200 rounded-lg text-xs font-medium hover:bg-green-100 transition">
                                            ✓ 通过
                                        </button>
                                        <button @click="handleReview(app.id, 'rejected')" class="px-3.5 py-1.5 bg-red-50 text-red-600 border border-red-200 rounded-lg text-xs font-medium hover:bg-red-100 transition">
                                            ✕ 驳回
                                        </button>
                                    </div>
                                    <div v-else-if="app.status === 'approved'">
                                        <button @click="handleReview(app.id, 'completed')" class="px-3.5 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-medium hover:bg-indigo-100 transition">
                                            📦 确认交接
                                        </button>
                                    </div>
                                    <span v-else-if="app.status === 'completed'" class="text-xs text-green-600 font-medium">✅ 已出库</span>
                                    <span v-else class="text-xs text-gray-400 font-medium">已处理</span>
                                </td>
                            </tr>
                            <tr v-if="applications.data.length === 0">
                                <td colspan="6" class="p-16 text-center">
                                    <div class="text-4xl mb-3">📭</div>
                                    <div class="text-gray-400 font-medium">暂无领养申请</div>
                                    <div class="text-gray-300 text-sm mt-1">等待用户提交领养申请</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 分页 -->
                <div v-if="applications.links && applications.links.length > 3" class="mt-6 flex justify-center gap-1.5">
                    <Link 
                        v-for="(link, k) in applications.links" 
                        :key="k"
                        :href="link.url ?? '#'"
                        class="px-3 py-1.5 rounded-lg text-sm transition-all duration-200"
                        :class="{ 
                            'bg-indigo-600 text-white shadow-sm': link.active, 
                            'text-gray-400 cursor-not-allowed': !link.active && !link.url, 
                            'text-gray-600 hover:bg-gray-100 bg-white border border-gray-100': !link.active && link.url 
                        }"
                    >
                        <span v-html="link.label.replace('Previous', '上一页').replace('Next', '下一页')"></span>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>