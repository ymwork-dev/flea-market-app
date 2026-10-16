<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// デモアカウントのプロフィール編集をチェックするクラス
class RestrictDemoAccount
{
    // デモアカウントならプロフィール更新を止めるメソッド
    public function handle(Request $request, Closure $next): Response
    {
        // ログインユーザー情報取得
        $user = Auth::user();

         // デモアカウントのプロフィール画面の更新を押した場合403エラー
        abort_if($user && $user->isDemoAccount(), 403, 'デモアカウントのプロフィールは変更できません。');

        // チェック通過、次のコントローラーへ進む
        return $next($request);
    }
}
