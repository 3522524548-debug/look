<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

const props = defineProps({
    stats: Object,
    isAdmin: Boolean,
    chartData: Object,
    notifications: Array,
});

const showNotifications = ref(false);
const lineChart = ref(null);
const pieChart = ref(null);
const barChart = ref(null);

const markAsRead = (id) => {
    router.post(route('notifications.read', id), {}, { preserveScroll: true });
};
const markAllRead = () => {
    router.post(route('notifications.readAll'), {}, { preserveScroll: true });
};

onMounted(() => {
    // 月度趋势折线图
    if (lineChart.value) {
        new Chart(lineChart.value, {
            type: 'line',
            data: {
                labels: props.chartData.monthlyTrend.map(i => i.month),
                datasets: [{
                    label: '收容数量',
                    data: props.chartData.monthlyTrend.map(i => i.count),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.1)',
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    }
    // 物种分布饼图
    if (pieChart.value) {
        const colors = ['#6366f1','#f59e0b','#10b981','#ef4444','#8b5cf6','#ec4899'];
        new Chart(pieChart.value, {
            type: 'doughnut',
            data: {
                labels: props.chartData.speciesData.map(i => i.species),
                datasets: [{
                    data: props.chartData.speciesData.map(i => i.count),
                    backgroundColor: colors.slice(0, props.chartData.speciesData.length),
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }
    // 领养统计柱状图
    if (barChart.value) {
        new Chart(barChart.value, {
            type: 'bar',
            data: {
                labels: ['待审核', '已通过', '已驳回'],
                datasets: [{
                    label: '申请数',
                    data: [
                        props.chartData.adoptionStats.pending,
                        props.chartData.adoptionStats.approved,
                        props.chartData.adoptionStats.rejected,
                    ],
                    backgroundColor: ['#f59e0b', '#10b981', '#ef4444'],
                    borderRadius: 6,
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    }
});
</script>

<template>
    <Head title="控制面板" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                工作台
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-lg p-8 mb-10 text-white flex flex-col md:flex-row justify-between items-center relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 -ml-10 -mb-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>

                    <div class="relative z-10">
                        <h3 class="text-3xl font-bold">
                            欢迎回来，{{ isAdmin ? '管理员' : '爱心领养人' }} 👋
                        </h3>
                        <p class="mt-2 text-indigo-100 text-lg">
                            {{ isAdmin ? '这里是救助站指挥中心。' : '感谢您对流浪动物的关爱！' }}
                        </p>
                    </div>
                    
                    <div class="relative z-10 mt-6 md:mt-0">
                        <Link 
                            :href="route('adopt.index')" 
                            class="group bg-white text-indigo-600 px-6 py-3 rounded-full font-bold shadow-md hover:bg-indigo-50 transition transform hover:-translate-y-1 flex items-center gap-2"
                        >
                            <span>🚀</span> 前往公众领养门户
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition duration-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase">收容动物</p>
                                <h4 class="text-3xl font-bold text-gray-800 mt-2">{{ stats?.animals_count ?? 0 }}</h4>
                            </div>
                            <div class="p-3 bg-blue-50 text-blue-500 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition duration-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase">档案记录</p>
                                <h4 class="text-3xl font-bold text-gray-800 mt-2">{{ stats?.files_count ?? 0 }}</h4>
                            </div>
                            <div class="p-3 bg-green-50 text-green-500 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition duration-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase">今日新增</p>
                                <h4 class="text-3xl font-bold text-gray-800 mt-2">{{ stats?.today_files ?? 0 }}</h4>
                            </div>
                            <div class="p-3 bg-purple-50 text-purple-500 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div v-if="isAdmin" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition duration-300 ring-2 ring-yellow-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase">待审核</p>
                                <h4 class="text-3xl font-bold text-yellow-600 mt-2">{{ stats?.pending_files ?? 0 }}</h4>
                            </div>
                            <div class="p-3 bg-yellow-50 text-yellow-500 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-800 mb-5 pl-3 border-l-4 border-indigo-500 leading-none">
                    {{ isAdmin ? '管理操作' : '快捷服务' }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <Link :href="route('animals.index')" class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-lg transition duration-300 flex items-center gap-4 cursor-pointer">
                        <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-lg text-gray-800 group-hover:text-blue-600 transition">
                                {{ isAdmin ? '动物管理列表' : '查看动物档案' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ isAdmin ? '编辑、删除、审核动物' : '查看救助站所有动物' }}
                            </div>
                        </div>
                    </Link>

                    <Link v-if="isAdmin" :href="route('upload.page')" class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-green-300 hover:shadow-lg transition duration-300 flex items-center gap-4 cursor-pointer">
                        <div class="w-14 h-14 bg-green-100 text-green-600 rounded-full flex items-center justify-center group-hover:bg-green-600 group-hover:text-white transition duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-lg text-gray-800 group-hover:text-green-600 transition">上传 PDF 档案</div>
                            <div class="text-sm text-gray-500">为动物添加新的健康记录</div>
                        </div>
                    </Link>

                    <Link v-if="isAdmin" :href="route('admin.files.pending')" class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-yellow-300 hover:shadow-lg transition duration-300 flex items-center gap-4 cursor-pointer">
                        <div class="w-14 h-14 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center group-hover:bg-yellow-600 group-hover:text-white transition duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-lg text-gray-800 group-hover:text-yellow-600 transition">审核档案</div>
                            <div class="text-sm text-gray-500">有 <span class="text-red-500 font-bold">{{ stats?.pending_files ?? 0 }}</span> 份档案待处理</div>
                        </div>
                    </Link>

                    <div v-if="!isAdmin" class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 opacity-75">
                         <div class="w-14 h-14 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-lg text-gray-800">联系管理员</div>
                            <div class="text-sm text-gray-500">如有疑问请联系救助站</div>
                        </div>
                    </div>

                </div>

                <!-- 数据图表区域 -->
                <h3 class="text-lg font-bold text-gray-800 mb-5 pl-3 border-l-4 border-purple-500 leading-none mt-10">
                    数据看板
                </h3>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition duration-300">
                        <h4 class="text-sm font-bold text-gray-600 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-50 text-indigo-500 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                            </span>
                            月度收容趋势
                        </h4>
                        <canvas ref="lineChart" height="200"></canvas>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition duration-300">
                        <h4 class="text-sm font-bold text-gray-600 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-amber-50 text-amber-500 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                            </span>
                            物种分布
                        </h4>
                        <canvas ref="pieChart" height="200"></canvas>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition duration-300">
                        <h4 class="text-sm font-bold text-gray-600 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-green-50 text-green-500 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            </span>
                            领养申请统计
                        </h4>
                        <canvas ref="barChart" height="200"></canvas>
                    </div>
                </div>

                <!-- 通知面板 -->
                <div v-if="notifications && notifications.length > 0" class="mb-10">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-bold text-gray-800 pl-3 border-l-4 border-red-500 leading-none flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            最新通知 <span class="text-sm text-red-500 font-normal ml-1">({{ notifications.length }} 条未读)</span>
                        </h3>
                        <button @click="markAllRead" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition">
                            全部标为已读
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div v-for="n in notifications" :key="n.id" 
                            class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition">
                            <div class="flex items-center gap-3">
                                <div v-if="n.type === 'success'" class="w-9 h-9 bg-green-50 text-green-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div v-else-if="n.type === 'error'" class="w-9 h-9 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </div>
                                <div v-else class="w-9 h-9 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ n.message }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ n.created_at }}</p>
                                </div>
                            </div>
                            <button @click="markAsRead(n.id)" class="text-xs text-gray-400 hover:text-indigo-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 transition">
                                已读
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>