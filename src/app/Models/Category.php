<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// カテゴリテーブルを扱うクラス
class Category extends Model
{
    // ダミーデータを作成準備
    use HasFactory;

    // カテゴリ名をまとめて保存してよい列　カテゴリ名
    protected $fillable = ['name'];

    // カテゴリ別の商品一覧をコントローラーや画面で取得するためのメソッド
    public function items()
    {
        // 商品一覧はたくさん
        return $this->belongsToMany(Item::class);
    }
}
