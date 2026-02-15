<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Notifications\AdoptionStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdoptionController extends Controller
{
    // 1. 用户提交申请 (保持不变)
    public function store(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'apply_reason'  => 'required|string|min:10',
            'contact_phone' => 'required|string',
            'address'       => 'required|string',
        ]);

        $exists = AdoptionApplication::where('user_id', Auth::id())
                    ->where('animal_id', $animal->id)
                    ->where('status', 'pending')
                    ->exists();

        if ($exists) {
            return back()->with('error', '您已提交过该动物的申请，请耐心等待。');
        }

        AdoptionApplication::create([
            'user_id'       => Auth::id(),
            'animal_id'     => $animal->id,
            'apply_reason'  => $validated['apply_reason'],
            'contact_phone' => $validated['contact_phone'],
            'address'       => $validated['address'],
            'status'        => 'pending',
        ]);

        // 跳转到“我的申请”页面，方便用户立刻看到刚才的记录
        return redirect()->route('adoptions.my')->with('success', '申请提交成功！请在此关注审核结果。');
    }

    // 2. 管理员查看所有申请 (保持不变)
    public function index()
    {
        // 只有管理员能看
        abort_unless(Auth::user()->isAdmin(), 403, '仅管理员可访问');

        $applications = AdoptionApplication::with(['user', 'animal'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Admin/AdoptionApplications/Index', [
            'applications' => $applications,
        ]);
    }

    // 3. ⭐ 核心修改：管理员处理审核 (通过/驳回)
    public function updateStatus(Request $request, AdoptionApplication $application)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        // 更新申请单状态
        $application->update(['status' => $request->status]);

        // 发送通知给申请人
        $application->user->notify(new AdoptionStatusNotification(
            $request->status,
            $application->animal->name
        ));

        // ⭐ 自动下架逻辑：如果通过了 (approved)
        if ($request->status === 'approved') {
            // 将该动物的 visibility 设为 private (或 false/0)，
            // 这样它就会从 publicIndex 列表中消失，别人就看不到了。
            $application->animal->update([
                'visibility' => 'private' // 或者是 0，取决于你数据库存的是什么
            ]);
            
            // 可选：同时也把该动物的其他“待审核”申请全部自动驳回（防止冲突）
            AdoptionApplication::where('animal_id', $application->animal_id)
                ->where('id', '!=', $application->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);
        }

        return back()->with('success', '操作成功！动物状态已同步更新。');
    }

    // 4. ⭐ 新增：普通用户查看“我的申请”
    // 确认有这个方法
    public function myApplications()
    {
        $myApps = AdoptionApplication::with('animal')
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Adoptions/MyIndex', [
            'applications' => $myApps
        ]);
    }
    
    // 5. 显示填表页 (保持不变)
    public function create(Animal $animal)
    {
        return Inertia::render('Adoptions/ApplyForm', [
            'animal' => $animal
        ]);
    }
}