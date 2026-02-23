<!--
    组件：表单错误提示
    当 message 有值时展示红色提示文本。
-->
<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-red-600">
            {{ message }}
        </p>
    </div>
</template>
