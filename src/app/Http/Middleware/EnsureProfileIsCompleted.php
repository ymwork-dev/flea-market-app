<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// 出品・購入・マイページなど、ログインが必要な画面で、
// プロフィール（住所）が登録済みかチェックするクラス
class EnsureProfileIsCompleted
{
    // 住所未登録の場合、プロフィール編集へ回すメソッド
    public function handle(Request $request, Closure $next)
    {
        // ログインユーザ情報の取得(メール認証はルートについてる)
        $user = Auth::user();

        // ログインユーザーの郵便番号が空の場合
        if ($user && empty($user->postcode)) {
            // プロフィール編集画面へ移動させる
            return redirect()->route('profile.edit');
        }

        // チェック通過、次のコントローラーへ進む
        return $next($request);
    }
}
