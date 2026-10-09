<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Notifications\ItemSoldNotification;
use App\Notifications\PaymentCompletedNotification;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;

// Stripeからの通知を処理するためのクラス
class StripeWebhookController extends Controller
{
    // 最初に１回だけ動き、Stripeとやりとりする係を$stripeに入れる
    public function __construct(private StripeCheckoutService $stripe)
    {
    }

    // Stripeから通知が届いたときに動くメソッド
    public function handle(Request $request)
    {
        try {
            // Stripeからの通知が届いたら本物か確認する
            $event = $this->stripe->constructWebhookEvent(
                // 届いた通知の中身を取得
                $request->getContent(),
                // 通知についてくる署名の取得、無ければ空の文字列
                $request->header('Stripe-Signature', '')
            );
        // 通知が偽物の場合に移動
        } catch (\Exception $e) {
            // 400エラー(受け付けられない)を返す
            return response()->json(['error' => 'invalid signature'], 400);
        }

        // カード支払い完了または、コンビニ払い決済手続き通知の場合
        if ($event->type === 'checkout.session.completed') {
            // 注文情報を新しく作成
            $this->createOrder($event->data->object);
        }

        // 支払い手続きのIDを取得
        $stripeSessionId = $event->data->object->id;

        // コンビニで支払いが完了した場合
        if ($event->type === 'checkout.session.async_payment_succeeded') {
            // 支払い手続きのIDで注文を1件取得
            $order = Order::where('stripe_session_id', $stripeSessionId)->first();

            // 注文が見つかった場合
            if ($order) {
                // 注文を支払済みにして保存
                $order->update(['payment_status' => 'paid']);
                // 出品者に支払完了のため発送するよう通知する
                $order->item->user->notify(new PaymentCompletedNotification($order->item));
            }
        }

        // 支払いが失敗した場合、支払い手続きIDから注文を探し、支払失敗にして保存
        if ($event->type === 'checkout.session.async_payment_failed') {
            Order::where('stripe_session_id', $stripeSessionId)->update(['payment_status' => 'failed']);
        }
        // Stripeに通知の受取済みを知らせる
        return response()->json(['status' => 'ok']);
    }

    // 注文を新しく作成するメソッド
    private function createOrder(object $session): void
    {
        // 通知の商品IDから商品を1件取得
        $item = Item::find($session->metadata->item_id);

        // 商品が無い場合、売切れの場合(2回同じ通知がきたとき)、なにもしない
        if (!$item || $item->is_sold) {
            return;
        }

        // 商品を売切れにして保存
        $item->update(['is_sold' => true]);

        // 購入コントローラーで預けておいた注文メモから、新しく注文を作成して保存
        Order::create([
            'user_id'  => $session->metadata->user_id,
            'item_id'  => $item->id,
            'postcode' => $session->metadata->postcode,
            'address'  => $session->metadata->address,
            'building' => $session->metadata->building,
            'payment_status' => $session->payment_status,
            'stripe_session_id' => $session->id,
        ]);

        // 出品者に商品が売れた通知を送る
        $item->user->notify(new ItemSoldNotification($item));
    }
}
