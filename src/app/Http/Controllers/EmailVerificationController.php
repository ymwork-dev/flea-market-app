<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

// メール認証を完了させるためのクラス
class EmailVerificationController extends Controller
{
    // 認証はこちらボタンで、メール認証完了させるメソッド
    public function completeVerification()
    {
        // ログインユーザー情報を取得
        $user = Auth::user();

        // ログインしていてまだメール認証していない場合
        if ($user && !$user->hasVerifiedEmail()) {
            // メール認証済みにする
            $user->markEmailAsVerified();
        }
        // マイページへ移動
        return redirect()->route('mypage');
    }
}
