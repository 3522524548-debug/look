<!--
  AnimalsIndex.vue - 动物列表页（早期版本/备用）
  
  功能说明：
  - 动物列表的早期实现版本，后来被 Animals/Index.vue 替代
  - 支持基本的动物CRUD操作
  - 包含搜索和筛选功能
-->
<script setup>
import { defineProps, ref, reactive, computed } from 'vue' // Vue 响应式 API
import { Link, router } from '@inertiajs/vue3'              // Inertia 组件（router 用于发送请求）

const props = defineProps({
  animals: Object, 
})

const items = computed(() => props.animals.data ?? [])

// ===========================
// 👇 新增：删除功能
// ===========================
const deleteAnimal = (id) => {
  if (confirm('确定要删除这就动物吗？\n注意：相关的护理记录也会被删除！')) {
    router.delete(route('animals.destroy', id), {
      preserveScroll: true, // 删除后保持滚动条位置
    })
  }
}

// ===========================
// 👇 新增：编辑弹窗逻辑
// ===========================
const isEditModalOpen = ref(false)
const form = reactive({
  id: null,
  name: '',
  species: ''
})

// 控制弹窗是“新增”还是“编辑”
const isEditMode = ref(false)

// 点击“新增动物”按钮
const openCreateModal = () => {
    isEditMode.value = false // 设为新增模式
    form.id = null
    form.name = ''
    form.species = ''
    isEditModalOpen.value = true
}

// 点击“编辑”按钮
const openEditModal = (animal) => {
    isEditMode.value = true // 设为编辑模式
    form.id = animal.id
    form.name = animal.name
    form.species = animal.species
    isEditModalOpen.value = true
}

// 提交表单（自动判断是新增还是修改）
const submitForm = () => {
    if (isEditMode.value) {
        // 编辑模式：发 PUT 请求
        router.put(route('animals.update', form.id), form, {
            onSuccess: () => isEditModalOpen.value = false
        })
    } else {
        // 新增模式：发 POST 请求
        router.post(route('animals.store'), form, {
            onSuccess: () => isEditModalOpen.value = false
        })
    }
}
</script>

<template>
  <div style="padding: 24px;">
    <h2 style="margin-bottom: 20px;">动物列表</h2>

    <!-- 返回控制面板 -->
    <p style="margin-bottom: 12px;">
      <Link href="/dashboard" style="color:#409eff;">
        ← 返回控制面板
      </Link>
    </p>

    <div style="margin-bottom: 16px;">
        <button 
            @click="openCreateModal"
            style="padding: 10px 20px; background-color: #409eff; color: white; border: none; border-radius: 4px; cursor: pointer;">
            + 新增动物
        </button>
    </div>
    <table
      border="1"
      cellspacing="0"
      cellpadding="8"
      style="width: 100%; text-align: left;"
    >
      <thead style="background: #f5f5f5;">
        <tr>
          <th style="width: 80px;">ID</th>
          <th>名称 / 编号</th>
          <th style="width: 120px;">档案数量</th>
          <th style="width: 160px;">操作</th>
        </tr>
      </thead>

      <tbody>
        <!-- 整行可点击：跳到该动物的档案列表 -->
        <tr
          v-for="animal in items"
          :key="animal.id"
          style="cursor: pointer;"
          @click="window.location.href = `/animals/${animal.id}/files`"
        >
          <td>{{ animal.id }}</td>
          <td>
            {{ animal.name ?? `动物 #${animal.id}` }}
          </td>
          <td>
            <!-- 这里就是后端 withCount('files') 带过来的字段 -->
            {{ animal.files_count ?? 0 }}
          </td>
          <!-- 查看 PDF 档案 -->
          <td class="border px-4 py-2 flex gap-3">
              <Link
                  :href="route('files.index', animal.id)"
                  class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
                  @click.stop 
              >
                  档案
              </Link>

              <Link
                  :href="route('carelogs.index', animal.id)"
                  class="px-3 py-1 bg-purple-500 text-white rounded hover:bg-purple-600 text-sm"
                  @click.stop
              >
                  护理
              </Link>

              <button
                  @click.stop="openEditModal(animal)"
                  class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm"
              >
                  编辑
              </button>

              <button
                  @click.stop="deleteAnimal(animal.id)"
                  class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
              >
                  删除
              </button>
          </td>




            
          
        </tr>
        <tr v-if="items.length === 0">
          <td colspan="4" style="text-align:center; padding:20px;">
            暂无动物数据
          </td>
        </tr>
      </tbody>
    </table>

    <!-- 简单分页信息 -->
    <div style="margin-top: 16px;">
      <span>
        第 {{ props.animals.current_page }} / {{ props.animals.last_page }} 页，
        共 {{ props.animals.total }} 条
      </span>
    </div>
  </div>
  <div v-if="isEditModalOpen" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;">
      <div style="background: white; padding: 24px; border-radius: 8px; width: 400px;">
        <h3 style="margin-bottom: 20px; font-weight: bold; font-size: 18px;">编辑动物信息</h3>
        
        <div style="margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 6px;">名称</label>
          <input v-model="form.name" type="text" style="width: 100%; border: 1px solid #ccc; padding: 8px; rounded: 4px;">
        </div>

        <div style="margin-bottom: 24px;">
          <label style="display: block; margin-bottom: 6px;">物种</label>
          <input v-model="form.species" type="text" style="width: 100%; border: 1px solid #ccc; padding: 8px; rounded: 4px;">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
          <button @click="isEditModalOpen = false" style="padding: 8px 16px; border: 1px solid #ccc; background: white; border-radius: 4px;">取消</button>
          <button @click="submitForm" style="padding: 8px 16px; background: #409eff; color: white; border: none; border-radius: 4px;">保存</button>
        </div>
      </div>
    </div>
</template>
