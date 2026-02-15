<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exports\AnimalsExport;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class AnimalController extends Controller
{
    // ==========================================
    // 1. 后台管理端功能
    // ==========================================

    /**
     * 管理员看到的动物列表（支持搜索筛选）
     */
    public function index(Request $request)
    {
        $query = Animal::withCount('files')->orderBy('id', 'desc');

        // 关键字搜索
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('species', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 审核状态筛选
        if ($status = $request->input('status')) {
            $query->where('review_status', $status);
        }

        // 物种筛选
        if ($species = $request->input('species')) {
            $query->where('species', $species);
        }

        return Inertia::render('Animals/Index', [
            'animals' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'species']),
            'speciesList' => Animal::select('species')->distinct()->pluck('species'),
        ]);
    }

    /**
     * ⭐ 保存新动物 (包含所有修复)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'species'       => 'required|string|max:255',
            'age'           => 'nullable|integer',
            'description'   => 'nullable|string',
            'review_status' => 'required|string',
            'visibility'    => 'required', 
            'photo'         => 'nullable|image|max:2048',
        ]);

        // 处理可见性
        $inputVis = $request->visibility;
        if ($inputVis === 'public' || $inputVis == 1 || $inputVis === true || $inputVis === '1') {
            $validated['visibility'] = 'public';
        } else {
            $validated['visibility'] = 'private';
        }

        // 自动填充创建人 ID
        $validated['created_by'] = auth()->id(); 

        // 处理图片上传
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('animals', 'public');
        }

        unset($validated['photo']);
        Animal::create($validated);

        return redirect()->route('animals.index')->with('success', '新动物已成功录入系统！');
    }

    /**
     * 更新动物信息
     */
    public function update(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'species'       => 'required|string|max:255',
            'age'           => 'nullable|integer',
            'description'   => 'nullable|string',
            'review_status' => 'required|string',
            'visibility'    => 'required',
            'photo'         => 'nullable|image|max:2048',
        ]);

        // 强制转为字符串
        $inputVis = $request->visibility;
        if ($inputVis === 'public' || $inputVis == 1 || $inputVis === true || $inputVis === '1') {
            $validated['visibility'] = 'public';
        } else {
            $validated['visibility'] = 'private';
        }

        // 处理图片上传
        if ($request->hasFile('photo')) {
            // 删除旧图片
            if ($animal->photo_path && Storage::disk('public')->exists($animal->photo_path)) {
                Storage::disk('public')->delete($animal->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('animals', 'public');
        }

        unset($validated['photo']);
        $animal->update($validated);

        return redirect()->back()->with('success', '动物信息更新成功！');
    }

    /**
     * 显示单个动物详情（跳转到领养申请页）
     */
    public function show(Animal $animal)
    {
        return redirect()->route('adoptions.create', $animal->id);
    }

    /**
     * 删除动物（仅管理员）
     */
    public function destroy(Animal $animal)
    {
        abort_unless(auth()->user()->isAdmin(), 403, '仅管理员可删除动物');

        // 删除关联图片
        if ($animal->photo_path && Storage::disk('public')->exists($animal->photo_path)) {
            Storage::disk('public')->delete($animal->photo_path);
        }

        $animal->delete();
        return redirect()->back()->with('success', '动物已移除');
    }

    /**
     * 导出动物列表为 Excel
     */
    public function export()
    {
        return Excel::download(new AnimalsExport, '动物列表_' . date('Ymd_His') . '.xlsx');
    }

    // ==========================================
    // 2. 前台公众端功能
    // ==========================================

    /**
     * ⭐ 补回：公众领养列表页 (解决你刚才的报错)
     */
    public function publicIndex(Request $request)
    {
        $query = Animal::where('review_status', 'approved')
                    ->where('visibility', 'public');

        // 关键字搜索
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('species', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 物种筛选
        if ($species = $request->input('species')) {
            $query->where('species', $species);
        }

        return Inertia::render('Public/AdoptIndex', [
            'animals' => $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString(),
            'filters' => $request->only(['search', 'species']),
            'speciesList' => Animal::where('review_status', 'approved')
                            ->where('visibility', 'public')
                            ->select('species')->distinct()->pluck('species'),
        ]);
    }
}