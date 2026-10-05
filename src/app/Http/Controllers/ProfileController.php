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
        // クエリパラメータ―から出品ページを取得
        $page = $request->query('page', 'sell');

        // 購入ページの場合、ユーザー情報と一緒に商品情報を取得
        if ($page === 'buy') {
            $items = Order::where('user_id', $user->id)
                    ->with('item')
                    ->get()
                    ->pluck('item');
        } else {
            $items = $user->items()->with('order')->get();
        }

        return view('mypage', compact('user','items', 'page'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(ProfileRequest $request)
    {
        $user = Auth::user();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profiles', 'public');
            $user->img_url = $path;
        }

        $user->fill([
            'name'     => $request->name,
            'postcode' => $request->postcode,
            'address'  => $request->address,
            'building' => $request->building,
        ])->save();

        return redirect('/')->with('message', 'プロフィールを設定しました。');
    }
}
