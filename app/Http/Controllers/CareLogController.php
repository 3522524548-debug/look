<?php

/**
 * 护理记录控制器 (CareLogController)
 *
 * 负责动物护理记录的 CRUD 操作。
 * 护理记录包括喂养、体检、驱虫、疫苗等日常护理信息。
 * 支持记录体重、身高、体温等健康指标，便于追踪动物健康状况。
 * 编辑/删除权限：仅创建人或管理员可操作。
 *
 * 路由前缀: /animals/{animal}/care-logs
 */

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\CareLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CareLogController extends Controller
{
    /**
     * 列出某只动物的所有护理记录
     *
     * GET /animals/{animal}/care-logs
     * 按护理日期倒序排列，并预加载创建人信息
     *
     * @param  Animal $animal 路由模型绑定的动物实例
     * @return \Inertia\Response 渲染 Animals/CareLogs 页面
     */
    public function index(Animal $animal)
    {
        $logs = CareLog::where('animal_id', $animal->id)
            ->orderByDesc('care_date')   // 按护理日期倒序
            ->orderByDesc('id')          // 同一天的按ID倒序
            ->with('creator:id,name')    // 预加载创建人（只取id和名字，减少数据传输）
            ->get([
                'id',
                'animal_id',
                'care_date',      // 护理日期
                'type',           // 护理类型
                'notes',          // 备注
                'weight',         // 体重
                'height',         // 身高
                'temperature',    // 体温
                'next_visit_at',  // 下次复查时间
                'created_by',     // 创建人ID
                'created_at',     // 创建时间
            ]);

        return Inertia::render('Animals/CareLogs', [
            'animal' => $animal->only('id', 'name', 'species'), // 只传递必要的动物信息
            'logs'   => $logs,
        ]);
    }

    /**
     * 新增护理记录
     *
     * POST /animals/{animal}/care-logs
     * 自动填充动物ID、记录人信息，同时写入 care_date 和 log_date
     *
     * @param  Request $request 表单数据
     * @param  Animal  $animal  关联的动物
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Animal $animal)
    {
        // 验证输入数据
        $data = $request->validate([
            'care_date'     => ['required', 'date'],                   // 护理日期（必填）
            'type'          => ['required', 'string', 'max:30'],       // 护理类型（必填）
            'notes'         => ['nullable', 'string', 'max:2000'],     // 备注（可选）
            'weight'        => ['nullable', 'numeric', 'between:0,999.99'], // 体重（可选）
            'temperature'   => ['nullable', 'numeric', 'between:0,99.9'],   // 体温（可选）
            'next_visit_at' => ['nullable', 'date'],                   // 下次复查时间（可选）
        ]);

        $userId = Auth::id(); // 当前登录用户ID

        $log = new CareLog();

        // 必填外键：关联动物
        $log->animal_id = $animal->id;

        // 日期：同时写入 care_date 和 log_date（因为库里 log_date 为 NOT NULL）
        $log->care_date = $data['care_date'];
        $log->log_date  = $data['care_date'];

        // 业务字段
        $log->type          = $data['type'];
        $log->notes         = $data['notes'] ?? null;
        $log->weight        = $data['weight'] ?? null;
        $log->temperature   = $data['temperature'] ?? null;
        $log->next_visit_at = $data['next_visit_at'] ?? null;

        // 创建人：同时写入 created_by 和 user_id（表中两个字段均为 NOT NULL）
        $log->created_by = $userId;
        $log->user_id    = $userId;

        $log->save();

        return back()->with('success', '已添加护理记录');
    }

    /**
     * 更新护理记录
     *
     * PUT /care-logs/{id}
     * 权限检查：仅创建人或管理员可编辑
     *
     * @param  Request $request 更新的表单数据
     * @param  int     $id      护理记录ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        $log = CareLog::findOrFail($id);

        $user = Auth::user();
        $isOwner = $log->created_by && $user && $log->created_by === $user->id; // 是否为创建人
        $isAdmin = $user && $user->isAdmin(); // 是否为管理员

        // 权限检查：既不是创建人也不是管理员，禁止编辑
        abort_unless($isOwner || $isAdmin, 403, '无权限编辑此记录');

        // 表单验证
        $data = $request->validate([
            'care_date'     => ['required', 'date'],
            'type'          => ['required', 'string', 'max:30'],
            'notes'         => ['nullable', 'string', 'max:2000'],
            'weight'        => ['nullable', 'numeric', 'between:0,999.99'],
            'temperature'   => ['nullable', 'numeric', 'between:0,99.9'],
            'next_visit_at' => ['nullable', 'date'],
        ]);

        // 更新记录，同步写入 log_date
        $log->update([
            'care_date'     => $data['care_date'],
            'log_date'      => $data['care_date'],          // 同步 log_date 字段
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
     * DELETE /care-logs/{id}
     * 权限检查：仅创建人或管理员可删除
     *
     * @param  int $id 护理记录ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        $log = CareLog::findOrFail($id);

        $user = Auth::user();
        $isOwner = $log->created_by && $user && $log->created_by === $user->id; // 是否为创建人
        $isAdmin = $user && $user->isAdmin(); // 是否为管理员

        // 权限检查
        abort_unless($isOwner || $isAdmin, 403, '无权限删除此记录');

        $log->delete();

        return back()->with('success', '已删除护理记录');
    }
}
