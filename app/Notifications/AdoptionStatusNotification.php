<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdoptionStatusNotification extends Notification
{
    use Queueable;

    protected string $status;
    protected string $animalName;

    public function __construct(string $status, string $animalName)
    {
        $this->status = $status;
        $this->animalName = $animalName;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusText = $this->status === 'approved' ? '已通过 ✅' : '已驳回 ❌';
        
        return [
            'type'        => $this->status === 'approved' ? 'success' : 'error',
            'message'     => "您对「{$this->animalName}」的领养申请{$statusText}",
            'animal_name' => $this->animalName,
            'status'      => $this->status,
        ];
    }
}
