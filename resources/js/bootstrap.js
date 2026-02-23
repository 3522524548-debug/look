/**
 * bootstrap.js - 前端基础库初始化
 *
 * 配置全局的 axios HTTP 客户端：
 * - 将 axios 挂载到 window 对象（便于全局访问）
 * - 设置默认请求头 X-Requested-With（标识为 AJAX 请求）
 */
import axios from 'axios';
window.axios = axios; // 全局可用

// 设置默认请求头，让 Laravel 后端识别为 AJAX 请求
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
