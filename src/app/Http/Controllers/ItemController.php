<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Http\Requests\ItemSearchRequest;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index(ItemSearchRequest $request)
    {
        $user = Auth::user();

        $tab = $request->getTab();
        $keyword = $request->getKeyword();
        $query = Item::query();

        if ($tab === 'mylist') {
            if ($user) {
                $query = $user->likedItems()->where('items.user_id', '!=', $user->id);
            } else {
                $query->where('id', 0);
            }
        } else {
            if ($user) {
                $query->where('user_id', '!=', $user->id);
            }
        }

        if ($keyword) {
            $query->where('name', 'LIKE', '%' . $keyword . '%');
        }

        $items = $query->get();

        return view('index', [
            'items' => $items,
            'tab' => $tab,
            'keyword' => $keyword
        ]);
    }

    public function show($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // 自分が出品した商品か / 自分が買った商品か をここで判定し、
        // ビューには結果だけを渡す（判定ロジックをビューに書かないため）
        $isOwner = $user && $user->id === $item->user_id;
        $isBuyer = $user && $item->order && $item->order->user_id === $user->id;
        $isLiked = $user && $user->likedItems->contains($item->id);

        // 返信(parent_idがある方)は除いた、質問コメントだけを取得する
        // 各コメントの投稿者・返信・返信の投稿者もまとめて取得しておく(N+1問題防止)
        $comments = $item->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->get();

        return view('item_detail', compact('item', 'isOwner', 'isBuyer', 'isLiked', 'comments'));
    }
}
