<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// 注文テーブルを扱うクラス
class Order extends Model
{
    // ダミーデータの作成準備
    use HasFactory;

    // まとめて保存してはいけない列(空なので全て良い)
    protected $guarded = [];

    // 商品情報をコントローラーや画面で取得するためのメソッド
    public function item()
    {
        // 商品情報は1件
        return $this->belongsTo(Item::class);
    }

    // 注文の購入者情報をコントローラーや画面で取得するためのメソッド
    public function user()
    {
        // ユーザー情報は1件
        return $this->belongsTo(User::class);
    }

    // 評価情報をコントローラーや画面で取得するためのメソッド
    public function rating()
    {
        // 評価情報は1件
        return $this->hasOne(Rating::class);
    }
}
