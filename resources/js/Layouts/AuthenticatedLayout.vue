<!--
  AuthenticatedLayout.vue - 已登录用户通用布局
  
  功能说明：
  - 所有需要登录的页面的统一布局包裹器
  - 顶部导航栏：控制面板、动物管理、领养列表、管理员菜单
  - 用户下拉菜单：个人资料、登出
  - 移动端响应式导航菜单
  - Toast 通知系统：显示成功/错误提示（自动消失）
  - 通过 <slot /> 插槽渲染子页面内容
-->
<script setup>
import { ref, watch, computed } from 'vue';                          // Vue 响应式 API
import ApplicationLogo from '@/Components/ApplicationLogo.vue';       // 应用 Logo 组件
import Dropdown from '@/Components/Dropdown.vue';                     // 下拉菜单组件
import DropdownLink from '@/Components/DropdownLink.vue';             // 下拉菜单链接组件
import NavLink from '@/Components/NavLink.vue';                       // 导航链接组件
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';   // 移动端导航链接
import { Link, usePage } from '@inertiajs/vue3';                      // Inertia 组件

/** 控制移动端导航菜单的显示/隐藏 */
const showingNavigationDropdown = ref(false);

/** 获取当前页面共享数据（包含 auth.user, flash 等） */
const page = usePage();

/** Toast 通知系统（替代 alert 弹窗，提供更好的用户体验） */
const toast = ref({ show: false, message: '', type: 'success' });

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

watch(flashSuccess, (message) => {
    if (message) {
        toast.value = { show: true, message, type: 'success' };
        setTimeout(() => { toast.value.show = false; }, 4000);
        if (page.props.flash) {
            page.props.flash.success = null;
        }
    }
}, { immediate: true });

watch(flashError, (message) => {
    if (message) {
        toast.value = { show: true, message, type: 'error' };
        setTimeout(() => { toast.value.show = false; }, 5000);
        if (page.props.flash) {
            page.props.flash.error = null;
        }
    }
}, { immediate: true });

// ⭐ 返回上一页功能
const goBack = () => {
    window.history.back();
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-indigo-50/30">
            <nav class="bg-white/95 backdrop-blur-md border-b border-gray-200/60 sticky top-0 z-50 shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center gap-2">
                                <Link :href="route('dashboard')" class="flex items-center gap-2 group">
                                    <ApplicationLogo class="block h-8 w-auto transition-transform group-hover:scale-110 duration-200" />
                                    <span class="hidden lg:block text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                        救助站
                                    </span>
                                </Link>
                            </div>

                            <div class="hidden space-x-1 sm:-my-px sm:ms-8 sm:flex items-center">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" /></svg>
                                        控制面板
                                    </span>
                                </NavLink>
                                
                                <NavLink :href="route('animals.index')" :active="route().current('animals.index')">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                                        动物管理
                                    </span>
                                </NavLink>

                                <NavLink :href="route('adopt.index')" :active="route().current('adopt.index')">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                        领养中心
                                    </span>
                                </NavLink>

                                <NavLink v-if="$page.props.auth.user.is_admin || $page.props.auth.user.role_status === 1" :href="route('admin.adoptions.index')" :active="route().current('admin.adoptions.index')">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        审核申请
                                    </span>
                                </NavLink>
                                <NavLink :href="route('adoptions.my')" :active="route().current('adoptions.my')">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                        我的申请
                                    </span>
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center gap-2 px-3 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-full text-gray-600 bg-gray-50 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 focus:outline-none transition-all duration-200">
                                                <span class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 text-white text-xs flex items-center justify-center font-bold">
                                                    {{ $page.props.auth.user.name?.charAt(0)?.toUpperCase() }}
                                                </span>
                                                {{ $page.props.auth.user.name }}
                                                <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                个人资料
                                            </span>
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            <span class="flex items-center gap-2 text-red-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                                退出登录
                                            </span>
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-200">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden border-t border-gray-100">
                    <div class="pt-2 pb-3 space-y-1 bg-gray-50/80">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')"> 🏠 控制面板 </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('animals.index')" :active="route().current('animals.index')"> 🐾 动物管理 </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('adopt.index')" :active="route().current('adopt.index')"> 🔍 领养中心 </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('adoptions.my')" :active="route().current('adoptions.my')"> 📋 我的申请 </ResponsiveNavLink>
                    </div>
                    <div class="pt-4 pb-3 border-t border-gray-200 bg-white/80">
                        <div class="flex items-center px-4 gap-3">
                            <span class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 text-white text-sm flex items-center justify-center font-bold">
                                {{ $page.props.auth.user.name?.charAt(0)?.toUpperCase() }}
                            </span>
                            <div>
                                <div class="font-semibold text-base text-gray-800">{{ $page.props.auth.user.name }}</div>
                                <div class="text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                            </div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')"> 个人资料 </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button"> 退出登录 </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-white/80 backdrop-blur-sm shadow-sm border-b border-gray-100" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <div>
                        <slot name="header" />
                    </div>

                    <button 
                        @click="goBack"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-200 hover:text-gray-800 transition-all duration-200"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        返回
                    </button>
                </div>
            </header>

            <!-- Toast 通知 -->
            <Teleport to="body">
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 -translate-y-3"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-3"
                >
                    <div v-if="toast.show" class="fixed top-4 left-1/2 -translate-x-1/2 z-[9999]">
                        <div :class="[
                            'flex items-center gap-3 px-6 py-3.5 rounded-2xl shadow-xl text-sm font-medium backdrop-blur-sm border',
                            toast.type === 'success' 
                                ? 'bg-green-50/95 text-green-700 border-green-200 shadow-green-100/50' 
                                : 'bg-red-50/95 text-red-700 border-red-200 shadow-red-100/50'
                        ]">
                            <span class="text-lg">{{ toast.type === 'success' ? '✅' : '❌' }}</span>
                            <span>{{ toast.message }}</span>
                            <button @click="toast.show = false" class="ml-2 opacity-50 hover:opacity-100 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </Transition>
            </Teleport>

            <main>
                <slot />
            </main>

            <!-- 页脚 -->
            <footer class="border-t border-gray-200/60 bg-white/50 mt-16">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-400">
                    © 2025 流浪动物救助与领养管理系统 · 毕业设计作品
                </div>
            </footer>
        </div>
    </div>
</template>