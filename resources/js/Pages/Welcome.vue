<!--
  Welcome.vue - 系统首页/着陆页
  
  功能说明：
  - 展示系统介绍和品牌信息
  - 显示动态统计数据（动物总数、领养数量、运行天数）
  - 提供登录/注册/进入控制面板的入口
  - 提供“浏览可领养动物”的快捷入口
  
  后端数据: canLogin, canRegister, laravelVersion, phpVersion, siteStats
  路由: GET /
-->
<script setup>
/**
 * 从 Inertia.js 导入 Head(页面标题) 和 Link(路由链接) 组件
 */
import { Head, Link } from '@inertiajs/vue3';

/**
 * 接收后端传递的页面属性
 */
const props = defineProps({
    canLogin: {          // 是否显示登录按钮
        type: Boolean,
    },
    canRegister: {       // 是否显示注册按钮
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
    siteStats: {
        type: Object,
        default: () => ({ totalAnimals: 0, adoptedCount: 0, runningDays: 0 }),
    },
});
</script>

<template>
    <Head title="流浪动物救助与领养管理系统" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <!-- 顶部导航 -->
        <nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white text-lg">
                            🐾
                        </div>
                        <span class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            救助站
                        </span>
                    </div>

                    <div v-if="canLogin" class="flex items-center gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-full hover:shadow-lg hover:shadow-indigo-200 transition-all duration-300 hover:-translate-y-0.5"
                        >
                            进入控制面板
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-5 py-2.5 text-gray-600 text-sm font-medium hover:text-indigo-600 transition-colors duration-200"
                            >
                                登录
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-full hover:shadow-lg hover:shadow-indigo-200 transition-all duration-300 hover:-translate-y-0.5"
                            >
                                注册账号
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero 区域 -->
        <section class="relative overflow-hidden">
            <!-- 背景装饰 -->
            <div class="absolute inset-0 -z-10">
                <div class="absolute top-20 left-10 w-72 h-72 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
                <div class="absolute top-40 right-10 w-72 h-72 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse" style="animation-delay: 2s;"></div>
                <div class="absolute bottom-10 left-1/3 w-72 h-72 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse" style="animation-delay: 4s;"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
                <div class="text-center max-w-3xl mx-auto">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-full text-sm text-indigo-700 font-medium mb-8">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        系统运行中 · 流浪动物救助与领养管理平台
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight tracking-tight">
                        给每一只流浪动物
                        <span class="block mt-2 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 bg-clip-text text-transparent">
                            一个温暖的家 🏠
                        </span>
                    </h1>

                    <p class="mt-6 text-lg sm:text-xl text-gray-500 leading-relaxed max-w-2xl mx-auto">
                        我们致力于救助流浪动物，为它们提供医疗护理、庇护所和全新的家庭。
                        每一份爱心都能改变一只动物的命运。
                    </p>

                    <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                        <Link 
                            :href="route('adopt.index')" 
                            class="group px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-2xl shadow-xl shadow-indigo-200 hover:shadow-2xl hover:shadow-indigo-300 transition-all duration-300 hover:-translate-y-1 text-base flex items-center justify-center gap-2"
                        >
                            <span>🐾</span> 浏览待领养动物
                            <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </Link>
                        <Link 
                            v-if="canLogin && !$page.props.auth.user"
                            :href="route('login')" 
                            class="px-8 py-4 bg-white text-gray-700 font-bold rounded-2xl border-2 border-gray-200 hover:border-indigo-300 hover:text-indigo-600 transition-all duration-300 hover:-translate-y-1 text-base flex items-center justify-center gap-2"
                        >
                            <span>👤</span> 员工登录
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- 功能特色 -->
        <section class="py-20 bg-white/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-extrabold text-gray-900">我们的服务</h2>
                    <p class="mt-4 text-lg text-gray-500">全方位的流浪动物救助与管理平台</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="group bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-xl hover:border-indigo-100 transition-all duration-300 hover:-translate-y-2 text-center">
                        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-5 text-3xl group-hover:scale-110 transition-transform duration-300">
                            🐕
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">动物收容管理</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">系统化管理每只动物的基本信息、健康状况和照片档案</p>
                    </div>

                    <div class="group bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-xl hover:border-green-100 transition-all duration-300 hover:-translate-y-2 text-center">
                        <div class="w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center mx-auto mb-5 text-3xl group-hover:scale-110 transition-transform duration-300">
                            📋
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">健康档案</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">完整的体检报告、疫苗接种、治疗记录电子化管理</p>
                    </div>

                    <div class="group bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-xl hover:border-purple-100 transition-all duration-300 hover:-translate-y-2 text-center">
                        <div class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center mx-auto mb-5 text-3xl group-hover:scale-110 transition-transform duration-300">
                            💝
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">在线领养</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">便捷的在线领养申请流程，让爱心人士快速找到心仪伙伴</p>
                    </div>

                    <div class="group bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-xl hover:border-orange-100 transition-all duration-300 hover:-translate-y-2 text-center">
                        <div class="w-16 h-16 bg-orange-50 rounded-2xl flex items-center justify-center mx-auto mb-5 text-3xl group-hover:scale-110 transition-transform duration-300">
                            📊
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">数据分析</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">直观的数据看板，实时掌握救助站运营状况和趋势</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 数据统计 -->
        <section class="py-20">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-3xl p-12 text-white text-center shadow-2xl shadow-indigo-200 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                    <div class="absolute bottom-0 left-0 w-40 h-40 bg-white/10 rounded-full blur-2xl -ml-10 -mb-10"></div>

                    <h2 class="text-3xl font-bold mb-10 relative z-10">用行动传递温暖</h2>
                    <div class="grid grid-cols-3 gap-8 relative z-10">
                        <div>
                            <div class="text-4xl font-extrabold">{{ siteStats.totalAnimals }}+</div>
                            <div class="mt-2 text-indigo-200 text-sm">累计救助</div>
                        </div>
                        <div>
                            <div class="text-4xl font-extrabold">{{ siteStats.adoptedCount }}+</div>
                            <div class="mt-2 text-indigo-200 text-sm">成功领养</div>
                        </div>
                        <div>
                            <div class="text-4xl font-extrabold">{{ siteStats.runningDays || 1 }}</div>
                            <div class="mt-2 text-indigo-200 text-sm">天守护</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 页脚 -->
        <footer class="border-t border-gray-200/60 bg-white/50 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="flex items-center justify-center gap-2 mb-3">
                    <span class="text-xl">🐾</span>
                    <span class="font-bold text-gray-700">流浪动物救助与领养管理系统</span>
                </div>
                <p class="text-sm text-gray-400">© 2025 毕业设计作品 · 用科技守护每一个小生命</p>
            </div>
        </footer>
    </div>
</template>
