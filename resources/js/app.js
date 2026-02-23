/**
 * app.js - 前端应用入口文件
 *
 * 负责初始化整个前端应用：
 * 1. 导入全局样式 (Tailwind CSS)
 * 2. 初始化 axios 等基础库
 * 3. 创建 Inertia.js 应用实例
 * 4. 注册 Vue 插件 (Inertia, Ziggy)
 * 5. 配置页面解析和加载进度条
 */
import '../css/app.css';   // Tailwind CSS 全局样式
import './bootstrap';       // 基础库初始化 (axios 等)

import { createInertiaApp } from '@inertiajs/vue3';                    // Inertia.js Vue 3 适配器
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'; // 页面组件解析工具
import { createApp, h } from 'vue';                                     // Vue 3 核心
import { ZiggyVue } from '../../vendor/tightenco/ziggy';                // Ziggy 插件（Laravel 路由在前端使用）

// 应用名称：从环境变量读取，默认为 'Laravel'
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// 创建 Inertia 应用
createInertiaApp({
    // 页面标题格式：“页面名 - 应用名”
    title: (title) => `${title} - ${appName}`,

    // 页面组件解析：根据后端传来的页面名，动态加载对应的 .vue 文件
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'), // 自动扫描 Pages 目录下所有 .vue 文件
        ),

    // Vue 应用初始化
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)     // 注册 Inertia 插件
            .use(ZiggyVue)   // 注册 Ziggy 插件（提供 route() 函数）
            .mount(el);      // 挂载到 DOM 元素
    },

    // 页面加载进度条配置
    progress: {
        color: '#4B5563', // 进度条颜色（灰色）
    },
});
