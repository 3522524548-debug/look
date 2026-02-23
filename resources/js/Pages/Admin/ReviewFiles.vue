<!--
  Admin/ReviewFiles.vue - 管理员文件审核页
  
  功能说明：
  - 显示所有待审核的文件档案列表
  - 支持按关键字搜索和文件类型筛选
  - 提供审核通过/驳回操作，可附加审核意见
  - 支持在线预览 PDF 文件
  - 审核后通过 Toast 提示结果
  
  后端数据: files
  路由: GET /admin/files/pending
-->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // 已登录用户布局
import { Head, Link } from '@inertiajs/vue3';                        // Inertia 组件
import { ref, computed } from 'vue';                                 // Vue 响应式 API
import axios from 'axios';                                           // HTTP 请求库

/** 接收后端传递的待审核文件列表 */
const props = defineProps({
  files: {
    type: Array,
    default: () => [],
  },
});

// 拷贝一份到本地，后面审核时好更新界面
const items = ref(props.files ?? []);

// Toast 通知
const toast = ref({ show: false, message: '', type: 'success' });
const showToast = (message, type = 'success') => {
    toast.value = { show: true, message, type };
    setTimeout(() => { toast.value.show = false; }, 3000);
};

// 筛选
const keyword = ref('');
const filteredItems = computed(() => {
    const kw = keyword.value.trim().toLowerCase();
    if (!kw) return items.value;
    return items.value.filter(f => {
        const name = (f.original_name ?? '').toLowerCase();
        const uploaderName = (f.uploader?.name ?? '').toLowerCase();
        const animalName = (f.animal?.name ?? '').toLowerCase();
        return name.includes(kw) || uploaderName.includes(kw) || animalName.includes(kw);
    });
});

// 发起审核请求
const reviewFile = async (fileId, action) => {
  if (!['approve', 'reject'].includes(action)) return;

  const text = action === 'approve' ? '通过' : '驳回';
  if (!confirm(`确认要${text}这条档案吗？`)) return;

  try {
    const response = await axios.post(`/files/${fileId}/review`, { action });

    const idx = items.value.findIndex(f => f.id === fileId);
    if (idx !== -1) {
      items.value[idx].review_status = response.data.status || (action === 'approve' ? 'approved' : 'rejected');
    }

    showToast(`审核${text}成功`);
  } catch (e) {
    console.error(e);
    showToast(`审核${text}失败，请稍后再试`, 'error');
  }
};

// 类型徽章颜色
const typeBadgeClass = (type) => {
    const map = {
        '检疫报告': 'bg-orange-50 text-orange-700 border-orange-100',
        '疫苗证明': 'bg-blue-50 text-blue-700 border-blue-100',
        '体检报告': 'bg-green-50 text-green-700 border-green-100',
    };
    return map[type] || 'bg-gray-50 text-gray-700 border-gray-100';
};
</script>

<template>
  <Head title="审核档案" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
        <span class="text-2xl">📑</span> 待审核档案
      </h2>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Toast 通知 -->
        <Transition
          enter-active-class="transition ease-out duration-300"
          enter-from-class="opacity-0 translate-y-[-10px]"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-200"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 translate-y-[-10px]"
        >
          <div v-if="toast.show" class="mb-4 flex justify-center">
            <div :class="[
              'inline-flex items-center gap-2 px-5 py-3 rounded-xl shadow-lg text-sm font-medium',
              toast.type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'
            ]">
              <span>{{ toast.type === 'success' ? '✅' : '❌' }}</span>
              {{ toast.message }}
            </div>
          </div>
        </Transition>

        <!-- 顶部提示 + 搜索 -->
        <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
          <p class="text-sm text-gray-500">
            共 <span class="font-bold text-indigo-600">{{ items.length }}</span> 条待审核档案
          </p>
          <div class="flex items-center gap-2">
            <div class="relative">
              <input
                v-model="keyword"
                type="text"
                placeholder="搜索文件名/上传人/动物..."
                class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-300 w-64 bg-white"
              />
              <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- 表格 -->
        <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100">
          <table class="w-full text-left border-collapse text-sm">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100/50 border-b border-gray-100">
              <tr>
                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">ID</th>
                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">关联动物</th>
                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">类型</th>
                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">文件名</th>
                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">大小</th>
                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">上传人</th>
                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">上传时间</th>
                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider text-center">状态</th>
                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">操作</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-50">
              <tr v-for="file in filteredItems" :key="file.id" class="hover:bg-indigo-50/30 transition-colors duration-150">
                <td class="p-4 text-gray-500 font-mono text-xs">#{{ file.id }}</td>
                <td class="p-4">
                  <span v-if="file.animal" class="font-medium text-gray-800">
                    {{ file.animal.name }}
                  </span>
                  <span v-else class="text-gray-400 text-xs">ID: {{ file.animal_id }}</span>
                </td>
                <td class="p-4">
                  <span :class="['inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border', typeBadgeClass(file.type)]">
                    {{ file.type }}
                  </span>
                </td>
                <td class="p-4">
                  <a
                    :href="`/storage/${file.path}`"
                    target="_blank"
                    class="text-indigo-600 hover:text-indigo-800 font-medium hover:underline max-w-[200px] truncate block"
                    :title="file.original_name"
                  >
                    {{ file.original_name || '查看PDF' }}
                  </a>
                </td>
                <td class="p-4 text-gray-500 text-xs">{{ file.size_kb }} KB</td>
                <td class="p-4">
                  <div class="flex items-center gap-2" v-if="file.uploader">
                    <span class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 text-white text-[10px] flex items-center justify-center font-bold">
                      {{ file.uploader.name?.charAt(0)?.toUpperCase() }}
                    </span>
                    <span class="text-gray-700 text-sm">{{ file.uploader.name }}</span>
                  </div>
                  <span v-else-if="file.uploaded_by" class="text-gray-400 text-xs">用户 #{{ file.uploaded_by }}</span>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="p-4 text-gray-500 text-xs">
                  {{ file.created_at ? new Date(file.created_at).toLocaleDateString('zh-CN') : '—' }}
                </td>
                <td class="p-4 text-center">
                  <span v-if="file.review_status === 'approved'" class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-700 rounded-lg text-xs font-medium border border-green-100">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> 已通过
                  </span>
                  <span v-else-if="file.review_status === 'rejected'" class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-xs font-medium border border-red-100">
                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> 已驳回
                  </span>
                  <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-medium border border-amber-100">
                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span> 待审核
                  </span>
                </td>
                <td class="p-4">
                  <div v-if="file.review_status === 'pending'" class="flex gap-2">
                    <button
                      @click="reviewFile(file.id, 'approve')"
                      class="px-3.5 py-1.5 bg-green-50 text-green-700 border border-green-200 rounded-lg text-xs font-medium hover:bg-green-100 transition"
                    >
                      ✓ 通过
                    </button>
                    <button
                      @click="reviewFile(file.id, 'reject')"
                      class="px-3.5 py-1.5 bg-red-50 text-red-600 border border-red-200 rounded-lg text-xs font-medium hover:bg-red-100 transition"
                    >
                      ✕ 驳回
                    </button>
                  </div>
                  <span v-else class="text-xs text-gray-400 font-medium">已处理</span>
                </td>
              </tr>

              <tr v-if="filteredItems.length === 0">
                <td colspan="9" class="p-16 text-center">
                  <div class="text-4xl mb-3">📭</div>
                  <div class="text-gray-400 font-medium">当前没有需要审核的档案</div>
                  <div class="text-gray-300 text-sm mt-1">所有档案已审核完毕</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
