<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ItemSoldNotification extends Notification
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
        $mail = (new MailMessage)
            ->subject('商品が売れました')
            ->greeting($notifiable->name . '様')
            ->line('出品していた商品が購入されました。')
            ->line('商品名: ' . $this->item->name)
            ->line('価格: ¥' . number_format($this->item->price))
            ->action('商品を確認する', url('/item/' . $this->item->id));

        if ($this->item->order->payment_status === 'paid') {
            return $mail->line('マイページから発送手続きをお願いします。');
        }

        return $mail->line('コンビニ払いのお支払いが確認できたら、改めてお知らせします。');
    }
}
