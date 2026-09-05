<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ItemShippedNotification extends Notification
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
            ->subject('商品が発送されました')
            ->greeting($notifiable->name . '様')
            ->line('購入した商品が発送されました。')
            ->line('商品名: ' . $this->item->name)
            ->action('商品を確認する', url('/item/' . $this->item->id))
            ->line('商品が届いたら、受け取り確認をお願いします。');
    }
}
