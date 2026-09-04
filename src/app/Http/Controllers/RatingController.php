<?php

namespace App\Http\Controllers;

use App\Http\Requests\RatingRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(RatingRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $order = $item->order;

        // この商品を買った本人以外は評価できない
        abort_if(!$order || $order->user_id !== Auth::id(), 403);
        // 受け取り確認をする前は評価できない
        abort_if(!$order->is_received, 403);
        // すでに評価済みなら、もう一度は評価できない
        abort_if($order->rating, 403);

        $order->rating()->create([
            'score' => $request->score,
            'comment' => $request->comment,
        ]);

        return redirect()->route('item.show', ['item_id' => $item->id])->with('message', '評価を送信しました');
    }
}
