<?php

/**
 * 动物控制器 (AnimalController)
 *
 * 负责动物信息的 CRUD 操作，分为两部分：
 * 1. 后台管理端 —— 管理员可查看/新增/编辑/删除动物，导出Excel
 * 2. 前台公众端 —— 普通用户浏览已审核通过的可领养动物
 *
 * 路由前缀: /animals (后台), /adopt (前台)
 */

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exports\AnimalsExport;         // Excel 导出类
use Maatwebsite\Excel\Facades\Excel;   // maatwebsite/excel 包的门面
use Inertia\Inertia;                   // Inertia.js 服务端适配器

class AnimalController extends Controller
{
    // ==========================================
    // 1. 后台管理端功能
    // ==========================================

    /**
     * 管理员动物列表页（支持搜索、状态筛选、物种筛选）
     *
     * GET /animals
     * 返回分页数据，每页10条，附带查询参数以保持翻页时的筛选状态
     *
     * @param  Request $request 包含 search/status/species 等筛选参数
     * @return \Inertia\Response 渲染 Animals/Index 页面
     */
    public function index(Request $request)
    {
        // 构建查询：附带文件数量统计，按ID倒序（最新的排在前面）
        $query = Animal::withCount('files')->orderBy('id', 'desc');

        // 关键字搜索：同时搜索名称、物种、描述
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('species', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 按审核状态筛选（pending/approved/adopted）
        if ($status = $request->input('status')) {
            $query->where('review_status', $status);
        }

        // 按物种筛选
        if ($species = $request->input('species')) {
            $query->where('species', $species);
        }

        // 渲染页面，传递分页数据、当前筛选条件、物种列表
        return Inertia::render('Animals/Index', [
            'animals' => $query->paginate(10)->withQueryString(),          // 分页数据
            'filters' => $request->only(['search', 'status', 'species']), // 当前筛选条件（回填前端表单）
            'speciesList' => Animal::select('species')->distinct()->pluck('species'), // 可用物种列表（下拉框选项）
        ]);
    }

    /**
     * 创建新动物记录
     *
     * POST /animals
     * 处理流程：验证输入 → 转换可见性 → 填充创建人 → 处理图片 → 保存记录
     *
     * @param  Request $request 包含动物信息和可选的照片文件
     * @return \Illuminate\Http\RedirectResponse 成功后跳转回列表页
     */
    public function store(Request $request)
    {
        // 表单验证规则
        $validated = $request->validate([
            'name'          => 'required|string|max:255',   // 名称（必填）
            'species'       => 'required|string|max:255',   // 物种（必填）
            'age'           => 'nullable|integer',          // 年龄（可选）
            'description'   => 'nullable|string',           // 描述（可选）
            'review_status' => 'required|string',           // 审核状态（必填）
            'visibility'    => 'required',                  // 可见性（必填）
            'photo'         => 'nullable|image|max:2048',   // 照片（可选，最大2MB）
        ]);

        // 处理可见性：统一将各种 true 值转为 'public' 字符串
        $inputVis = $request->visibility;
        if ($inputVis === 'public' || $inputVis == 1 || $inputVis === true || $inputVis === '1') {
            $validated['visibility'] = 'public';
        } else {
            $validated['visibility'] = 'private';
        }

        // 自动填充当前登录用户为创建人
        $validated['created_by'] = auth()->id(); 

        // 如果上传了照片，存储到 storage/app/public/animals 目录
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('animals', 'public');
        }

        // 移除 photo 字段（数据库中不存在该字段，只存 photo_path）
        unset($validated['photo']);
        Animal::create($validated);

        return redirect()->route('animals.index')->with('success', '新动物已成功录入系统！');
    }

