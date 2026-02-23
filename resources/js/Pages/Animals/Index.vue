<!--
  Animals/Index.vue - 动物管理列表页（后台）
  
  功能说明：
  - 显示动物列表表格，支持搜索、状态筛选、物种筛选
  - 支持新增/编辑/删除动物（弹窗表单）
  - 支持上传动物照片
  - 提供服务快捷链接（文件档案、护理记录、动物详情）
  - 管理员可导出 Excel
  - 分页展示（每页10条）
  
  后端数据: animals(分页), filters, speciesList
  路由: GET /animals
-->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // 已登录用户布局
import { Head, Link, useForm, router } from '@inertiajs/vue3';      // Inertia 组件
import { ref, watch, computed } from 'vue';                          // Vue 响应式 API

/** 接收后端传递的页面属性 */
const props = defineProps({
    animals: Object,      // 动物分页数据（包含 data, links, meta 等）
    filters: Object,      // 当前筛选条件（用于回填表单）
    speciesList: Array,   // 可选物种列表（下拉框选项）
})

// 分页数据（computed 确保筛选后自动更新）
const items = computed(() => props.animals.data ?? [])

// 搜索筛选
const search = ref(props.filters?.search ?? '');
const statusFilter = ref(props.filters?.status ?? '');
const speciesFilter = ref(props.filters?.species ?? '');

let searchTimeout = null;
const doSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('animals.index'), {
            search: search.value || undefined,
            status: statusFilter.value || undefined,
            species: speciesFilter.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
};

watch([search, statusFilter, speciesFilter], doSearch);

// 弹窗状态控制
const isEditModalOpen = ref(false); 
const isEditMode = ref(false); 

// 表单定义（新增 photo 字段）
const form = useForm({
    id: null, 
    name: '',
    species: '',
    description: '', 
    age: 0,           
    review_status: 'pending', 
    visibility: 1,
    photo: null,
});

// 图片预览
const photoPreview = ref(null);

const handlePhotoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('图片最大 2MB');
            e.target.value = '';
            return;
        }
        form.photo = file;
        photoPreview.value = URL.createObjectURL(file);
    }
};

// 1. 删除动物
const deleteAnimal = (id) => {
    if (confirm('确定要删除这只动物吗？\n注意：相关的档案和护理记录也会被删除！')) {
        router.delete(route('animals.destroy', id), {
            preserveScroll: true,
        })
    }
}

// 2. 打开新增弹窗
const openCreateModal = () => {
    isEditMode.value = false 
    form.reset() 
    form.review_status = 'pending'
    form.visibility = 1
    photoPreview.value = null
    isEditModalOpen.value = true
}

// 3. 打开编辑弹窗
const openEditModal = (animal) => {
    isEditMode.value = true 
    form.id = animal.id
    form.name = animal.name
    form.species = animal.species
    form.description = animal.description ?? ''
    form.age = animal.age ?? 0
    form.review_status = animal.review_status ?? 'pending'
    form.visibility = animal.visibility == 'public' || animal.visibility === true || animal.visibility == 1 ? 1 : 0
    form.photo = null
    photoPreview.value = animal.photo_path ? `/storage/${animal.photo_path}` : null
    isEditModalOpen.value = true
}

// 4. 提交表单 (新增或编辑)
const submitForm = () => {
    if (isEditMode.value) {
        // Inertia 的 form.post 配合 _method 字段来模拟 PUT 请求（支持文件上传）
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('animals.update', form.id), {
            forceFormData: true,
            onSuccess: () => {
                isEditModalOpen.value = false;
                form.reset();
                photoPreview.value = null;
            }
        });
    } else {
        form.post(route('animals.store'), {
            forceFormData: true,
            onSuccess: () => {
                isEditModalOpen.value = false;
                form.reset();
                photoPreview.value = null;
            }
        });
    }
}
</script>

