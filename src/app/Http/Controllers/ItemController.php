<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Http\Requests\ItemSearchRequest;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    // トップページの商品一覧を表示
    public function index(ItemSearchRequest $request)
    {
        // ログインユーザーの情報を取り出す。未ログインは空
        $user = Auth::user();

        // リクエストされたタブの取得
        $tab = $request->getTab();
        // リクエストされたキーワードの取得
        $keyword = $request->getKeyword();
        // 商品を準備
        $query = Item::query();

        // マイリストのタブが選ばれた場合
        if ($tab === 'mylist') {
            // ログインしていれば、いいねした商品を準備
            if ($user) {
                $query = $user->likedItems();
            // 未ログインなら空を返す準備
            } else {
                $query->where('id', 0);
            }
        }

        // キーワード検索の場合、商品名にキーワードを含む商品に絞る
        if ($keyword) {
            $query->where('name', 'LIKE', '%' . $keyword . '%');
        }

        // アイテム情報の取得
        $items = $query->get();

        // 商品とタブ、キーワードを表示
        return view('index', [
            'items' => $items,
            'tab' => $tab,
            'keyword' => $keyword
        ]);
    }

    // 商品情報の表示
    public function show($item_id)
    {
        // 指定された商品情報を1件取得
        $item = Item::findOrFail($item_id);
        // ユーザー情報を取得
        $user = Auth::user();

        // 出品した商品か、購入した商品か、いいねした商品か確認
        $isOwner = $user && $user->id === $item->user_id;
        $isBuyer = $user && $item->order && $item->order->user_id === $user->id;
        $isLiked = $user && $user->likedItems->contains($item->id);

        // 支払い済みか、発送済みか、受取済みか、評価済みかの確認
        $isPaid = $item->order && $item->order->payment_status === 'paid';
        $isShipped = $item->order && $item->order->is_shipped;
        $isReceived = $item->order && $item->order->is_received;
        $rating = $item->order ? $item->order->rating : null;

        // 出品者のこれまでの評価カウントと、評価平均値を取得
        $sellerRatingsCount = $item->user->receivedRatingsCount();
        $sellerRatingsAverage = $item->user->receivedRatingsAverage();

        // 商品のコメント情報取得。(コメントした人情報・出品者情報も一緒に取得)
        $comments = $item->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->get();

        // 商品詳細情報の表示(商品情報・出品者情報・購入者情報
        // いいね・コメント・支払い・発送・受取・評価・評価カウント・評価平均値)
        return view('item_detail', compact(
            'item', 'isOwner', 'isBuyer', 'isLiked', 'comments',
            'isPaid', 'isShipped', 'isReceived', 'rating',
            'sellerRatingsCount', 'sellerRatingsAverage'
        ));
    }
}
