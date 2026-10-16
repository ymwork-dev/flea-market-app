<?php

namespace App\Http\Controllers;

use App\Models\Item;

// いいね機能を処理するためのクラス
class LikeController extends Controller
{
    // いいねを付ける・外すを切り替えるメソッド
    public function toggleLike($item_id)
    {
        // ログインユーザーの情報を取得
        $user = auth()->user();
        // ログインユーザーでない場合
        if (!$user) {
            // 401エラーでメッセージを返す
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // いいねが付いてれば外し、付いてなければ付ける
        $user->likedItems()->toggle($item_id);

        // 切り替えた後、自分のいいねの中にこの商品があるか調べる
        $is_liked = $user->likedItems()->where('item_id', $item_id)->exists();

        // お気に入り登録数を数える
        $like_count = \App\Models\Item::findOrFail($item_id)->likedByUsers()->count();

        // お気に入り登録とカウント数を返す
        return response()->json([
            'is_liked' => $is_liked,
            'like_count' => $like_count
        ]);
    }
}

