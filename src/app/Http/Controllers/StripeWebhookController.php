<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Notifications\ItemSoldNotification;
use App\Notifications\PaymentCompletedNotification;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    public function __construct(private StripeCheckoutService $stripe)
    {
    }

    // Stripeから「支払いの状況が変わりました」という通知が届いた時に呼ばれる
    public function handle(Request $request)
    {
        try {
            // 署名を確認して、本当にStripeから送られたリクエストかを確かめる
            $event = $this->stripe->constructWebhookEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', '')
            );
        } catch (\Exception $e) {
            // 署名が不正な場合は処理せず、400を返して終わる
            return response()->json(['error' => 'invalid signature'], 400);
        }

        // コンビニ払いのお客様は購入手続き完了後、アプリの画面には戻ってこない
        // (Stripeが用意した支払い用紙のページに留まる)ので、
        // 「注文を作る」処理は success_url のページではなく、ここ(Webhook)で行う。
        // カード払い・コンビニ払いのどちらでも、手続きが終わると必ずこのイベントが届く。
        if ($event->type === 'checkout.session.completed') {
            $this->createOrder($event->data->object);
        }

        $stripeSessionId = $event->data->object->id;

        if ($event->type === 'checkout.session.async_payment_succeeded') {
            $order = Order::where('stripe_session_id', $stripeSessionId)->first();

            if ($order) {
                $order->update(['payment_status' => 'paid']);
                // コンビニ払いの支払いが確認できたので、出品者に発送をお願いするメールを送る
                $order->item->user->notify(new PaymentCompletedNotification($order->item));
            }
        }

        if ($event->type === 'checkout.session.async_payment_failed') {
            Order::where('stripe_session_id', $stripeSessionId)->update(['payment_status' => 'failed']);
        }

        return response()->json(['status' => 'ok']);
    }

    // Stripeの決済セッションの情報から、注文を作る
    private function createOrder(object $session): void
    {
        $item = Item::find($session->metadata->item_id);

        // Webhookは同じ内容が2回届くことがあるので、
        // すでに売却済みなら何もしない(二重に注文を作らないため)
        if (!$item || $item->is_sold) {
            return;
        }

        $item->update(['is_sold' => true]);

        Order::create([
            'user_id'  => $session->metadata->user_id,
            'item_id'  => $item->id,
            'postcode' => $session->metadata->postcode,
            'address'  => $session->metadata->address,
            'building' => $session->metadata->building,
            'payment_status' => $session->payment_status,
            'stripe_session_id' => $session->id,
        ]);

        // 出品者に「商品が売れました」というメールを送る
        $item->user->notify(new ItemSoldNotification($item));
    }
}
