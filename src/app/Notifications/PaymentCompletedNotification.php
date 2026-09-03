<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(private Item $item)
    {
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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('お支払いが確認できました')
            ->greeting($notifiable->name . '様')
            ->line('コンビニ払いのお支払いが確認できました。')
            ->line('商品名: ' . $this->item->name)
            ->action('発送手続きをする', url('/item/' . $this->item->id))
            ->line('マイページから発送手続きをお願いします。');
    }
}
