<?php

/**
 * 领养控制器 (AdoptionController)
 *
 * 负责动物领养申请的全流程管理：
 * 1. 用户提交领养申请
 * 2. 管理员查看所有申请
 * 3. 管理员审核（通过/驳回/确认交接）
 * 4. 用户查看“我的申请”
 * 5. 显示申请表单页
 *
 * 审核流程：pending(待审核) → approved(已通过) → completed(交接完成)
 *                                → rejected(已驳回)
 *
 * 路由前缀: /adoptions, /admin/adoptions
 */

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Notifications\AdoptionStatusNotification; // 领养状态变更通知
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdoptionController extends Controller
{
    /**
     * 1. 用户提交领养申请
     *
     * POST /animals/{animal}/adopt
     * 防止重复提交：同一用户对同一动物只能有一个 pending 状态的申请
     *
     * @param  Request $request 包含 apply_reason, contact_phone, address
     * @param  Animal  $animal  申请领养的动物
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Animal $animal)
    {
        // 验证表单数据
        $validated = $request->validate([
            'apply_reason'  => 'required|string|min:10',  // 领养理由（至少10字）
            'contact_phone' => 'required|string',         // 联系电话
            'address'       => 'required|string',         // 联系地址
        ]);

        // 检查是否已有待审核的申请（防重复提交）
        $exists = AdoptionApplication::where('user_id', Auth::id())
                    ->where('animal_id', $animal->id)
                    ->where('status', 'pending')
                    ->exists();

        if ($exists) {
            return back()->with('error', '您已提交过该动物的申请，请耐心等待。');
        }

        // 创建领养申请记录
        AdoptionApplication::create([
            'user_id'       => Auth::id(),
            'animal_id'     => $animal->id,
            'apply_reason'  => $validated['apply_reason'],
            'contact_phone' => $validated['contact_phone'],
            'address'       => $validated['address'],
            'status'        => 'pending', // 初始状态：待审核
        ]);

        return redirect()->route('adoptions.my')->with('success', '申请提交成功！请在此关注审核结果。');
    }

    /**
     * 2. 管理员查看所有领养申请
     *
     * GET /admin/adoptions
     * 显示所有申请，按时间倒序，每页10条
     *
     * @return \Inertia\Response 渲染 Admin/AdoptionApplications/Index 页面
     */
    public function index()
    {
        // 权限检查：仅管理员可访问
        abort_unless(Auth::user()->isAdmin(), 403, '仅管理员可访问');

        // 查询所有申请，预加载用户和动物信息
        $applications = AdoptionApplication::with(['user', 'animal'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Admin/AdoptionApplications/Index', [
            'applications' => $applications,
        ]);
    }

    /**
     * 3. 管理员处理审核（通过/驳回/确认交接）
     *
     * PUT /admin/adoptions/{application}
     * 审核流程：
     * - approved: 通过申请，动物自动下架，其他待审核申请自动驳回
     * - rejected: 驳回申请
     * - completed: 确认交接完成，动物标记为“已领养”（仅从 approved 状态流转）
     * 每次状态变更都会发送通知给申请人
     *
     * @param  Request              $request     包含 status 字段
     * @param  AdoptionApplication  $application 路由模型绑定的申请实例
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, AdoptionApplication $application)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,completed',
        ]);

        // completed 只能从 approved 状态流转
        if ($request->status === 'completed' && $application->status !== 'approved') {
            return back()->with('error', '只有已通过的申请才能确认交接。');
        }

        // 更新申请单状态
        $application->update(['status' => $request->status]);

        // 发送状态变更通知给申请人
        $application->user->notify(new AdoptionStatusNotification(
            $request->status,
            $application->animal->name
        ));

        // 审核通过时的自动处理
        if ($request->status === 'approved') {
            // 动物自动下架（设为私有）
            $application->animal->update([
                'visibility' => 'private'
            ]);
            
            // 该动物的其他待审核申请全部自动驳回
            AdoptionApplication::where('animal_id', $application->animal_id)
                ->where('id', '!=', $application->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);
        }

        // 确认交接时的自动处理
        if ($request->status === 'completed') {
            // 标记动物为“已领养”，完成出库闭环
            $application->animal->update([
                'review_status' => 'adopted',
                'visibility'    => 'private',
            ]);
        }

        // 状态文字映射（用于返回给前端的提示消息）
        $statusTexts = [
            'approved'  => '已通过，等待交接',
            'rejected'  => '已驳回',
            'completed' => '交接完成，动物已出库',
        ];

        return back()->with('success', $statusTexts[$request->status] ?? '操作成功');
    }

    /**
     * 4. 普通用户查看“我的申请”
     *
     * GET /adoptions/my
     * 显示当前用户的所有领养申请记录
     *
     * @return \Inertia\Response 渲染 Adoptions/MyIndex 页面
     */
    public function myApplications()
    {
        $myApps = AdoptionApplication::with('animal') // 预加载关联动物信息
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Adoptions/MyIndex', [
            'applications' => $myApps
        ]);
    }
    
    /**
     * 5. 显示领养申请表单页
     *
     * GET /animals/{animal}/adopt
     * 展示申请表单，用户填写理由、电话、地址后提交
     *
     * @param  Animal $animal 申请领养的动物
     * @return \Inertia\Response 渲染 Adoptions/ApplyForm 页面
     */
    public function create(Animal $animal)
    {
        return Inertia::render('Adoptions/ApplyForm', [
            'animal' => $animal
        ]);
    }
}
