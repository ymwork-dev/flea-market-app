<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Item;
use App\Notifications\CommentPostedNotification;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CommentRequest;

class CommentController extends Controller
{
    public function storeComment(CommentRequest $request, $item_id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $item = Item::findOrFail($item_id);
        $comment = $item->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        // 自分の商品に自分でコメントすることは無いはずだが、念のため
        // 出品者本人がコメントした時は自分宛てに通知を送らないようにする
        if ($item->user_id !== Auth::id()) {
            $item->user->notify(new CommentPostedNotification($item, $comment->comment));
        }

        return back()->with('message', 'コメントを投稿しました');
    }

    public function storeReply(CommentRequest $request, $item_id, $comment_id)
    {
        $item = Item::findOrFail($item_id);

        // 出品者本人だけが返信できる
        abort_if(Auth::id() !== $item->user_id, 403);

        // 返信先のコメントが、この商品に対するコメントであることを確認する
        $parentComment = Comment::where('item_id', $item->id)->findOrFail($comment_id);

        $item->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment,
            'parent_id' => $parentComment->id,
        ]);

        return back()->with('message', '返信を投稿しました');
    }

}
