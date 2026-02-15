<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\CareLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CareLogController extends Controller
{
    /**
     * 列出某只动物的护理记录并渲染页面
     *
     * GET /animals/{animal}/care-logs
     */
    public function index(Animal $animal)
    {
        $logs = CareLog::where('animal_id', $animal->id)
            ->orderByDesc('care_date')
            ->orderByDesc('id')
            ->with('creator:id,name')   // 方便前端显示记录人
            ->get([
                'id',
                'animal_id',
                'care_date',
                'type',
                'notes',
                'weight',
                'height',
                'temperature',
                'next_visit_at',
                'created_by',
                'created_at',
            ]);

        return Inertia::render('Animals/CareLogs', [
            'animal' => $animal->only('id', 'name', 'species'),
            'logs'   => $logs,
        ]);
    }

    /**
     * 新增护理记录
     *
     * POST /animals/{animal}/care-logs
     */
    public function store(Request $request, Animal $animal)
    {
        $data = $request->validate([
            'care_date'     => ['required', 'date'],
            'type'          => ['required', 'string', 'max:30'],
            'notes'         => ['nullable', 'string', 'max:2000'],
            'weight'        => ['nullable', 'numeric', 'between:0,999.99'],
            'temperature'   => ['nullable', 'numeric', 'between:0,99.9'],
            'next_visit_at' => ['nullable', 'date'],
        ]);

        $userId = Auth::id();

        $log = new CareLog();

        // 必填外键
        $log->animal_id = $animal->id;

        // 日期：同时写 care_date 和 log_date（库里 log_date NOT NULL）
        $log->care_date = $data['care_date'];
        $log->log_date  = $data['care_date'];

        // 业务字段
        $log->type          = $data['type'];
        $log->notes         = $data['notes'] ?? null;
        $log->weight        = $data['weight'] ?? null;
        $log->temperature   = $data['temperature'] ?? null;
        $log->next_visit_at = $data['next_visit_at'] ?? null;

        // 记录人（你的表里有 created_by / user_id 这两个 NOT NULL 字段）
        $log->created_by = $userId;
        $log->user_id    = $userId;

        $log->save();

        return back()->with('success', '已添加护理记录');
    }

    /**
     * 更新护理记录
     *
     * 对应路由示例：
     *   Route::put('/care-logs/{id}', [CareLogController::class, 'update'])->name('carelogs.update');
     */
    public function update(Request $request, int $id)
    {
        $log = CareLog::findOrFail($id);

        $user = Auth::user();
        $isOwner = $log->created_by && $user && $log->created_by === $user->id;
        $isAdmin = $user && $user->isAdmin();

        // 既不是创建人也不是管理员，禁止编辑
        abort_unless($isOwner || $isAdmin, 403, '无权限编辑此记录');

        $data = $request->validate([
            'care_date'     => ['required', 'date'],
            'type'          => ['required', 'string', 'max:30'],
            'notes'         => ['nullable', 'string', 'max:2000'],
            'weight'        => ['nullable', 'numeric', 'between:0,999.99'],
            'temperature'   => ['nullable', 'numeric', 'between:0,99.9'],
            'next_visit_at' => ['nullable', 'date'],
        ]);

        $log->update([
            'care_date'     => $data['care_date'],
            'log_date'      => $data['care_date'],          // 同步 log_date
            'type'          => $data['type'],
            'notes'         => $data['notes'] ?? null,
            'weight'        => $data['weight'] ?? null,
            'temperature'   => $data['temperature'] ?? null,
            'next_visit_at' => $data['next_visit_at'] ?? null,
        ]);

        return back()->with('success', '已更新护理记录');
    }

    /**
     * 删除护理记录
     *
     * 对应路由示例：
     *   Route::delete('/care-logs/{id}', [CareLogController::class, 'destroy'])->name('carelogs.destroy');
     */
    public function destroy(int $id)
    {
        $log = CareLog::findOrFail($id);

        $user = Auth::user();
        $isOwner = $log->created_by && $user && $log->created_by === $user->id;
        $isAdmin = $user && $user->isAdmin();

        // 既不是创建人也不是管理员，禁止删除
        abort_unless($isOwner || $isAdmin, 403, '无权限删除此记录');

        $log->delete();

        return back()->with('success', '已删除护理记录');
    }
}
