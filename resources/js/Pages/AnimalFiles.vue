<!--
  AnimalFiles.vue - 动物文件档案管理页
  
  功能说明：
  - 显示某只动物的所有文件档案（PDF）
  - 支持上传新文件（仅PDF格式，最大5MB）
  - 支持在线预览和下载PDF
  - 管理员可删除文件和审核文件
  - 显示文件状态：待审核/已通过/已驳回
  
  后端数据: animalId, files
  路由: GET /animals/{animal}/files
-->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // 已登录用户布局
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3'; // Inertia 组件
import { ref, computed } from 'vue';                                 // Vue 响应式 API
import axios from 'axios';                                           // HTTP 请求库

/** 接收后端传递的参数 */
const props = defineProps({
  animalId: Number,       // 当前动物的ID
  files: Array,           // 文件档案列表
})

// ---------------------------------------------------------
// 1. 数据与筛选逻辑 (保留了你的逻辑)
// ---------------------------------------------------------

// 原始列表 (使用 ref 以便我们在前端增删改查时界面能实时变)
const items = ref(props.files ?? [])

// 筛选条件
const selectedType = ref('全部')
const keyword = ref('')

// 获取当前用户信息
const page = usePage()
// 判断是否管理员（与后端 User::isAdmin() 保持一致）
const isAdmin = computed(() => {
  const u = page.props.auth?.user
  return u && (!!u.is_admin || u.role_status === 1)
})

// 计算筛选结果
const filteredItems = computed(() => {
  return items.value.filter((file) => {
    // 类型筛选
    const matchType = selectedType.value === '全部' || file.type === selectedType.value
    
    // 关键字筛选
    const name = (file.original_name ?? file.name ?? '').toLowerCase()
    const remark = (file.remark ?? '').toLowerCase()
    const kw = keyword.value.trim().toLowerCase()
    const matchKeyword = !kw || name.includes(kw) || remark.includes(kw)

    return matchType && matchKeyword
  })
})

// ---------------------------------------------------------
// 2. 核心操作：上传、删除、审核
// ---------------------------------------------------------

// --- A. 上传功能 (集成弹窗) ---
const showUploadModal = ref(false);
const uploadForm = useForm({
    animal_id: props.animalId,
    type: '检疫报告', // 默认值
    file: null,
    remark: '',
});

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file && file.size > 5 * 1024 * 1024) {
        alert('文件大小不能超过 5MB');
        e.target.value = '';
        return;
    }
    uploadForm.file = file;
};

const submitUpload = () => {
    uploadForm.post(`/animals/${props.animalId}/files`, {
        onSuccess: () => {
            showUploadModal.value = false;
            uploadForm.reset();
            // 手动刷新页面数据
            router.reload({ onSuccess: () => {
                // 刷新后更新本地 items，否则列表不会变
                items.value = page.props.files; 
            }});
        },
    });
};

// --- B. 删除功能 (保留你的逻辑，稍微美化交互) ---
const deleteFile = async (id) => {
  if (!confirm('确认要删除这条档案吗？')) return

  try {
    // 使用 axios 可以在不刷新页面的情况下删除前端数据
    await axios.delete(`/files/${id}`)
    // 从本地列表中移除
    items.value = items.value.filter((item) => item.id !== id)
  } catch (e) {
    console.error(e)
    alert('删除失败，请稍后再试')
  }
}

// --- C. 审核功能 (你的核心逻辑) ---
const reviewFile = async (id, status) => {
  const msg = status === 'approved' ? '确定要【通过】这条档案吗？' : '确定要【驳回】这条档案吗？'
  if (!confirm(msg)) return

  const action = status === 'approved' ? 'approve' : 'reject'

  try {
    await axios.post(`/files/${id}/review`, { action })

    // 前端直接更新状态，无需刷新页面
    items.value = items.value.map((item) =>
      item.id === id ? { ...item, review_status: status } : item
    )
  } catch (e) {
    console.error('审核失败：', e)
    alert('操作失败，请稍后再试')
  }
}

// 辅助：获取文件链接
const getFileUrl = (path) => `/storage/${path}`;
</script>

