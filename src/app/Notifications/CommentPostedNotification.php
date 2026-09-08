<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentPostedNotification extends Notification
{
    use Queueable;

    public function __construct(private Item $item, private string $commentBody)
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
            ->subject('商品にコメントが届きました')
            ->greeting($notifiable->name . '様')
            ->line('出品していた商品にコメントが届きました。')
            ->line('商品名: ' . $this->item->name)
            ->line('コメント: ' . $this->commentBody)
            ->action('コメントを確認する', url('/item/' . $this->item->id))
            ->line('返信をお待ちしています。');
    }
}
