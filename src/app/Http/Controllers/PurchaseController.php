<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;
use App\Services\StripeCheckoutService;

class PurchaseController extends Controller
{
    public function __construct(private StripeCheckoutService $stripe)
    {
    }

    public function showPurchasePage($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        if (session('last_item_id') != $item_id) {
            session()->forget('payment_method');
        }

        session(['last_item_id' => $item_id]);

        return view('purchase', compact('item', 'user'));
    }

    public function purchase(PurchaseRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // 購入画面で選んだ支払い方法を、Stripeに渡す種類に変換する。
        // 「コンビニ払い」が選ばれた時だけ konbini にし、それ以外（カード支払い）は card にする。
        $paymentMethodTypes = $request->payment_method === 'コンビニ払い' ? ['konbini'] : ['card'];

        // success_url に {CHECKOUT_SESSION_ID} と書いておくと、
        // Stripeが決済完了後にこの部分を本物のセッションIDに置き換えてくれる。
        // metadataには「どの商品・どのユーザーの購入か」を記録しておき、
        // 決済確認のときに商品IDとユーザーIDが一致するかどうかの照合に使う。
        $session = $this->stripe->createSession([
            'payment_method_types' => $paymentMethodTypes,
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'jpy',
                    'product_data' => ['name' => $item->name],
                    'unit_amount'  => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'metadata' => [
                'item_id' => $item->id,
                'user_id' => $user->id,
            ],
            'success_url' => route('purchase.success', ['item_id' => $item->id]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('purchase.show', ['item_id' => $item->id]),
        ]);

        session(['shipping_address' => [
            'postcode' => $user->postcode,
            'address'  => $user->address,
            'building' => $user->building,
        ]]);

        return redirect($session->url);
    }

    public function success(Request $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // すでに購入済みなら、再アクセスされても二重に注文を作らない
        if ($item->is_sold) {
            return redirect('/')->with('message', '商品を購入しました');
        }

        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            abort(403, '不正なアクセスです');
        }

        // Stripeに直接問い合わせて、本当に決済が完了しているか・
        // 自分がこれから購入しようとしていた商品と一致しているかを確認する
        $checkoutSession = $this->stripe->retrieveSession($sessionId);

        if (
            $checkoutSession->status !== 'complete' ||
            (int) $checkoutSession->metadata->item_id !== (int) $item->id ||
            (int) $checkoutSession->metadata->user_id !== (int) $user->id
        ) {
            abort(403, '決済が確認できませんでした');
        }

        $shipping = session('shipping_address');

        $item->update(['is_sold' => true]);

        Order::create([
            'user_id'  => $user->id,
            'item_id'  => $item->id,
            'postcode' => $shipping['postcode'] ?? $user->postcode,
            'address'  => $shipping['address'] ?? $user->address,
            'building' => $shipping['building'] ?? $user->building,
        ]);

        session()->forget(['payment_method', 'shipping_address']);

        return redirect('/')->with('message', '商品を購入しました');
    }

    public function editAddress($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();
        return view('purchase_address', compact('item', 'user'));
    }

    public function updateAddress(\App\Http\Requests\AddressRequest $request, $item_id)
    {
        $user = Auth::user();
        $user->update([
            'postcode' => $request->postcode,
            'address'  => $request->address,
            'building' => $request->building,
        ]);

        return redirect()->route('purchase.show', ['item_id' => $item_id]);
    }

    public function storePaymentSession(Request $request)
    {
        session(['payment_method' => $request->payment_method]);
        return response()->json(['success' => true]);
    }
}
