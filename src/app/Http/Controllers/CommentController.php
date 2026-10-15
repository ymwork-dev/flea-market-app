<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Item;
use App\Notifications\CommentPostedNotification;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CommentRequest;

// コメント機能を処理するためのクラス
class CommentController extends Controller
{
    // コメントを保存し、出品者に通知するメソッド
    public function storeComment(CommentRequest $request, $item_id)
    {
        // 認証されたログインユーザーでない場合
        if (!Auth::check()) {
            // ログイン画面へ遷移
            return redirect()->route('login');
        }

        // 商品IDデータから1件の商品情報を取得
        $item = Item::findOrFail($item_id);
        // 商品へのコメントを作り、DBに保存
        $comment = $item->comments()->create([
            // コメントしたログイン中ユーザーのIDを入れる
            'user_id' => Auth::id(),
            // 入力されたコメントを入れる
            'comment' => $request->comment,
        ]);

        // 商品の出品者とコメントした人が別の人の場合
        if ($item->user_id !== Auth::id()) {
            // 出品者にコメントが届いた通知を送る
            $item->user->notify(new CommentPostedNotification($item, $comment->comment));
        }

        // メッセージと一緒に商品詳細画面に戻る
        return back()->with('message', 'コメントを投稿しました');
    }

    // 出品者からの、返信コメントを保存するメソッド
    public function storeReply(CommentRequest $request, $item_id, $comment_id)
    {
        // 商品IDから商品情報を1件取得
        $item = Item::findOrFail($item_id);

        // 商品の出品者とコメントの返信者が別の人の場合403エラー
        abort_if(Auth::id() !== $item->user_id, 403);

        // 返信先のコメントを、この商品のコメントの中から取り出す
        $parentComment = Comment::where('item_id', $item->id)->findOrFail($comment_id);

        // 商品にコメントを作りDBに保存
        $item->comments()->create([
            // 返信した出品者のユーザーIDを入れる
            'user_id' => Auth::id(),
            // 入力された返信コメントを入れる
            'comment' => $request->comment,
            // 返信先のコメントのIDを入れる
            'parent_id' => $parentComment->id,
        ]);

        // メッセージと一緒に商品詳細画面へ戻る
        return back()->with('message', '返信を投稿しました');
    }

}