<template>
  <Head title="档案列表" />

  <AuthenticatedLayout>
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            档案管理 (ID: {{ animalId }})
        </h2>
    </template>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                    <Link href="/animals" class="text-blue-600 hover:text-blue-800 flex items-center font-bold">
                        <span class="mr-1 text-lg">&larr;</span> 返回动物列表
                    </Link>

                    <button 
                        @click="showUploadModal = true"
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition shadow"
                    >
                        ➕ 上传新档案
                    </button>
                </div>

                <div class="mb-6 p-4 bg-gray-50 rounded border flex flex-wrap gap-4 items-center">
                    <div class="flex items-center">
                        <label class="text-sm font-bold text-gray-700 mr-2">类型筛选:</label>
                        <select v-model="selectedType" class="border-gray-300 rounded text-sm py-1 px-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="全部">全部</option>
                            <option value="检疫报告">检疫报告</option>
                            <option value="疫苗证明">疫苗证明</option>
                            <option value="体检报告">体检报告</option>
                            <option value="其他">其他</option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <label class="text-sm font-bold text-gray-700 mr-2">搜索:</label>
                        <input
                            v-model="keyword"
                            type="text"
                            placeholder="文件名或备注..."
                            class="border-gray-300 rounded text-sm py-1 px-2 w-48 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-200 text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="border p-3 text-left">ID</th>
                                <th class="border p-3 text-left">类型</th>
                                <th class="border p-3 text-left">文件名</th>
                                <th class="border p-3 text-left">大小</th>
                                <th class="border p-3 text-left">上传人</th>
                                <th class="border p-3 text-left">备注</th>
                                <th class="border p-3 text-left">状态</th>
                                <th class="border p-3 text-left">上传时间</th>
                                <th class="border p-3 text-left" style="min-width: 180px;">操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="file in filteredItems" :key="file.id" class="hover:bg-gray-50 transition">
                                <td class="border p-3">{{ file.id }}</td>
                                <td class="border p-3">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                        {{ file.type }}
                                    </span>
                                </td>
                                <td class="border p-3 font-medium text-gray-800 max-w-xs truncate" :title="file.original_name">
                                    {{ file.original_name }}
                                </td>
                                <td class="border p-3 text-gray-500">{{ file.size_kb }} KB</td>
                                
                                <td class="border p-3">
                                    {{ file.uploader ? file.uploader.name : (file.uploaded_by ? `#${file.uploaded_by}` : '-') }}
                                </td>
                                
                                <td class="border p-3 max-w-xs truncate text-gray-500" :title="file.remark">
                                    {{ file.remark || '-' }}
                                </td>

                                <td class="border p-3">
                                    <span v-if="file.review_status === 'approved'" class="text-green-600 font-bold flex items-center">
                                        ✅ 已通过
                                    </span>
                                    <span v-else-if="file.review_status === 'rejected'" class="text-red-600 font-bold flex items-center">
                                        ❌ 已驳回
                                    </span>
                                    <span v-else class="text-yellow-600 font-bold flex items-center">
                                        ⏳ 待审核
                                    </span>
                                </td>

                                <td class="border p-3 text-gray-500">
                                    {{ new Date(file.created_at).toLocaleDateString() }}
                                </td>

                                <td class="border p-3 space-y-2">
                                    <div class="flex gap-2 mb-1">
                                        <a 
                                            :href="getFileUrl(file.path)" 
                                            target="_blank"
                                            class="text-blue-600 hover:underline font-bold"
                                        >
                                            下载
                                        </a>
                                        <button @click="deleteFile(file.id)" class="text-red-500 hover:underline">
                                            删除
                                        </button>
                                    </div>

                                    <div v-if="isAdmin" class="flex gap-2 text-xs">
                                        <button 
                                            @click="reviewFile(file.id, 'approved')"
                                            class="px-2 py-1 bg-green-100 text-green-700 rounded border border-green-200 hover:bg-green-200"
                                            :class="{'opacity-50 cursor-not-allowed': file.review_status === 'approved'}"
                                            :disabled="file.review_status === 'approved'"
                                        >
                                            通过
                                        </button>
                                        <button 
                                            @click="reviewFile(file.id, 'rejected')"
                                            class="px-2 py-1 bg-red-100 text-red-700 rounded border border-red-200 hover:bg-red-200"
                                            :class="{'opacity-50 cursor-not-allowed': file.review_status === 'rejected'}"
                                            :disabled="file.review_status === 'rejected'"
                                        >
                                            驳回
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="filteredItems.length === 0">
                                <td colspan="9" class="text-center p-8 text-gray-400 bg-gray-50">
                                    没有符合条件的档案记录
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div v-if="showUploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-[500px] max-w-[90%] shadow-xl">
            <h3 class="text-lg font-bold mb-4">上传档案</h3>
            
            <form @submit.prevent="submitUpload">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">档案类型</label>
                    <select v-model="uploadForm.type" class="w-full border-gray-300 rounded shadow-sm">
                        <option value="检疫报告">检疫报告</option>
                        <option value="疫苗证明">疫苗证明</option>
                        <option value="体检报告">体检报告</option>
                        <option value="其他">其他</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">选择文件 (PDF)</label>
                    <input type="file" @change="handleFileChange" accept=".pdf" class="w-full border p-2 rounded text-sm bg-gray-50" required>
                    <div v-if="uploadForm.errors.file" class="text-red-500 text-xs mt-1">{{ uploadForm.errors.file }}</div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1">备注说明</label>
                    <textarea v-model="uploadForm.remark" rows="3" class="w-full border-gray-300 rounded shadow-sm" placeholder="请输入描述..."></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="showUploadModal = false" class="px-4 py-2 border rounded hover:bg-gray-100">取消</button>
                    <button type="submit" :disabled="uploadForm.processing" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        {{ uploadForm.processing ? '上传中...' : '确认上传' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

  </AuthenticatedLayout>
</template>