<template>
    <Head title="动物列表" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">动物管理列表</h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <Link :href="route('dashboard')" class="text-indigo-600 font-semibold flex items-center gap-1.5 hover:text-indigo-800 transition group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        返回控制面板
                    </Link>

                    <div class="flex gap-3">
                        <a :href="route('animals.export')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-sm font-medium hover:bg-emerald-100 transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            导出 Excel
                        </a>
                        <button @click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-medium hover:shadow-lg hover:shadow-indigo-200 transition-all duration-200 hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            新增动物
                        </button>
                    </div>
                </div>

                <!-- 搜索筛选栏 -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6 flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-[200px] relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input v-model="search" type="text" placeholder="搜索名称、物种、描述..." 
                            class="w-full pl-10 border-gray-200 rounded-xl shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition" />
                    </div>
                    <select v-model="statusFilter" class="border-gray-200 rounded-xl shadow-sm text-sm focus:ring-indigo-500 bg-gray-50 focus:bg-white transition pl-4 pr-9 py-2.5 min-w-[120px] appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22%236b7280%22%3E%3Cpath%20fill-rule%3D%22evenodd%22%20d%3D%22M5.293%207.293a1%201%200%20011.414%200L10%2010.586l3.293-3.293a1%201%200%20111.414%201.414l-4%204a1%201%200%2001-1.414%200l-4-4a1%201%200%20010-1.414z%22%20clip-rule%3D%22evenodd%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.5rem_center] bg-no-repeat">
                        <option value="">全部状态</option>
                        <option value="pending">⏳ 待审核</option>
                        <option value="approved">✅ 已通过</option>
                        <option value="rejected">❌ 已驳回</option>
                        <option value="adopted">🏠 已领养</option>
                    </select>
                    <select v-model="speciesFilter" class="border-gray-200 rounded-xl shadow-sm text-sm focus:ring-indigo-500 bg-gray-50 focus:bg-white transition pl-4 pr-9 py-2.5 min-w-[120px] appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22%236b7280%22%3E%3Cpath%20fill-rule%3D%22evenodd%22%20d%3D%22M5.293%207.293a1%201%200%20011.414%200L10%2010.586l3.293-3.293a1%201%200%20111.414%201.414l-4%204a1%201%200%2001-1.414%200l-4-4a1%201%200%20010-1.414z%22%20clip-rule%3D%22evenodd%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.5rem_center] bg-no-repeat">
                        <option value="">全部物种</option>
                        <option v-for="s in speciesList" :key="s" :value="s">{{ s }}</option>
                    </select>
                    <button v-if="search || statusFilter || speciesFilter" 
                        @click="search = ''; statusFilter = ''; speciesFilter = ''" 
                        class="text-sm text-gray-400 hover:text-red-500 transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        清除
                    </button>
                </div>

                <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100">
                    <table class="w-full border-collapse text-left text-sm">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100/50 border-b border-gray-100">
                            <tr>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider w-16">ID</th>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider w-16">照片</th>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">名称/物种</th>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider w-24">年龄</th>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider w-20 text-center">审核</th>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider w-20 text-center">可见</th>
                                <th class="p-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="animal in items" :key="animal.id" class="hover:bg-indigo-50/30 transition-colors duration-150">
                                <td class="p-4 text-gray-500 font-mono text-xs">#{{ animal.id }}</td>
                                <td class="p-4">
                                    <img v-if="animal.photo_path" :src="'/storage/' + animal.photo_path" class="w-11 h-11 rounded-xl object-cover ring-2 ring-gray-100" />
                                    <span v-else class="w-11 h-11 rounded-xl bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center text-lg border border-gray-100">
                                        {{ animal.species === '猫' ? '🐱' : '🐶' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-gray-800">{{ animal.name ?? '未命名' }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ animal.species }}</div>
                                </td>
                                <td class="p-4 text-gray-600">{{ animal.age ? `${animal.age} 岁` : '—' }}</td>
                                <td class="p-4 text-center">
                                    <span v-if="animal.review_status === 'approved'" class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 rounded-lg text-xs font-medium">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> 通过
                                    </span>
                                    <span v-else-if="animal.review_status === 'rejected'" class="inline-flex items-center gap-1 px-2 py-1 bg-red-50 text-red-700 rounded-lg text-xs font-medium">
                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> 驳回
                                    </span>
                                    <span v-else-if="animal.review_status === 'adopted'" class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-medium">
                                        <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></span> 已领养
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-50 text-yellow-700 rounded-lg text-xs font-medium">
                                        <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full animate-pulse"></span> 待审
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span v-if="animal.visibility == 1 || animal.visibility == 'public'" class="text-green-500">🌎</span>
                                    <span v-else class="text-gray-300">🔒</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex gap-1.5">
                                        <Link :href="route('files.index', animal.id)" class="px-2.5 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-medium hover:bg-indigo-100 transition">档案</Link>
                                        <Link :href="route('carelogs.index', animal.id)" class="px-2.5 py-1.5 bg-purple-50 text-purple-600 rounded-lg text-xs font-medium hover:bg-purple-100 transition">记录</Link>
                                        <button @click="openEditModal(animal)" class="px-2.5 py-1.5 bg-amber-50 text-amber-600 rounded-lg text-xs font-medium hover:bg-amber-100 transition">编辑</button>
                                        <button @click="deleteAnimal(animal.id)" class="px-2.5 py-1.5 bg-red-50 text-red-500 rounded-lg text-xs font-medium hover:bg-red-100 transition">删除</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="items.length === 0">
                                <td colspan="7" class="p-16 text-center">
                                    <div class="text-4xl mb-3">🐾</div>
                                    <div class="text-gray-400 font-medium">暂无动物数据</div>
                                    <div class="text-gray-300 text-sm mt-1">点击"新增动物"添加第一只动物</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="px-6 py-4 border-t border-gray-50 bg-gray-50/50 flex justify-center gap-1.5">
                        <Link v-for="(link, k) in props.animals.links" :key="k" :href="link.url ?? '#'" 
                            class="px-3 py-1.5 rounded-lg text-sm transition-all duration-200"
                            :class="{ 'bg-indigo-600 text-white shadow-sm': link.active, 'text-gray-400 cursor-not-allowed': !link.url, 'text-gray-600 hover:bg-gray-100': link.url && !link.active }"
                            v-html="link.label.replace('Previous', '上一页').replace('Next', '下一页')" />
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isEditModalOpen" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="isEditModalOpen = false">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all">
                <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>{{ isEditMode ? '✏️' : '➕' }}</span>
                        {{ isEditMode ? '修改动物资料' : '录入新动物' }}
                    </h3>
                    <button @click="isEditModalOpen = false" class="text-white/60 hover:text-white text-2xl transition">&times;</button>
                </div>
                
                <form @submit.prevent="submitForm" class="p-6 space-y-5">
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-gray-700">名称</label>
                            <input v-model="form.name" type="text" class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 bg-gray-50 focus:bg-white transition" placeholder="例：旺财" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-gray-700">物种</label>
                            <input v-model="form.species" type="text" class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 bg-gray-50 focus:bg-white transition" placeholder="例：拉布拉多" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-gray-700">年龄</label>
                            <input v-model="form.age" type="number" class="w-full border-gray-200 rounded-xl shadow-sm bg-gray-50 focus:bg-white transition" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-gray-700">审核状态</label>
                            <select v-model="form.review_status" class="w-full border-gray-200 rounded-xl shadow-sm bg-gray-50 focus:bg-white transition">
                                <option value="pending">待审核</option>
                                <option value="approved">已通过</option>
                                <option value="rejected">已驳回</option>
                                <option value="adopted">已领养</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700">描述 / 特征</label>
                        <textarea v-model="form.description" rows="3" class="w-full border-gray-200 rounded-xl shadow-sm bg-gray-50 focus:bg-white transition" placeholder="描述一下动物的情况..."></textarea>
                    </div>

                    <!-- 图片上传 -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">动物照片</label>
                        <div class="flex items-center gap-4">
                            <div v-if="photoPreview" class="w-20 h-20 rounded-xl overflow-hidden ring-2 ring-indigo-200 shadow-sm">
                                <img :src="photoPreview" class="w-full h-full object-cover" />
                            </div>
                            <div v-else class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center text-gray-300 text-2xl bg-gray-50">
                                📷
                            </div>
                            <div>
                                <input type="file" accept="image/*" @change="handlePhotoChange" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 file:font-medium file:cursor-pointer" />
                                <p class="text-xs text-gray-400 mt-1">支持 jpg/png，最大 2MB</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 py-2">
                        <input type="checkbox" id="vis" v-model="form.visibility" :true-value="1" :false-value="0" class="rounded-md text-indigo-600 border-gray-300" />
                        <label for="vis" class="text-sm font-medium cursor-pointer text-gray-700">在公众领养页面公开展示</label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 mt-4">
                        <button type="button" @click="isEditModalOpen = false" class="px-5 py-2.5 text-sm text-gray-500 hover:bg-gray-100 rounded-xl transition font-medium">取消</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 text-sm bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:shadow-lg hover:shadow-indigo-200 transition-all duration-200 disabled:opacity-50 font-medium">
                            {{ form.processing ? '正在提交...' : (isEditMode ? '保存更新' : '确认录入') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>