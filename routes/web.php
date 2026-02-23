<?php

/**
 * Web 路由文件 (web.php)
 *
 * 定义系统所有的 HTTP 路由规则，分为以下几个模块：
 * 1. 首页 - 公开访问，展示系统统计数据
 * 2. 控制面板 - 登录后的数据概览和图表
 * 3. 动物管理 - CRUD + 文件档案 + 护理记录
 * 4. 领养流程 - 申请/审核/交接
 * 5. 公众领养页 - 无需登录即可浏览
 */

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\Web\AdoptionController;
use App\Http\Controllers\FileRecordController;
use App\Http\Controllers\CareLogController; 
use App\Models\Animal;              
use App\Models\User;                
use App\Models\AdoptionApplication;
use App\Models\FileRecord; 
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes —— 流浪动物救助与领养管理系统
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. 首页（公开访问，无需登录）
// 展示系统介绍、统计数据（动物总数、领养数量、运行天数）
// ==========================================
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),           // 是否显示登录按钮
        'canRegister' => Route::has('register'),     // 是否显示注册按钮
        'laravelVersion' => Application::VERSION,    // Laravel 版本号
        'phpVersion' => PHP_VERSION,                 // PHP 版本号
        'siteStats' => [                             // 网站统计数据
            'totalAnimals'   => Animal::count(),     // 动物总数
            'adoptedCount'   => AdoptionApplication::where('status', 'completed')->count(), // 已完成领养数
            'runningDays'    => (int) now()->diffInDays(Animal::min('created_at') ?? now()), // 系统运行天数
        ],
    ]);
});

