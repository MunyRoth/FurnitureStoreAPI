<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $url;

    /**
     * Create a new notification instance.
     */
    public function __construct($url)
    {
        $this->url = $url;
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
        return (new MailMessage)
            ->subject(Lang::get('ការកំណត់ពាក្យសម្ងាត់ឡើងវិញ'))
            ->greeting('សួស្តី, '.$notifiable->name.'!')
            ->line(Lang::get('អ្នកកំពុងទទួលបានអ៊ីមែលនេះ ដោយសារយើងបានទទួលសំណើកំណត់ពាក្យសម្ងាត់ឡើងវិញសម្រាប់គណនីរបស់អ្នក។'))
            ->action(Lang::get('កំណត់ពាក់សម្ងាត់ឡើងវិញ'), $this->url)
            ->line(Lang::get('តំណកំណត់ពាក្យសម្ងាត់ឡើងវិញនេះនឹងផុតកំណត់នៅក្នុងរយៈពេល :count នាទី.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('ប្រសិនបើអ្នកមិនបានស្នើសុំកំណត់ពាក្យសម្ងាត់ឡើងវិញទេនោះ មិនចាំបាច់ចុច "កំណត់ពាក់សម្ងាត់ឡើងវិញ" ទេ។'));
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
