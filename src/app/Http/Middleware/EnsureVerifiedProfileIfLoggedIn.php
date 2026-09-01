<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// ログインしていない人（ゲスト）はそのまま通す。
// ログイン済みの人だけ、メール認証と住所登録が済んでいるかを確認する。
class EnsureVerifiedProfileIfLoggedIn
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            if (empty($user->postcode)) {
                return redirect('/mypage/profile');
            }
        }

        return $next($request);
    }
}
