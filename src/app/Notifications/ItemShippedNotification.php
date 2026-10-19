<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// 商品が発送されたことを購入者へメールで知らせるクラス
class ItemShippedNotification extends Notification
{
    // 通知を裏で送る仕組みを使えるようにする(すぐに送っているので未使用)
    use Queueable;

    // 通知を作るとき商品情報を受取り覚えておくためのメソッド
    public function __construct(private Item $item)
    {
    }

    // どの方法で送るか決めるメソッド
    public function via(object $notifiable): array
    {
        // メールで送る
        return ['mail'];
    }

    // メールの中身を組み立てるメソッド
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
