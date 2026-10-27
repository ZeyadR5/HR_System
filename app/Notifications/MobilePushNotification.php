<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MobilePushNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $actionedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message, $actionedBy = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->actionedBy = $actionedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title . ' - نظام الموارد البشرية')
            ->greeting('مرحباً ' . ($notifiable->name ?? 'عزيزنا الموظف'))
            ->line($this->message);

        if (!empty($this->actionedBy)) {
            $mail->line('تم اتخاذ الإجراء بواسطة: ' . $this->actionedBy);
        }

        return $mail
            ->action('فتح النظام', url('/'))
            ->line('هذا إشعار تلقائي صادر من نظام الموارد البشرية.')
            ->salutation('مع تحيات إدارة الموارد البشرية - Gamma');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'actioned_by' => $this->actionedBy,
        ];
    }
}
