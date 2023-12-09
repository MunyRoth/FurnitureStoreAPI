<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('ការផ្ទៀងផ្ទាត់អាសយដ្ឋានអ៊ីម៉ែល')
            ->greeting('សួស្តី, '.$notifiable->name.'!')
            ->line('សូមចុចប៊ូតុងខាងក្រោមដើម្បីផ្ទៀងផ្ទាត់អាសយដ្ឋានអ៊ីមែលរបស់អ្នក។')
            ->action('ផ្ទៀងផ្ទាត់អាសយដ្ឋានអ៊ីម៉ែល', $verificationUrl)
            ->line('ប្រសិនបើអ្នកមិនបានបង្កើតគណនីទេ មិនចាំបាច់ចុច "ផ្ទៀងផ្ទាត់អាសយដ្ឋានអ៊ីម៉ែល" ទេ។');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
