<?php

namespace App\Exports;

use App\Models\Animal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AnimalsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Animal::orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            '名称',
            '物种',
            '年龄',
            '描述',
            '审核状态',
            '创建时间',
        ];
    }

    public function map($animal): array
    {
        $statusMap = [
            'approved' => '已通过',
            'pending'  => '待审核',
            'rejected' => '已驳回',
        ];

        return [
            $animal->id,
            $animal->name,
            $animal->species,
            $animal->age ?? '-',
            $animal->description ?? '-',
            $statusMap[$animal->review_status] ?? $animal->review_status,
            $animal->created_at?->format('Y-m-d H:i'),
        ];
    }
}
