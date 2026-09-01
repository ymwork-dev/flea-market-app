<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session;

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

    public function retrieveSession(string $sessionId): object
    {
        return Session::retrieve($sessionId);
    }
}
