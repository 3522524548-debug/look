<script setup>
import { defineProps, ref } from 'vue'
import axios from 'axios'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  files: {
    type: Array,
    default: () => [],
  },
})

// 拷贝一份到本地，后面审核时好更新界面
const items = ref(props.files ?? [])

// 发起审核请求：approve / reject
const reviewFile = async (fileId, action) => {
  if (!['approve', 'reject'].includes(action)) return

  const text = action === 'approve' ? '通过' : '驳回'
  if (!confirm(`确认要${text}这条档案吗？`)) return

  try {
    const response = await axios.post(`/files/${fileId}/review`, {
      action,
    })

    // 审核成功后，本地更新这一条记录的 review_status
    const idx = items.value.findIndex(f => f.id === fileId)
    if (idx !== -1) {
      items.value[idx].review_status = response.data.status || (action === 'approve' ? 'approved' : 'rejected')
    }

    alert(`审核${text}成功`)
  } catch (e) {
    console.error(e)
    alert(`审核${text}失败，请稍后再试`)
  }
}
</script>

<template>
  <div style="padding: 24px;">
    <!-- 返回控制面板 -->
    <div style="margin-bottom: 16px;">
      <Link
        :href="route('dashboard')"
        class="inline-flex items-center px-3 py-1 bg-gray-600 border border-transparent
               rounded-md text-xs font-semibold text-white hover:bg-gray-500
               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
      >
        ← 返回控制面板
      </Link>
    </div>

    <h2 style="margin-bottom: 12px; font-size: 20px; font-weight: bold;">
      待审核档案列表
    </h2>

    <p style="margin-bottom: 16px; color: #666; font-size: 14px;">
      这里只显示 <strong>审核状态为 pending</strong> 的档案。你可以对每一条进行「通过」或「驳回」操作。
    </p>

    <table
      border="1"
      cellspacing="0"
      cellpadding="8"
      style="width: 100%; text-align: left; font-size: 14px;"
    >
      <thead style="background: #f5f5f5;">
        <tr>
          <th style="width: 60px;">ID</th>
          <th style="width: 80px;">动物ID</th>
          <th style="width: 120px;">类型</th>
          <th>文件名</th>
          <th style="width: 90px;">大小(KB)</th>
          <th style="width: 120px;">上传人</th>
          <th style="width: 140px;">上传时间</th>
          <th style="width: 80px;">状态</th>
          <th style="width: 140px;">操作</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="file in items" :key="file.id">
          <td>{{ file.id }}</td>
          <td>{{ file.animal_id }}</td>
          <td>{{ file.type }}</td>
          <td>
            <a
              :href="`/storage/${file.path}`"
              target="_blank"
              style="color:#409eff;"
            >
              {{ file.original_name || '查看PDF' }}
            </a>
          </td>
          <td>{{ file.size_kb }}</td>
          <td>
            <span v-if="file.uploader">
              {{ file.uploader.name }}
            </span>
            <span v-else-if="file.uploaded_by">
              用户 #{{ file.uploaded_by }}
            </span>
            <span v-else>—</span>
          </td>
          <td>{{ file.created_at }}</td>
          <td>
            <span v-if="file.review_status === 'approved'" style="color: green;">
              已通过
            </span>
            <span v-else-if="file.review_status === 'rejected'" style="color: red;">
              已驳回
            </span>
            <span v-else>
              待审核
            </span>
          </td>
          <td>
            <!-- 只有待审核的才显示按钮，避免重复审核 -->
            <template v-if="file.review_status === 'pending'">
              <button
                type="button"
                @click="reviewFile(file.id, 'approve')"
                style="margin-right: 6px; padding: 2px 8px; background:#4caf50; color:#fff; border:none; border-radius:3px; cursor:pointer;"
              >
                通过
              </button>
              <button
                type="button"
                @click="reviewFile(file.id, 'reject')"
                style="padding: 2px 8px; background:#f44336; color:#fff; border:none; border-radius:3px; cursor:pointer;"
              >
                驳回
              </button>
            </template>
            <span v-else>—</span>
          </td>
        </tr>

        <tr v-if="items.length === 0">
          <td colspan="9" style="text-align:center; padding:20px;">
            当前没有需要审核的档案。
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
