<?php

/**
 * 动物数据 Excel 导出类 (AnimalsExport)
 *
 * 使用 maatwebsite/excel 包将动物列表导出为 .xlsx 文件。
 * 实现三个接口：
 * - FromCollection: 提供数据集合
 * - WithHeadings: 定义表头
 * - WithMapping: 自定义每行数据的映射规则
 */

namespace App\Exports;

use App\Models\Animal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AnimalsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * 获取要导出的数据集合
     * 按ID倒序获取所有动物记录
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Animal::orderBy('id', 'desc')->get();
    }

    /**
     * 定义 Excel 表头（第一行）
     *
     * @return array
     */
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

    /**
     * 自定义每行数据的映射规则
     * 将模型属性转换为可读的导出格式
     *
     * @param  Animal $animal 动物模型实例
     * @return array 一行数据
     */
    public function map($animal): array
    {
        // 审核状态英文转中文映射
        $statusMap = [
            'approved' => '已通过',
            'pending'  => '待审核',
            'rejected' => '已驳回',
            'adopted'  => '已领养',
        ];

        return [
            $animal->id,
            $animal->name,
            $animal->species,
            $animal->age ?? '-',                                              // 空值显示为“-”
            $animal->description ?? '-',
            $statusMap[$animal->review_status] ?? $animal->review_status,     // 翻译状态
            $animal->created_at?->format('Y-m-d H:i'),                       // 格式化日期
        ];
    }
}
