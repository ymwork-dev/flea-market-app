<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// デモログイン機能を定義するクラス
class DemoLoginController extends Controller
{
    // デモログインボタンの種類とデモアカウントメールアドレスの組み合わせ
    private const DEMO_EMAILS_BY_TYPE = [
        'seller' => 'test@example.com',
        'buyer' => 'demo@example.com',
    ];

    // デモ用ログイン機能を定義するメソッド
    public function login(string $type)
    {
        // 種類が購入者か出品者でなければ404エラー
        abort_unless(array_key_exists($type, self::DEMO_EMAILS_BY_TYPE), 404);

        // 種類に合うメールアドレスのユーザーをDBから取得、なければ404エラー
        $user = User::where('email', self::DEMO_EMAILS_BY_TYPE[$type])->firstOrFail();
        // パスワードなしで、そのユーザーとしてログインできる
        Auth::login($user);

        // ログインしたら商品一覧画面へ遷移
        return redirect()->route('item.index');
    }
}
