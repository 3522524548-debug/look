<?php

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
     */
    public function index(int $animalId)
    {
        $user = Auth::user();

        $query = FileRecord::with('uploader')
            ->where('animal_id', $animalId)
            ->orderByDesc('created_at');

        // 普通用户只看审核通过的
        if (!$this->isAdmin($user)) {
            $query->where('review_status', 'approved');
        }

        $files = $query->get([
            'id',
            'animal_id',
            'type',
            'path',
            'original_name',
            'size_kb',
            'mime',
            'uploaded_by',
            'review_status',
            'remark',
            'created_at',
        ]);

        return Inertia::render('AnimalFiles', [
            'animalId' => $animalId,
            'files'    => $files,
        ]);
    }

    /**
     * 处理上传
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'animal_id' => ['required', 'integer'],
            'type'      => ['required', 'string'],
            'file'      => ['required', 'file', 'mimetypes:application/pdf', 'max:5120'],
            'remark'    => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $validated['file'];

        // 保存文件到 storage/app/public/pdfs
        $path = $file->store('pdfs', 'public');

        // 创建记录，默认待审核 pending
        FileRecord::create([
            'animal_id'     => $validated['animal_id'],
            'type'          => $validated['type'],
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'size_kb'       => (int) ceil($file->getSize() / 1024),
            'mime'          => $file->getMimeType(),
            'remark'        => $validated['remark'] ?? null,
            'uploaded_by'   => Auth::id(),
            'review_status' => 'pending',
        ]);

        return back()->with('success', '上传成功，已提交审核。');
    }

    /**
     * 删除档案（仅管理员）
     */
    public function destroy(int $id)
    {
        abort_unless(Auth::user()->isAdmin(), 403, '仅管理员可删除档案');

        $record = FileRecord::findOrFail($id);

        // 删物理文件
        if ($record->path && Storage::disk('public')->exists($record->path)) {
            Storage::disk('public')->delete($record->path);
        }

        // 删数据库记录
        $record->delete();

        return back()->with('success', '档案已删除');
    }

    /**
     * 管理员查看所有待审核的档案
     */
    public function pending()
    {
        $user = Auth::user();

        // 只有管理员可以访问
        abort_unless($this->isAdmin($user), 403, '仅管理员可访问');

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
     * 管理员审核某条档案（通过 / 驳回）
     */
    public function review(Request $request, FileRecord $file)
    {
        $user = Auth::user();

        // 只有管理员可以审核
        abort_unless($this->isAdmin($user), 403, '仅管理员可审核');

        $data = $request->validate([
            'action'  => ['required', 'in:approve,reject'], // 通过 / 驳回
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $file->review_status = $data['action'] === 'approve' ? 'approved' : 'rejected';
        $file->reviewed_by   = $user->id;
        $file->reviewed_at   = now();

        // 如果有审核意见，就拼到 remark 后面
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
     * 小工具：判断是否管理员
     */
    private function isAdmin($user): bool
    {
        return $user && $user->isAdmin();
    }
}
