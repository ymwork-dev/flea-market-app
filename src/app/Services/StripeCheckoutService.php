<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;

// Stripe決済サービスの処理を定義するクラス
class StripeCheckoutService
{
    // このクラスが作られたとき、1回だけ動くメソッド
    public function __construct()
    {
        // 秘密のキーを設定
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // Stripeに支払画面を作ってもらうメソッド
    public function createSession(array $params): object
    {
        // 支払画面を作ってもらい、結果を返す
        return Session::create($params);
    }

    // 届いた通知が本当にStripeから届いた通知か確認するメソッド
    public function constructWebhookEvent(string $payload, string $sigHeader): object
    {
        // 中身・署名・秘密のキーで確かめ、正しければ中身を返す。
        return Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.stripe.webhook_secret')
        );
    }
}
