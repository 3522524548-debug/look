<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

// 1. 保留之前定义的 props 结构
const props = defineProps({
    animal: Object,
    auth: Object,
    flash: Object,
});

// 2. 状态管理：控制弹窗
const isModalOpen = ref(false);

// 3. 保留之前确定的 useForm 字段，确保与数据库一致
const form = useForm({
    apply_reason: '',
    contact_phone: props.auth.user?.phone || '', 
    address: '',
});

// 4. 保留之前的提交逻辑和回调
const submit = () => {
    form.post(route('adoption.store', props.animal.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            isModalOpen.value = false;
        },
        onError: (errors) => {
            console.error('提交失败:', errors);
        }
    });
};
</script>

<template>
    <div class="max-w-7xl mx-auto py-10 px-4">
        <h1 class="text-3xl font-bold">{{ props.animal.name }}</h1>
        <p class="mt-4 text-gray-600">当前状态: {{ props.animal.status }}</p>

        <div v-if="props.flash.success || props.flash.error" class="mt-4 p-4 rounded-md" 
             :class="{'bg-green-100 text-green-800': props.flash.success, 'bg-red-100 text-red-800': props.flash.error}">
            {{ props.flash.success || props.flash.error }}
        </div>

        <div class="mt-8">
            <button 
                v-if="props.auth.user && props.animal.status === 'available'"
                @click="isModalOpen = true"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition"
            >
                申请领养 {{ props.animal.name }}
            </button>
            <p v-else-if="props.animal.status !== 'available'" class="text-red-500">
                此动物当前不可领养。
            </p>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg p-8 w-full max-w-md shadow-2xl">
                <h3 class="text-2xl font-bold mb-6">领养申请：{{ props.animal.name }}</h3>
                
                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">领养理由:</label>
                        <textarea v-model="form.apply_reason" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md p-2 shadow-sm"></textarea>
                        <div v-if="form.errors.apply_reason" class="text-red-500 text-xs mt-1">{{ form.errors.apply_reason }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">联系电话:</label>
                        <input type="text" v-model="form.contact_phone" class="mt-1 block w-full border border-gray-300 rounded-md p-2 shadow-sm">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">详细居住地址:</label>
                        <input type="text" v-model="form.address" class="mt-1 block w-full border border-gray-300 rounded-md p-2 shadow-sm">
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-gray-600">取消</button>
                        <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50">
                            {{ form.processing ? '提交中...' : '提交申请' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>