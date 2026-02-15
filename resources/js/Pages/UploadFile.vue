<script setup>
import { reactive, ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'

// 用 reactive 管理表单
const form = reactive({
  animal_id: '',
  type: '检疫报告',
  file: null,
  remark: '',
})

const uploading = ref(false)
const message = ref('')

// 页面挂载时，从 URL 上读取 ?animal_id=xxx
onMounted(() => {
  const url = new URL(window.location.href)
  const idFromQuery = url.searchParams.get('animal_id')
  if (idFromQuery) {
    form.animal_id = idFromQuery        // 直接赋值给表单
  }
})

const handleFileChange = (event) => {
  form.file = event.target.files[0] || null
}

const submit = async () => {
  if (!form.file) {
    message.value = '请先选择一个 PDF 文件'
    return
  }

  if (!form.animal_id) {
    message.value = '请先填写动物 ID'
    return
  }

  uploading.value = true
  message.value = ''

  const data = new FormData()
  data.append('animal_id', form.animal_id)
  data.append('type', form.type)
  data.append('file', form.file)
  data.append('remark', form.remark || '')   // 备注，可空

  try {
    await axios.post('/files/upload', data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    message.value = '上传成功，等待管理员审核。'
  } catch (error) {
    console.error(error)
    message.value = '上传失败，请检查表单或稍后重试。'
  } finally {
    uploading.value = false
  }
}
</script>

<template>
  <div style="padding: 24px; max-width: 600px;">
    <!-- ✅ 顶部返回按钮 -->
    <div style="margin-bottom: 16px;">
      <!-- 如果带着 animal_id，从该动物档案列表跳回去；否则回控制面板 -->
      <Link
        :href="form.animal_id ? route('files.index', { animalId: form.animal_id }) : route('dashboard')"
        class="inline-flex items-center px-3 py-1 bg-gray-600 border border-transparent
               rounded-md text-xs font-semibold text-white hover:bg-gray-500
               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
      >
        返回
      </Link>
      <span style="margin-left: 8px; font-size: 12px; color: #666;">
        （带着动物 ID 来时会返回档案列表，否则回到控制面板）
      </span>
    </div>

    <h2 style="margin-bottom: 16px;">上传动物 PDF 档案</h2>

    <div style="margin-bottom: 12px;">
      <label>动物 ID：</label>
      <input type="number" v-model="form.animal_id" />
      <span style="margin-left: 8px; color: #666;">
        当前 ID：{{ form.animal_id || '（还未指定）' }}
      </span>
    </div>

    <div style="margin-bottom: 12px;">
      <label>文件类型：</label>
      <select v-model="form.type">
        <option value="检疫报告">检疫报告</option>
        <option value="疫苗证明">疫苗证明</option>
        <option value="其他">其他</option>
      </select>
    </div>

    <div style="margin-bottom: 12px;">
      <label>备注说明（可选）：</label><br />
      <textarea
        v-model="form.remark"
        rows="3"
        style="width: 100%; max-width: 400px;"
        placeholder="例如：2025年11月体检报告、疫苗加强针记录等"
      ></textarea>
    </div>

    <div style="margin-bottom: 12px;">
      <label>选择文件（仅 PDF）：</label>
      <input type="file" accept="application/pdf" @change="handleFileChange" />
    </div>

    <button :disabled="uploading" @click="submit">
      {{ uploading ? '上传中...' : '提交上传' }}
    </button>

    <p style="margin-top: 12px; color: #409eff;">{{ message }}</p>
  </div>
</template>
