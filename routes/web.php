<?php

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
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. 首页
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// 2. 控制面板
Route::get('/dashboard', function () {
    $userId = auth()->id();
    $isAdmin = auth()->user()->isAdmin(); 

    // 图表数据：每月收容趋势（最近6个月）
    $monthlyTrend = [];
    for ($i = 5; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $monthlyTrend[] = [
            'month' => $date->format('Y-m'),
            'count' => Animal::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)->count(),
        ];
    }

    // 图表数据：物种分布
    $speciesData = Animal::select('species', DB::raw('count(*) as count'))
        ->groupBy('species')->orderByDesc('count')->limit(6)->get();

    // 图表数据：领养统计
    $adoptionStats = [
        'pending'  => AdoptionApplication::where('status', 'pending')->count(),
        'approved' => AdoptionApplication::where('status', 'approved')->count(),
        'rejected' => AdoptionApplication::where('status', 'rejected')->count(),
    ];

    // 通知数据
    $notifications = auth()->user()->unreadNotifications->take(10)->map(function ($n) {
        return [
            'id'         => $n->id,
            'message'    => $n->data['message'] ?? '',
            'type'       => $n->data['type'] ?? 'info',
            'created_at' => $n->created_at->diffForHumans(),
        ];
    });

    return Inertia::render('Dashboard', [
        'isAdmin' => $isAdmin,
        'stats' => [
            'animals_count' => Animal::count(),
            'files_count'   => FileRecord::count(),
            'today_files'   => FileRecord::whereDate('created_at', now())->count(),
            'pending_files' => FileRecord::where('review_status', 'pending')->count(),
        ],
        'chartData' => [
            'monthlyTrend' => $monthlyTrend,
            'speciesData'  => $speciesData,
            'adoptionStats' => $adoptionStats,
        ],
        'notifications' => $notifications,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// 3. 需要登录的功能组
Route::middleware('auth')->group(function () {
    
    // --- 个人资料 ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- 动物管理 ---
    Route::get('/animals/export', [AnimalController::class, 'export'])->name('animals.export');
    Route::resource('animals', AnimalController::class);

    // --- 档案管理 ---
    Route::get('/animals/{animal}/files', [FileRecordController::class, 'index'])->name('files.index');
    Route::post('/animals/{animal}/files', [FileRecordController::class, 'store'])->name('files.store');
    Route::delete('/files/{file}', [FileRecordController::class, 'destroy'])->name('files.destroy');
    Route::post('/files/{file}/review', [FileRecordController::class, 'review'])->name('files.review');

    // --- 护理记录 ---
    Route::get('/animals/{animal}/carelogs', [CareLogController::class, 'index'])->name('carelogs.index');
    Route::post('/animals/{animal}/carelogs', [CareLogController::class, 'store'])->name('carelogs.store');
    Route::put('/carelogs/{careLog}', [CareLogController::class, 'update'])->name('carelogs.update');
    Route::delete('/carelogs/{careLog}', [CareLogController::class, 'destroy'])->name('carelogs.destroy');

    // --- 领养申请流程 ---
    Route::get('/animals/{animal}/adopt/apply', [AdoptionController::class, 'create'])->name('adoptions.create');
    Route::post('/animals/{animal}/adopt', [AdoptionController::class, 'store'])->name('adoptions.store');
    Route::get('/my-adoptions', [AdoptionController::class, 'myApplications'])->name('adoptions.my');

    // --- 管理员审核 ---
    Route::get('/admin/adoptions', [AdoptionController::class, 'index'])->name('admin.adoptions.index');
    Route::patch('/admin/adoptions/{application}', [AdoptionController::class, 'updateStatus'])->name('admin.adoptions.update');

    // --- 快捷方式 / 占位路由 ---
    // 上传页面快捷方式（跳转到动物列表，用户可选择动物后上传）
    Route::get('/upload', function(\Illuminate\Http\Request $request) { 
        if ($animalId = $request->query('animal_id')) {
            return redirect()->route('files.index', $animalId);
        }
        return redirect()->route('animals.index');
    })->name('upload.page');
    // 待审核文件
    Route::get('/admin/files/pending', [FileRecordController::class, 'pending'])->name('admin.files.pending');

    // --- 通知 ---
    Route::post('/notifications/{id}/read', function ($id) {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back();
    })->name('notifications.read');

    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.readAll');
});

// 4. 公众领养页面
Route::get('/adopt', [AnimalController::class, 'publicIndex'])->name('adopt.index');

require __DIR__.'/auth.php';