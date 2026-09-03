<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;

// Stripeへの問い合わせをこのクラスにまとめておくことで、
// テストの時だけ本物のStripeの代わりに「偽物」に差し替えられるようにする。
class StripeCheckoutService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // 戻り値の型を object にしているのは、テストの時に本物の
    // Stripe\Checkout\Session ではなく、必要なプロパティ（status, metadata）
    // だけを持つ偽物のオブジェクトに差し替えられるようにするため。
    public function createSession(array $params): object
    {
        return Session::create($params);
    }

    // Webhookで届いたリクエストが、本当にStripeから送られたものかを
    // 署名（$sigHeader）を使って確認する。改ざんされていたり、
    // Stripe以外から送られた偽物のリクエストだとここで例外が発生する。
    public function constructWebhookEvent(string $payload, string $sigHeader): object
    {
        return Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.stripe.webhook_secret')
        );
    }
}
