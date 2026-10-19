<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// 商品にコメントが届いたことを出品者にメールで知らせるクラス
class CommentPostedNotification extends Notification
{
    // 通知を裏で送る仕組みを使えるようにする(すぐに送っているので未使用)
    use Queueable;

    // 通知を作るとき商品とコメントの文を受取り覚えておくためのメソッド
    public function __construct(private Item $item, private string $commentBody)
    {
    }

    // どの方法で送るかきめるメソッド
    public function via(object $notifiable): array
    {
        // メールで送る
        return ['mail'];
    }

    // メールの中身をくみたてるメソッド
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
