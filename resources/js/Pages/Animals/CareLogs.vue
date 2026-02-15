<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3'; // 引入 Head
import { ref, computed } from 'vue'
import axios from 'axios'

// 接收后端传来的动物信息和护理记录
const props = defineProps({
  animal: Object,
  logs: Array // 注意：如果你后端分页了，这里可能需要改成 Object 并处理 logs.data
})

// ------------ 表单相关（新增 / 编辑） ------------

// 表单数据
const form = ref({
  care_date: new Date().toISOString().slice(0, 10),
  type: '例行检查',
  notes: '',
  weight: '',
  temperature: '',
  next_visit_at: '',
})

// 当前是否在编辑模式（null = 新增，非 null = 正在编辑的记录 ID）
const editingId = ref(null)

const submitting = ref(false)
const msg = ref('')

// 重置表单为“新增”状态
const resetForm = () => {
  editingId.value = null
  form.value = {
    care_date: new Date().toISOString().slice(0, 10),
    type: '例行检查',
    notes: '',
    weight: '',
    temperature: '',
    next_visit_at: '',
  }
}

// 点击“编辑”某条记录
// resources/js/Pages/Animals/CareLogs.vue

const startEdit = (log) => {
  editingId.value = log.id;
  
  // ⭐ 核心修复：处理日期格式
  // 如果 log.care_date 有值，用 split('T')[0] 把它截断成 2025-12-07
  // 如果没有值，则默认为今天
  let rawDate = log.care_date;
  if (rawDate && rawDate.includes('T')) {
      rawDate = rawDate.split('T')[0];
  }

  form.value = {
    care_date: rawDate ?? new Date().toISOString().slice(0, 10),
    type: log.type ?? '例行检查',
    notes: log.notes ?? '',
    weight: log.weight ?? '',
    temperature: log.temperature ?? '',
    // 下次复诊时间保持原样逻辑即可
    next_visit_at: log.next_visit_at
      ? log.next_visit_at.replace(' ', 'T').slice(0, 16)
      : '',
  };
};

// 取消编辑，恢复到新增模式
const cancelEdit = () => {
  resetForm()
}

// 提交表单（新增 / 编辑）
const submit = async () => {
  submitting.value = true
  msg.value = ''

  try {
    const payload = { ...form.value }

    if (editingId.value) {
      // ⭐ 编辑
      const url = `/carelogs/${editingId.value}?_method=PUT`
      await axios.post(url, payload)
    } else {
      // ⭐ 新增
      const url = `/animals/${props.animal.id}/carelogs`
      await axios.post(url, payload)
    }

    // 成功后刷新页面
    location.reload()
  } catch (e) {
    console.error('提交出错:', e)
    msg.value = '提交失败，请检查表单或稍后再试'
  } finally {
    submitting.value = false
  }
}

// 删除记录
const removeLog = async (id) => {
  if (!confirm('确定删除该条记录？')) return

  try {
    const url = `/carelogs/${id}?_method=DELETE`
    await axios.post(url)
    location.reload()
  } catch (e) {
    console.error('删除失败:', e)
    alert('删除失败，请稍后再试')
  }
}

// ------------ 列表筛选相关 ------------
const filterType = ref('全部')
const filterKeyword = ref('')

// 计算筛选后的护理记录
const filteredLogs = computed(() => {
  // 如果后端是分页数据，请用 props.logs.data；如果是直接数组，用 props.logs
  const list = props.logs.data ?? props.logs ?? [] 
  const kw = filterKeyword.value.trim().toLowerCase()

  return list.filter((log) => {
    const matchType = filterType.value === '全部' || log.type === filterType.value
    const typeText = (log.type ?? '').toLowerCase()
    const notes = (log.notes ?? '').toLowerCase()
    const matchKeyword = !kw || typeText.includes(kw) || notes.includes(kw)
    return matchType && matchKeyword
  })
})
</script>

