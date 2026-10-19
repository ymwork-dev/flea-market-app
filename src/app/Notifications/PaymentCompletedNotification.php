<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// コンビニ払いの支払い確認ができた時に出品者へメールで知らせるクラス
class PaymentCompletedNotification extends Notification
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
            ->subject('お支払いが確認できました')
            ->greeting($notifiable->name . '様')
            ->line('コンビニ払いのお支払いが確認できました。')
            ->line('商品名: ' . $this->item->name)
            ->action('発送手続きをする', url('/item/' . $this->item->id))
            ->line('マイページから発送手続きをお願いします。');
    }
}
