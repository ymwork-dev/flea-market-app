<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
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
            // metadataには、Webhookが届いた時に注文を作るために必要な情報を入れておく。
            // Webhookはブラウザのセッションと無関係にStripeのサーバーから直接届くので、
            // sessionではなくmetadataに持たせておく必要がある。
            'metadata' => [
                'item_id' => $item->id,
                'user_id' => $user->id,
                'postcode' => $user->postcode,
                'address' => $user->address,
                'building' => $user->building ?? '',
            ],
            'success_url' => route('purchase.success', ['item_id' => $item->id]),
            'cancel_url'  => route('purchase.show', ['item_id' => $item->id]),
        ]);

        return redirect($session->url);
    }

    public function success($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // 「注文を作る」処理はWebhook(StripeWebhookController)側で行っている。
        // カード払いはWebhookの方が先に届くことが多いので、ここでは
        // すでに注文が出来ていれば購入完了メッセージを出すだけにする。
        // (コンビニ払いのお客様は、実はこの画面には戻ってこない。詳しくは
        //  Stripe公式ドキュメントの「Redirect to Stripe hosted voucher page」を参照)
        $order = $item->order;

        if ($order && $order->user_id === $user->id) {
            return redirect('/')->with('message', '商品を購入しました');
        }

        return redirect('/');
    }

    public function ship($item_id)
    {
        $item = Item::findOrFail($item_id);

        // 出品者本人以外は発送操作できない
        abort_if($item->user_id !== Auth::id(), 403);
        // 売れていない商品には発送も何もない
        abort_if(!$item->is_sold, 403);

        $order = $item->order;
        abort_if(!$order, 404);

        $order->update(['is_shipped' => true]);

        return redirect()->route('item.show', ['item_id' => $item->id])->with('message', '発送手続きが完了しました');
    }

    public function receive($item_id)
    {
        $item = Item::findOrFail($item_id);
        $order = $item->order;

        // この商品を買った本人以外は受け取り確認できない
        abort_if(!$order || $order->user_id !== Auth::id(), 403);
        // 発送される前に受け取り確認はできない
        abort_if(!$order->is_shipped, 403);

        $order->update(['is_received' => true]);

        return redirect()->route('item.show', ['item_id' => $item->id])->with('message', '受け取り確認をしました');
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
