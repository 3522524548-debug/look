<?php

/**
 * 领养状态变更通知 (AdoptionStatusNotification)
 *
 * 当管理员审核领养申请后，自动发送通知给申请人。
 * 通知通过数据库渠道(database)发送，存储在 notifications 表中。
 * 支持三种状态：已通过(approved)、已驳回(rejected)、交接完成(completed)
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdoptionStatusNotification extends Notification
{
    use Queueable; // 支持队列异步发送

    /** @var string 申请状态 (approved/rejected/completed) */
    protected string $status;

    /** @var string 动物名称 */
    protected string $animalName;

    /**
     * 创建通知实例
     *
     * @param string $status     新状态
     * @param string $animalName 动物名称
     */
    public function __construct(string $status, string $animalName)
    {
        $this->status = $status;
        $this->animalName = $animalName;
    }

    /**
     * 定义通知发送渠道
     * 使用 database 渠道，存入 notifications 表
     *
     * @param  object $notifiable 接收通知的用户
     * @return array 渠道列表
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * 定义存入数据库的通知内容
     *
     * @param  object $notifiable 接收通知的用户
     * @return array 通知数据（存入 notifications 表的 data 字段）
     */
    public function toArray(object $notifiable): array
    {
        // 状态文字和类型映射
        $statusMap = [
            'approved'  => ['text' => '已通过 ✅', 'type' => 'success'],
            'rejected'  => ['text' => '已驳回 ❌', 'type' => 'error'],
            'completed' => ['text' => '已完成交接 🎉', 'type' => 'success'],
        ];

        $info = $statusMap[$this->status] ?? ['text' => $this->status, 'type' => 'info'];

        // 根据状态生成不同的消息内容
        $message = $this->status === 'completed'
            ? "恭喜！「{$this->animalName}」的领养交接已完成，请好好照顾它哦 🐾"
            : "您对「{$this->animalName}」的领养申请{$info['text']}";

        return [
            'type'        => $info['type'],         // 通知类型 (success/error/info)
            'message'     => $message,               // 通知内容
            'animal_name' => $this->animalName,      // 动物名称
            'status'      => $this->status,          // 原始状态值
        ];
    }
}
