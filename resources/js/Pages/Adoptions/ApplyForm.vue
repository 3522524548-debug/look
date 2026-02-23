<!--
  Adoptions/ApplyForm.vue - 领养申请表单页
  
  功能说明：
  - 用户填写领养申请信息：领养理由、联系电话、联系地址
  - 显示申请领养的动物信息卡片
  - 表单规则：领养理由至少10字，电话和地址必填
  - 提交后跳转到“我的申请”页面
  
  后端数据: animal
  路由: GET /animals/{animal}/adopt/apply
-->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // 已登录用户布局
import { Head, useForm, Link } from '@inertiajs/vue3';               // Inertia 组件

/** 接收后端传递的动物信息 */
const props = defineProps({
    animal: Object,       // 申请领养的动物详细信息
});

/** 使用 Inertia useForm 创建响应式表单 */
const form = useForm({
    apply_reason: '',     // 领养理由
    contact_phone: '',
    address: '',
});

const submit = () => {
    form.post(route('adoptions.store', props.animal.id), {
        // ⭐ 成功时的反馈
        onSuccess: () => {
            alert('🎉 申请提交成功！请耐心等待管理员审核。');
        },
        // ⭐ 失败时的反馈 (关键！)
        onError: (errors) => {
            console.log(errors); // 在控制台打印错误详情
            alert('⚠️ 提交失败！请检查页面上的红色错误提示。\n(提示：领养理由至少要写10个字)');
        },
        // ⭐ 无论成功失败，结束时执行
        onFinish: () => {
            // 可以做一些清理工作，比如重置按钮状态（Inertia会自动处理loading）
        }
    });
};

// 返回上一页
const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="填写领养申请" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                申请领养：{{ animal.name }}
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <button @click="goBack" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 font-semibold group transition">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        返回上一页
                    </button>
                </div>

                <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
                    <!-- 动物信息头部 -->
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6 text-white">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">💝</span>
                            <div>
                                <h3 class="text-xl font-bold">申请领养 {{ animal.name }}</h3>
                                <p class="text-indigo-200 text-sm mt-0.5">{{ animal.species }} · 请如实填写信息，工作人员将在3个工作日内审核</p>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="p-8 space-y-6">
                        
                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-1.5">联系电话 <span class="text-red-500">*</span></label>
                            <input 
                                v-model="form.contact_phone" 
                                type="text" 
                                class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition" 
                                placeholder="请输入您的手机号"
                            />
                            <div v-if="form.errors.contact_phone" class="text-red-500 text-sm mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ form.errors.contact_phone }}
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-1.5">居住地址 <span class="text-red-500">*</span></label>
                            <input 
                                v-model="form.address" 
                                type="text" 
                                class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition" 
                                placeholder="请输入详细居住地址"
                            />
                            <div v-if="form.errors.address" class="text-red-500 text-sm mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ form.errors.address }}
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-1.5">
                                领养理由 <span class="text-red-500">* (至少10个字)</span>
                            </label>
                            <textarea 
                                v-model="form.apply_reason" 
                                rows="5" 
                                class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition" 
                                placeholder="请详细描述您的领养条件、养宠经验以及对它的承诺..."
                            ></textarea>
                            
                            <div v-if="form.errors.apply_reason" class="text-red-500 text-sm mt-1.5 font-medium flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ form.errors.apply_reason }}
                            </div>
                            
                            <div class="text-right text-xs mt-1.5" :class="form.apply_reason.length >= 10 ? 'text-green-500' : 'text-gray-400'">
                                {{ form.apply_reason.length }} / 10+ 字
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl shadow-md hover:shadow-lg hover:shadow-indigo-200 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <span v-if="form.processing" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    正在提交...
                                </span>
                                <span v-else>提交申请 →</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>