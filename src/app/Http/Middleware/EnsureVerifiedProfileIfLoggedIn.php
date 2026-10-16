<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// メール認証と住所登録が完了しているかチェックするクラス
class EnsureVerifiedProfileIfLoggedIn
{
    // 未完了ならメール認証かプロフィール編集へ回すメソッド
    public function handle(Request $request, Closure $next)
    {
        // ログインユーザー情報の取得
        $user = Auth::user();

        //ログインユーザーの場合
        if ($user) {
            // メール認証完了していない場合
            if (!$user->hasVerifiedEmail()) {
                // メール認証画面へ移動させる
                return redirect()->route('verification.notice');
            }

            // 郵便番号が登録されていない場合
            if (empty($user->postcode)) {
                // プロフィール編集画面へ移動させる
                return redirect('/mypage/profile');
            }
        }

        // チェック通過、次のコントローラーへ進む
        // ログインしていない人もそのまま通り、商品一覧・詳細が見られる
        return $next($request);
    }
}
