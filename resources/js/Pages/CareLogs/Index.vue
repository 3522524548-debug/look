<!--
  CareLogs/Index.vue - 护理记录页（简版/占位页）
  
  功能说明：
  - 护理记录的简版页面，主要功能在 Animals/CareLogs.vue 中实现
  - 接收 logs 和 animals 数据
-->
<script setup>
/** 接收后端传递的护理记录和动物列表 */
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
