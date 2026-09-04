<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // すべてのカラムの保存を許可する
    protected $guarded = [];

    // 商品とのリレーション（任意ですがあると便利です）
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // この取引に対する評価（購入者が出品者に付けたもの）
    // まだ評価されていなければ null になる
    public function rating()
    {
        return $this->hasOne(Rating::class);
    }
}
