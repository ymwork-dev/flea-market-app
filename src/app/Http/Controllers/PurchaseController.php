<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\RatingRequest;
use App\Notifications\ItemShippedNotification;
use App\Services\StripeCheckoutService;
use App\Http\Requests\AddressRequest;

//  購入に関する処理を行うコントローラー
class PurchaseController extends Controller
{
    // 最初に1回だけ動き、Stripeとやりとりする係を$stripeに入れる
    public function __construct(private StripeCheckoutService $stripe)
    {
    }

    // 購入ページを表示するためのメソッド
    public function showPurchasePage($item_id)
    {
        // 商品IDから商品を1件取得
        $item = Item::findOrFail($item_id);
        // ログイン中のユーザー情報を取得
        $user = Auth::user();

        // ユーザーが出品した商品の購入は403エラー
        abort_if($item->user_id === $user->id, 403, '自分が出品した商品は購入できません。');

        // 前に見ていた商品と違う商品なら、セッションに残っている支払方法を消す
        if (session('last_item_id') != $item_id) {
            session()->forget('payment_method');
        }

        // 今見ている商品のIDをセッションに残す
        session(['last_item_id' => $item_id]);

        // 購入画面に商品とユーザー情報を渡して表示
        return view('purchase', compact('item', 'user'));
    }

    // Stripeの支払画面へ移動させるメソッド
    public function purchase(PurchaseRequest $request, $item_id)
    {
        // 商品IDから商品情報を1件取得
        $item = Item::findOrFail($item_id);
        // ログイン中ユーザーの情報を取得
        $user = Auth::user();

        // 選択した支払方法をStripeの言葉に置き換え
        $paymentMethodTypes = $request->payment_method === 'コンビニ払い' ? ['konbini'] : ['card'];

        // Stripeに支払のセッションを作ってもらう
        $session = $this->stripe->createSession([
            // 支払方法の選択
            'payment_method_types' => $paymentMethodTypes,
            'line_items' => [[
                // 商品価格
                'price_data' => [
                    // 日本円
                    'currency'     => 'jpy',
                    // 商品名
                    'product_data' => ['name' => $item->name],
                    // 商品情報に価格を設定
                    'unit_amount'  => $item->price,
                ],
                // 商品数
                'quantity' => 1,
            ]],
            // 1回きりの支払い
            'mode' => 'payment',
            // 支払完了後に保存するため、支払い前に注文メモをStripeに預けておく
            'metadata' => [
                'item_id' => $item->id,
                'user_id' => $user->id,
                'postcode' => $user->postcode,
                'address' => $user->address,
                'building' => $user->building ?? '',
            ],
            // 購入が成功したら購入完了画面へ
            'success_url' => route('purchase.success', ['item_id' => $item->id]),
            // 購入をキャンセルしたら購入画面へ
            'cancel_url'  => route('purchase.show', ['item_id' => $item->id]),
        ]);

        // Stripeの支払画面へ移動する
        return redirect($session->url);
    }

    // 商品購入完了後のメソッド
    public function success($item_id)
    {
        // 商品IDから商品情報を1件取得
        $item = Item::findOrFail($item_id);
        // ログイン中ユーザーの情報を取得
        $user = Auth::user();

        // この商品の注文を取得
        $order = $item->order;

        // 注文があり、ログイン中ユーザーの注文の場合、メッセージと一緒にトップ画面へリダイレクト
        if ($order && $order->user_id === $user->id) {
            return redirect('/')->with('message', '商品を購入しました');
        }

        // そうでない場合は、メッセージは無く、トップ画面へリダイレクト
        return redirect('/');
    }

    // 発送に関するメソッド
    public function ship($item_id)
    {
        // 商品IDから商品情報を1件取得
        $item = Item::findOrFail($item_id);

        // 商品情報のユーザーがログインユーザーでない場合403エラー
        abort_if($item->user_id !== Auth::id(), 403);
        // 売れてない商品情報の場合、403エラー
        abort_if(!$item->is_sold, 403);

        // 商品の注文を取得
        $order = $item->order;
        // 注文がない場合、404エラー
        abort_if(!$order, 404);

        // 注文を発送済みにして保存
        $order->update(['is_shipped' => true]);
        // 発送されましたの通知メールを送る
        $order->user->notify(new ItemShippedNotification($item));

        // メッセージと一緒に商品詳細画面へリダイレクト
        return redirect()->route('item.show', ['item_id' => $item->id])->with('message', '発送手続きが完了しました');
    }

    // 商品受取りに関するメソッド
    public function receive(RatingRequest $request, $item_id)
    {
        // 商品IDから商品情報を1件取得
        $item = Item::findOrFail($item_id);
        // 商品の注文を取得
        $order = $item->order;

        // 注文が無い場合、又はログイン中ユーザーの注文でない場合、403エラー
        abort_if(!$order || $order->user_id !== Auth::id(), 403);
        // 発送済みでない場合403エラー
        abort_if(!$order->is_shipped, 403);
        // 受取済みの場合403エラー
        abort_if($order->is_received, 403);

        // 注文を受取済みにして保存
        $order->update(['is_received' => true]);
        // 評価とコメントを保存
        $order->rating()->create([
            'score' => $request->score,
            'comment' => $request->comment,
        ]);

        // メッセージと一緒に商品詳細ページにリダイレクト
        return redirect()->route('item.show', ['item_id' => $item->id])->with('message', '受け取り評価を送信しました');
    }

    // 送付先住所に関するメソッド
    public function editAddress($item_id)
    {
        // 商品IDから商品情報を1件取得
        $item = Item::findOrFail($item_id);
        // ログイン中ユーザーの情報を取得
        $user = Auth::user();
        // 住所変更画面に商品とユーザー情報を渡して表示
        return view('purchase_address', compact('item', 'user'));
    }

    // 購入者の住所変更に関するメソッド
    public function updateAddress(AddressRequest $request, $item_id)
    {
        // ログイン中のユーザー情報を取得
        $user = Auth::user();
        // 郵便番号・住所・建物名の更新
        $user->update([
            'postcode' => $request->postcode,
            'address'  => $request->address,
            'building' => $request->building,
        ]);

        // 購入画面へリダイレクト
        return redirect()->route('purchase.show', ['item_id' => $item_id]);
    }

    // 支払方法をセッションに保存するメソッド
    public function storePaymentSession(Request $request)
    {
        // 選択した支払方法をセッションに保存
        session(['payment_method' => $request->payment_method]);

        // 選択した支払方法を覚えたという返事を返す
        return response()->json(['success' => true]);
    }
}
