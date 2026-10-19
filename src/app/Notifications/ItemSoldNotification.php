<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// 商品が売れた時に出品者へメールで知らせるクラス
class ItemSoldNotification extends Notification
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
        $mail = (new MailMessage)
            ->subject('商品が売れました')
            ->greeting($notifiable->name . '様')
            ->line('出品していた商品が購入されました。')
            ->line('商品名: ' . $this->item->name)
            ->line('価格: ¥' . number_format($this->item->price))
            ->action('商品を確認する', url('/item/' . $this->item->id));

        // 支払済み(カード払い)の場合、発送のお願いで終わる
        if ($this->item->order->payment_status === 'paid') {
            return $mail->line('マイページから発送手続きをお願いします。');
        }

        // まだ支払われていない(コンビニ払い)場合
        return $mail->line('コンビニ払いのお支払いが確認できたら、改めてお知らせします。');
    }
}
