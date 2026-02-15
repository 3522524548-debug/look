<script setup>
defineProps({ logs: Object, animals: Array })
</script>

<template>
  <div style="padding:24px">
    <h2>护理记录页面加载成功 ✅</h2>
    <p>（这里以后再渲染表格与表单）</p>

    <div style="margin-top:12px; color:#666;">
      <div>animals 传过来：{{ animals?.length ?? 0 }} 条</div>
      <div>logs 传过来：{{ logs?.data?.length ?? 0 }} 条</div>
    </div>
  </div>
</template>
