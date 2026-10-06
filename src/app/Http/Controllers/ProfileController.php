<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// ProfileControllerクラスは、プロフィールに関連する操作を処理するコントローラー
class ProfileController extends Controller
{
    // indexメソッドは、プロフィールページを表示するためのメソッド
    public function index(Request $request)
    {
        // 認証されているユーザー情報を取得
        $user = Auth::user();
        // クエリパラメータ―からページの種類を取得。無いときは出品した商品ページにする
        $page = $request->query('page', 'sell');

        // 購入した商品のページの場合、自分の注文情報を商品と一緒に取り出し、商品情報だけを取り出す
        if ($page === 'buy') {
            $items = Order::where('user_id', $user->id)
                    ->with('item')
                    ->get()
                    ->pluck('item');
        } else {
            // 出品した商品ページの場合、自分の出品した商品情報と一緒に注文情報を取り出す
            $items = $user->items()->with('order')->get();
        }

        // マイページにユーザー情報と商品情報のページを表示する
        return view('mypage', compact('user','items', 'page'));
    }

    // editメソッドは、プロフィール編集ページを表示するためのメソッド
    public function edit()
    {
        // 認証されているユーザー情報を取得
        $user = Auth::user();
        // プロフィール編集ページにユーザー情報を渡して表示する
        return view('profile.edit', compact('user'));
    }

    // updateメソッドは、プロフィール情報を更新するためのメソッド
    public function update(ProfileRequest $request)
    {
        // 認証されているユーザー情報を取得
        $user = Auth::user();

        // もし画像のアップロードリクエストがあれば、保存し、ユーザー画像を更新
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profiles', 'public');
            $user->img_url = $path;
        }

        // ユーザー情報を画像と一緒にデーターベースへ保存
        $user->fill([
            'name'     => $request->name,
            'postcode' => $request->postcode,
            'address'  => $request->address,
            'building' => $request->building,
        ])->save();

        // プロフィール更新メッセージと一緒にトップページにリダイレクトする
        return redirect('/')->with('message', 'プロフィールを設定しました。');
    }
}