// ==========================================
// 2. 控制面板（登录后可访问）
// 展示统计数据、图表数据、未读通知
// ==========================================
Route::get('/dashboard', function () {
    $userId = auth()->id();
    $isAdmin = auth()->user()->isAdmin(); 

    // --- 图表数据1：每月收容趋势（最近6个月） ---
    $monthlyTrend = [];
    for ($i = 5; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $monthlyTrend[] = [
            'month' => $date->format('Y-m'),           // 月份标签
            'count' => Animal::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)->count(), // 当月新增动物数
        ];
    }

    // --- 图表数据2：物种分布（前6名） ---
    $speciesData = Animal::select('species', DB::raw('count(*) as count'))
        ->groupBy('species')->orderByDesc('count')->limit(6)->get();

    // --- 图表数据3：领养申请统计 ---
    $adoptionStats = [
        'pending'   => AdoptionApplication::where('status', 'pending')->count(),   // 待审核
        'approved'  => AdoptionApplication::where('status', 'approved')->count(),  // 已通过
        'rejected'  => AdoptionApplication::where('status', 'rejected')->count(),  // 已驳回
        'completed' => AdoptionApplication::where('status', 'completed')->count(), // 已完成
    ];

    // --- 未读通知（最多10条） ---
    $notifications = auth()->user()->unreadNotifications->take(10)->map(function ($n) {
        return [
            'id'         => $n->id,
            'message'    => $n->data['message'] ?? '',       // 通知内容
            'type'       => $n->data['type'] ?? 'info',      // 通知类型
            'created_at' => $n->created_at->diffForHumans(), // 相对时间（如“3分钟前”）
        ];
    });

    return Inertia::render('Dashboard', [
        'isAdmin' => $isAdmin,  // 是否为管理员
        'stats' => [
            'animals_count'    => Animal::count(),                               // 动物总数
            'files_count'      => FileRecord::count(),                           // 文件总数
            'today_files'      => FileRecord::whereDate('created_at', now())->count(), // 今日上传
            'pending_files'    => FileRecord::where('review_status', 'pending')->count(), // 待审核文件
            'pending_adoptions'=> AdoptionApplication::where('status', 'pending')->count(), // 待审核领养
        ],
        'chartData' => [
            'monthlyTrend' => $monthlyTrend,    // 月度趋势图表数据
            'speciesData'  => $speciesData,      // 物种分布图表数据
            'adoptionStats' => $adoptionStats,   // 领养统计图表数据
        ],
        'notifications' => $notifications,       // 未读通知列表
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// ==========================================
// 3. 需要登录的功能组（auth 中间件保护）
// ==========================================
Route::middleware('auth')->group(function () {
    
    // --- 个人资料管理 ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');       // 编辑页面
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); // 更新操作
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); // 删除账户

    // --- 动物管理 (RESTful 资源路由) ---
    Route::get('/animals/export', [AnimalController::class, 'export'])->name('animals.export'); // 导出 Excel（必须在 resource 前注册以避免被 {animal} 捕获）
    Route::resource('animals', AnimalController::class); // 标准 CRUD: index/create/store/show/edit/update/destroy

    // --- 动物文件档案管理 ---
    Route::get('/animals/{animal}/files', [FileRecordController::class, 'index'])->name('files.index');   // 查看档案列表
    Route::post('/animals/{animal}/files', [FileRecordController::class, 'store'])->name('files.store');  // 上传文件
    Route::delete('/files/{file}', [FileRecordController::class, 'destroy'])->name('files.destroy');      // 删除文件
    Route::post('/files/{file}/review', [FileRecordController::class, 'review'])->name('files.review');   // 审核文件

    // --- 动物护理记录管理 ---
    Route::get('/animals/{animal}/carelogs', [CareLogController::class, 'index'])->name('carelogs.index');   // 查看护理记录
    Route::post('/animals/{animal}/carelogs', [CareLogController::class, 'store'])->name('carelogs.store'); // 新增护理记录
    Route::put('/carelogs/{careLog}', [CareLogController::class, 'update'])->name('carelogs.update');      // 更新护理记录
    Route::delete('/carelogs/{careLog}', [CareLogController::class, 'destroy'])->name('carelogs.destroy'); // 删除护理记录

    // --- 领养申请流程 ---
    Route::get('/animals/{animal}/adopt/apply', [AdoptionController::class, 'create'])->name('adoptions.create'); // 显示申请表单
    Route::post('/animals/{animal}/adopt', [AdoptionController::class, 'store'])->name('adoptions.store');        // 提交申请
    Route::get('/my-adoptions', [AdoptionController::class, 'myApplications'])->name('adoptions.my');             // 我的申请列表

    // --- 管理员专属路由（admin 中间件保护） ---
    Route::middleware('admin')->group(function () {
        Route::get('/admin/adoptions', [AdoptionController::class, 'index'])->name('admin.adoptions.index');                   // 领养申请列表
        Route::patch('/admin/adoptions/{application}', [AdoptionController::class, 'updateStatus'])->name('admin.adoptions.update'); // 审核领养申请
    });

    // --- 快捷方式 / 占位路由 ---
    // 上传页面快捷方式：有 animal_id 参数则跳转到该动物文件页，否则跳转到动物列表
    Route::get('/upload', function(\Illuminate\Http\Request $request) { 
        if ($animalId = $request->query('animal_id')) {
            return redirect()->route('files.index', $animalId);
        }
        return redirect()->route('animals.index');
    })->name('upload.page');

    // 管理员查看待审核文件列表
    Route::get('/admin/files/pending', [FileRecordController::class, 'pending'])->middleware('admin')->name('admin.files.pending');

    // --- 通知管理 ---
    // 标记单条通知为已读
    Route::post('/notifications/{id}/read', function ($id) {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back();
    })->name('notifications.read');

    // 标记所有通知为已读
    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.readAll');
});

// ==========================================
// 4. 公众领养页面（无需登录）
// 任何人都可以浏览已审核通过的可领养动物
// ==========================================
Route::get('/adopt', [AnimalController::class, 'publicIndex'])->name('adopt.index');

// 加载认证路由（登录/注册/找回密码等）
require __DIR__.'/auth.php';