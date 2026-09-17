<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    // 本来はメール本文のリンクを踏んで認証する想定だが、動作確認をしやすくするため、
    // ボタン操作だけでメール認証を完了させて次のページへ進めるようにしている
    public function completeVerification()
    {
        $user = Auth::user();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->route('mypage');
    }
}
