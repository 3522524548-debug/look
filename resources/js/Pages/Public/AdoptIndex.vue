<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    animals: Object,
    filters: Object,
    speciesList: Array,
});

const adoptableAnimals = computed(() => props.animals?.data || []);
const user = usePage().props.auth.user;

// 搜索筛选
const search = ref(props.filters?.search ?? '');
const speciesFilter = ref(props.filters?.species ?? '');

let searchTimeout = null;
const doSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('adopt.index'), {
            search: search.value || undefined,
            species: speciesFilter.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
};

watch([search, speciesFilter], doSearch);
</script>

<template>
    <Head title="领养中心" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-50/50 via-white to-purple-50/50">
        
        <nav class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white text-lg shadow-sm">
                            🐾
                        </div>
                        <Link href="/" class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            流浪动物救助中心
                        </Link>
                    </div>

                    <div class="flex items-center gap-3">
                        <template v-if="user">
                            <span class="text-sm text-gray-500 hidden sm:inline">你好, {{ user.name }}</span>
                            <Link 
                                :href="route('dashboard')"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium rounded-xl hover:shadow-lg hover:shadow-indigo-200 transition-all duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                控制面板
                            </Link>
                        </template>

                        <template v-else>
                            <Link href="/" class="text-gray-500 hover:text-gray-700 text-sm font-medium transition">返回首页</Link>
                            <Link :href="route('login')" class="px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium rounded-xl hover:shadow-lg hover:shadow-indigo-200 transition-all duration-200">
                                登录系统
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Hero 区域 -->
                <div class="text-center mb-12 relative">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-full text-sm text-indigo-700 font-medium mb-6">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        正在匹配合适的伙伴
                    </div>
                    <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl tracking-tight">
                        寻找你的 <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">新家人</span>
                    </h1>
                    <p class="mt-4 max-w-2xl mx-auto text-lg text-gray-500">
                        每一只动物都值得被温柔以待。浏览下方的毛茸茸小伙伴，给它们一个温暖的家。
                    </p>
                </div>

                <!-- 搜索筛选栏 -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-10 flex flex-wrap gap-4 items-center justify-center">
                    <div class="flex-1 min-w-[250px] max-w-lg relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input v-model="search" type="text" placeholder="搜索动物名称、品种..." 
                            class="w-full pl-11 border-gray-200 rounded-xl shadow-sm text-sm px-5 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition py-3" />
                    </div>
                    <select v-model="speciesFilter" class="border-gray-200 rounded-xl shadow-sm text-sm px-4 py-3 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                        <option value="">全部物种</option>
                        <option v-for="s in speciesList" :key="s" :value="s">{{ s }}</option>
                    </select>
                    <button v-if="search || speciesFilter" 
                        @click="search = ''; speciesFilter = ''" 
                        class="text-sm text-gray-400 hover:text-red-500 transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        清除
                    </button>
                </div>

                <div v-if="adoptableAnimals.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="animal in adoptableAnimals" :key="animal.id" class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:border-indigo-100 transition-all duration-300 hover:-translate-y-1 flex flex-col">
                        
                        <!-- 图片区域 -->
                        <div class="h-52 bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center text-gray-300 text-6xl relative overflow-hidden">
                            <img 
                                v-if="animal.photo_path" 
                                :src="'/storage/' + animal.photo_path" 
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                alt="animal photo" 
                            />
                            <span v-else class="group-hover:scale-110 transition-transform duration-300">
                                {{ animal.species === '猫' ? '🐱' : '🐶' }}
                            </span>
                            <!-- 物种标签 -->
                            <span class="absolute top-3 right-3 px-3 py-1 bg-white/90 backdrop-blur-sm text-indigo-700 text-xs font-bold rounded-full shadow-sm">
                                {{ animal.species }}
                            </span>
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-3">
                                <h2 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition">{{ animal.name }}</h2>
                                <span class="text-sm text-gray-400 font-medium">
                                    {{ animal.age ? `${animal.age} 岁` : '年龄未知' }}
                                </span>
                            </div>
                            
                            <p class="text-gray-500 text-sm line-clamp-3 mb-5 flex-1 leading-relaxed">
                                {{ animal.description || '暂无详细描述，请联系救助站了解更多...' }}
                            </p>

                            <Link 
                                :href="route('adoptions.create', animal.id)" 
                                class="group/btn inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold text-sm rounded-xl shadow-md hover:shadow-lg hover:shadow-indigo-200 transition-all duration-200 hover:-translate-y-0.5"
                            >
                                💝 申请领养
                                <svg class="w-4 h-4 transition-transform group-hover/btn:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-20 bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-6xl mb-4">😿</div>
                    <h3 class="text-lg font-bold text-gray-800">暂时没有待领养的动物</h3>
                    <p class="mt-2 text-gray-400">救助站正在整理档案，请稍后再来看看。</p>
                </div>
                
                <div v-if="props.animals.links && props.animals.links.length > 3" class="mt-10 flex justify-center gap-1.5">
                     <Link 
                        v-for="(link, k) in props.animals.links" 
                        :key="k"
                        :href="link.url ?? '#'"
                        class="px-3 py-1.5 rounded-lg text-sm transition-all duration-200"
                        :class="{ 'bg-indigo-600 text-white shadow-sm': link.active, 'text-gray-400 cursor-not-allowed': !link.active && !link.url, 'text-gray-600 hover:bg-gray-100 bg-white border border-gray-100': !link.active && link.url }"
                     >
                        <span v-html="link.label"></span>
                     </Link>
                 </div>

            </div>
        </div>

        <!-- 页脚 -->
        <footer class="border-t border-gray-200/60 bg-white/50 py-6 mt-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-400">
                © 2025 流浪动物救助与领养管理系统 · 毕业设计作品
            </div>
        </footer>
    </div>
</template>