    /**
     * 更新动物信息
     *
     * PUT /animals/{animal}
     * 支持更新基础信息和替换照片（旧照片会被自动删除）
     *
     * @param  Request $request 更新的表单数据
     * @param  Animal  $animal  路由模型绑定自动注入的动物实例
     * @return \Illuminate\Http\RedirectResponse 成功后返回上一页
     */
    public function update(Request $request, Animal $animal)
    {
        // 验证规则与创建时相同
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'species'       => 'required|string|max:255',
            'age'           => 'nullable|integer',
            'description'   => 'nullable|string',
            'review_status' => 'required|string',
            'visibility'    => 'required',
            'photo'         => 'nullable|image|max:2048',
        ]);

        // 统一处理可见性字段
        $inputVis = $request->visibility;
        if ($inputVis === 'public' || $inputVis == 1 || $inputVis === true || $inputVis === '1') {
            $validated['visibility'] = 'public';
        } else {
            $validated['visibility'] = 'private';
        }

        // 如果上传了新照片，先删除旧照片再保存新照片
        if ($request->hasFile('photo')) {
            // 删除旧照片文件
            if ($animal->photo_path && Storage::disk('public')->exists($animal->photo_path)) {
                Storage::disk('public')->delete($animal->photo_path);
            }
            // 存储新照片
            $validated['photo_path'] = $request->file('photo')->store('animals', 'public');
        }

        // 移除 photo 字段后更新数据库记录
        unset($validated['photo']);
        $animal->update($validated);

        return redirect()->back()->with('success', '动物信息更新成功！');
    }

    /**
     * 显示单个动物详情页
     *
     * GET /animals/{animal}
     * 展示动物的完整信息，包括照片、描述、状态等
     *
     * @param  Animal $animal 路由模型绑定自动注入
     * @return \Inertia\Response 渲染 Animals/Show 页面
     */
    public function show(Animal $animal)
    {
        return Inertia::render('Animals/Show', [
            'animal' => $animal,
        ]);
    }

    /**
     * 删除动物记录（仅管理员可操作）
     *
     * DELETE /animals/{animal}
     * 会同时删除关联的照片文件
     *
     * @param  Animal $animal 要删除的动物
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Animal $animal)
    {
        // 权限检查：非管理员返回 403
        abort_unless(auth()->user()->isAdmin(), 403, '仅管理员可删除动物');

        // 删除服务器上的照片文件
        if ($animal->photo_path && Storage::disk('public')->exists($animal->photo_path)) {
            Storage::disk('public')->delete($animal->photo_path);
        }

        // 删除数据库记录（关联的文件档案和护理记录不会级联删除）
        $animal->delete();
        return redirect()->back()->with('success', '动物已移除');
    }

    /**
     * 导出动物列表为 Excel 文件
     *
     * GET /animals/export
     * 使用 maatwebsite/excel 包生成 .xlsx 文件并直接下载
     * 文件名格式: 动物列表_年月日_时分秒.xlsx
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return Excel::download(new AnimalsExport, '动物列表_' . date('Ymd_His') . '.xlsx');
    }

    // ==========================================
    // 2. 前台公众端功能
    // ==========================================

    /**
     * 前台公众领养列表页
     *
     * GET /adopt
     * 只展示审核通过且公开可见的动物，支持搜索和物种筛选
     * 每页显示12条记录（卡片布局）
     *
     * @param  Request $request 包含 search/species 筛选参数
     * @return \Inertia\Response 渲染 Public/AdoptIndex 页面
     */
    public function publicIndex(Request $request)
    {
        // 只查询已审核通过且公开的动物
        $query = Animal::where('review_status', 'approved')
                    ->where('visibility', 'public');

        // 关键字模糊搜索
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('species', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 按物种筛选
        if ($species = $request->input('species')) {
            $query->where('species', $species);
        }

        return Inertia::render('Public/AdoptIndex', [
            'animals' => $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString(),
            'filters' => $request->only(['search', 'species']),
            // 物种列表只包含已审核通过且公开的动物的物种
            'speciesList' => Animal::where('review_status', 'approved')
                            ->where('visibility', 'public')
                            ->select('species')->distinct()->pluck('species'),
        ]);
    }
}