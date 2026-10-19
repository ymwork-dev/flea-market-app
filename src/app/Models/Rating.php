<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// 評価テーブルを扱うクラス
class Rating extends Model
{
    // まとめて保存してよい列　注文ＩＤ・評価点・コメント
    protected $fillable = ['order_id', 'score', 'comment'];

    // 注文情報をコントローラーや画面で取得するためのメソッド
    public function order()
    {
        // 注文情報は1件
        return $this->belongsTo(Order::class);
    }
}
