<?php

/**
 * 文件档案控制器 (FileRecordController)
 *
 * 负责动物相关文件(照片/PDF文档)的上传、查看、删除和审核。
 * 文件上传后默认为待审核状态，管理员审核通过后普通用户才能看到。
 * 普通用户只能看到已审核通过的文件，管理员可看到所有状态的文件。
 *
 * 路由前缀: /animals/{animal}/files, /admin/files
 */

namespace App\Http\Controllers;

use App\Models\FileRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FileRecordController extends Controller
{
    /**
     * 显示某只动物的所有 PDF 档案
     *
     * GET /animals/{animalId}/files
     * 普通用户只看已审核通过的，管理员可看全部
     *
     * @param  int $animalId 动物ID
     * @return \Inertia\Response 渲染 AnimalFiles 页面
     */
    public function index(int $animalId)
    {
        $user = Auth::user();

        $query = FileRecord::with('uploader') // 预加载上传人信息
            ->where('animal_id', $animalId)
            ->orderByDesc('created_at');

        // 普通用户只能看审核通过的文件
        if (!$this->isAdmin($user)) {
            $query->where('review_status', 'approved');
        }

        $files = $query->get([
            'id',
            'animal_id',
            'type',           // 文件类型
            'path',           // 存储路径
            'original_name',  // 原始文件名
            'size_kb',        // 文件大小
            'mime',           // MIME类型
            'uploaded_by',    // 上传人ID
            'review_status',  // 审核状态
            'remark',         // 备注
            'created_at',     // 上传时间
        ]);

        return Inertia::render('AnimalFiles', [
            'animalId' => $animalId,
            'files'    => $files,
        ]);
    }

    /**
     * 处理文件上传
     *
     * POST /animals/{animal}/files
     * 仅接受 PDF 格式，最大5MB。上传后状态为 pending（待审核）
     * 文件存储在 storage/app/public/pdfs 目录
     *
     * @param  Request $request 包含 animal_id, type, file, remark
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 验证上传数据
        $validated = $request->validate([
            'animal_id' => ['required', 'integer'],                           // 动物ID
            'type'      => ['required', 'string'],                            // 文件类型
            'file'      => ['required', 'file', 'mimetypes:application/pdf', 'max:5120'], // PDF文件，最大5MB
            'remark'    => ['nullable', 'string', 'max:1000'],                // 备注
        ]);

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $validated['file'];

        // 保存文件到 storage/app/public/pdfs 目录
        $path = $file->store('pdfs', 'public');

        // 创建文件记录，默认状态为 pending（待管理员审核）
        FileRecord::create([
            'animal_id'     => $validated['animal_id'],
            'type'          => $validated['type'],
            'path'          => $path,                           // 存储路径
            'original_name' => $file->getClientOriginalName(),  // 原始文件名
            'size_kb'       => (int) ceil($file->getSize() / 1024), // 文件大小(KB)
            'mime'          => $file->getMimeType(),             // MIME类型
            'remark'        => $validated['remark'] ?? null,
            'uploaded_by'   => Auth::id(),                       // 当前登录用户
            'review_status' => 'pending',                       // 初始状态：待审核
        ]);

        return back()->with('success', '上传成功，已提交审核。');
    }

    /**
     * 删除档案（仅管理员可操作）
     *
     * DELETE /files/{id}
     * 会同时删除服务器上的物理文件和数据库记录
     *
     * @param  int $id 文件记录ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        // 权限检查：仅管理员可删除
        abort_unless(Auth::user()->isAdmin(), 403, '仅管理员可删除档案');

        $record = FileRecord::findOrFail($id);

        // 删除服务器上的物理文件
        if ($record->path && Storage::disk('public')->exists($record->path)) {
            Storage::disk('public')->delete($record->path);
        }

        // 删除数据库记录
        $record->delete();

        return back()->with('success', '档案已删除');
    }

    /**
     * 管理员查看所有待审核的档案
     *
     * GET /admin/files/pending
     * 显示所有 review_status='pending' 的文件，附带动物和上传人信息
     *
     * @return \Inertia\Response 渲染 Admin/ReviewFiles 页面
     */
    public function pending()
    {
        $user = Auth::user();

        // 权限检查：仅管理员可访问
        abort_unless($this->isAdmin($user), 403, '仅管理员可访问');

        // 查询所有待审核的文件，预加载关联的动物和上传人
        $files = FileRecord::with(['animal', 'uploader'])
            ->where('review_status', 'pending')
            ->orderByDesc('created_at')
            ->get([
                'id',
                'animal_id',
                'type',
                'original_name',
                'size_kb',
                'review_status',
                'remark',
                'uploaded_by',
                'created_at',
                'path',
            ]);

        return Inertia::render('Admin/ReviewFiles', [
            'files' => $files,
        ]);
    }

    /**
     * 管理员审核某条档案（通过/驳回）
     *
     * POST /admin/files/{file}/review
     * 审核通过后状态变为 approved，驳回则变为 rejected
     * 审核意见会追加到 remark 字段中
     *
     * @param  Request    $request 包含 action(通过/驳回) 和 comment(审核意见)
     * @param  FileRecord $file    路由模型绑定的文件记录
     * @return \Illuminate\Http\JsonResponse
     */
    public function review(Request $request, FileRecord $file)
    {
        $user = Auth::user();

        // 权限检查
        abort_unless($this->isAdmin($user), 403, '仅管理员可审核');

        // 验证审核操作
        $data = $request->validate([
            'action'  => ['required', 'in:approve,reject'], // approve=通过, reject=驳回
            'comment' => ['nullable', 'string', 'max:500'], // 审核意见（可选）
        ]);

        // 更新审核状态和审核人信息
        $file->review_status = $data['action'] === 'approve' ? 'approved' : 'rejected';
        $file->reviewed_by   = $user->id;  // 审核人
        $file->reviewed_at   = now();       // 审核时间

        // 如果有审核意见，追加到 remark 字段
        if (!empty($data['comment'])) {
            $prefix       = $file->remark ? $file->remark . "\n" : '';
            $file->remark = $prefix . '[审核意见] ' . $data['comment'];
        }

        $file->save();

        return response()->json([
            'message' => '审核已提交',
            'status'  => $file->review_status,
        ]);
    }

    /**
     * 工具方法：判断用户是否为管理员
     *
     * @param  mixed $user 用户实例
     * @return bool 是否为管理员
     */
    private function isAdmin($user): bool
    {
        return $user && $user->isAdmin();
    }
}
