<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

// 発送のお知らせの部品を扱うクラス
class ShippingNotice extends Component
{
    // 発送が必要な件数を入れておく
    public int $count;

    // 部品を作るとき発送が必要な件数を数えておくためのメソッド
    public function __construct()
    {
        // ログイン中のユーザーを取り出す
        $user = Auth::user();
        // ログインしていれば発送が必要な件数を数える。していなければ0
        $this->count = $user ? $user->unshippedOrdersCount() : 0;
    }

    // 部品の見た目に使う　bladeを返すメソッド
    public function render(): View|Closure|string
    {
        // components/shipping-notice.blade.phpを返す
        return view('components.shipping-notice');
    }
}