<template>
  <Head :title="`${props.animal.name} - 护理记录`" />

  <AuthenticatedLayout>
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            护理记录管理
        </h2>
    </template>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- 顶部动物信息卡片 -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-lg p-6 mb-8 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                <div class="absolute bottom-0 left-0 -ml-6 -mb-6 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl"></div>
                <div class="relative z-10 flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">{{ animal.name ?? `#${animal.id}` }}</h2>
                            <p class="text-indigo-100 text-sm mt-0.5">{{ animal.species }} · 护理记录档案</p>
                        </div>
                    </div>
                    <Link :href="route('animals.index')" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur rounded-lg text-sm font-medium transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        返回列表
                    </Link>
                </div>
            </div>

            <!-- 新增/编辑表单 -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="editingId ? 'bg-amber-100 text-amber-600' : 'bg-green-100 text-green-600'">
                            <svg v-if="!editingId" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </div>
                        <h3 class="font-bold text-gray-800">
                            {{ editingId ? `编辑记录 #${editingId}` : '新增护理记录' }}
                        </h3>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">护理日期</label>
                            <input type="date" v-model="form.care_date" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">护理类型</label>
                            <select v-model="form.type" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition">
                                <option value="例行检查">例行检查</option>
                                <option value="疫苗">疫苗</option>
                                <option value="治疗">治疗</option>
                                <option value="其他">其他</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">体重 (kg)</label>
                            <input type="number" step="0.01" v-model="form.weight" placeholder="如：5.20" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">体温 (℃)</label>
                            <input type="number" step="0.1" v-model="form.temperature" placeholder="如：38.5" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">下次复诊时间</label>
                            <input type="datetime-local" v-model="form.next_visit_at" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition" />
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">备注说明</label>
                            <textarea v-model="form.notes" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition resize-none" placeholder="描述检查结果、用药方案、注意事项等…"></textarea>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center gap-3 pt-4 border-t border-gray-100">
                        <button 
                            :disabled="submitting" 
                            @click="submit" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-medium shadow-sm transition disabled:opacity-50"
                            :class="editingId ? 'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600' : 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700'"
                        >
                            <svg v-if="submitting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            {{ submitting ? '提交中...' : (editingId ? '保存修改' : '提交记录') }}
                        </button>

                        <button 
                            v-if="editingId" 
                            type="button" 
                            @click="cancelEdit" 
                            class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition"
                        >
                            取消
                        </button>

                        <span v-if="msg" class="inline-flex items-center gap-1 text-rose-600 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ msg }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 筛选栏 -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                        <select v-model="filterType" class="bg-gray-50 border border-gray-200 rounded-lg text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition">
                            <option value="全部">全部类型</option>
                            <option value="例行检查">例行检查</option>
                            <option value="疫苗">疫苗</option>
                            <option value="治疗">治疗</option>
                            <option value="其他">其他</option>
                        </select>
                    </div>
                    <div class="relative flex-1 min-w-[200px] max-w-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input v-model="filterKeyword" type="text" placeholder="搜索备注内容..." class="w-full bg-gray-50 border border-gray-200 rounded-lg text-sm py-2 pl-9 pr-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition" />
                    </div>
                    <span class="text-xs text-gray-400 ml-auto">共 {{ filteredLogs.length }} 条记录</span>
                </div>
            </div>

            <!-- 记录表格 -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">日期</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">类型</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">体征数据</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">备注</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">下次复诊</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-indigo-50/30 transition">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-800">{{ log.care_date }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold"
                                        :class="{
                                            'bg-blue-50 text-blue-700': log.type === '例行检查',
                                            'bg-amber-50 text-amber-700': log.type === '疫苗',
                                            'bg-rose-50 text-rose-700': log.type === '治疗',
                                            'bg-gray-100 text-gray-700': log.type === '其他'
                                        }">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="{
                                            'bg-blue-500': log.type === '例行检查',
                                            'bg-amber-500': log.type === '疫苗',
                                            'bg-rose-500': log.type === '治疗',
                                            'bg-gray-500': log.type === '其他'
                                        }"></span>
                                        {{ log.type }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span v-if="log.weight" class="inline-flex items-center gap-1 text-sm text-gray-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
                                            {{ log.weight }} kg
                                        </span>
                                        <span v-if="log.temperature" class="inline-flex items-center gap-1 text-sm text-gray-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            {{ log.temperature }} ℃
                                        </span>
                                        <span v-if="!log.weight && !log.temperature" class="text-sm text-gray-300">—</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 max-w-xs">
                                    <p class="text-sm text-gray-600 truncate" :title="log.notes">{{ log.notes || '—' }}</p>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span v-if="log.next_visit_at" class="inline-flex items-center gap-1 text-sm text-indigo-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        {{ log.next_visit_at }}
                                    </span>
                                    <span v-else class="text-sm text-gray-300">—</span>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <button @click="startEdit(log)" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="编辑">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <button @click="removeLog(log.id)" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="删除">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!filteredLogs || filteredLogs.length === 0">
                                <td colspan="6" class="text-center py-16">
                                    <div class="text-gray-300 mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <p class="text-gray-500 font-medium">暂无护理记录</p>
                                    <p class="text-gray-400 text-sm mt-1">请在上方表单添加第一条护理记录</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
  </AuthenticatedLayout>
</template>