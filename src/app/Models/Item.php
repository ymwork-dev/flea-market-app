<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// 商品テーブルを扱うクラス
class Item extends Model
{
    // ダミーデータ機能読み込み
    use HasFactory;

    // まとめて保存して良い列
    // (商品名、価格、ブランド名、商品説明、画像、コンディション、出品者ID、売切れか)
    protected $fillable = [
        'name','price','brand','description','img_url','condition','user_id','is_sold'
    ];

    // この商品のコメント一覧を取り出せるようにする
    public function comments()
    {
        // たくさん
        return $this->hasMany(Comment::class);
    }

    // この商品にいいねした人の一覧を取り出せるようにする
    public function likedByUsers()
    {
        // たくさん
        return $this->belongsToMany(User::class, 'likes');
    }

    // この商品の出品者を取り出せるようにする
    public function user()
    {
        // 一人
        return $this->belongsTo(User::class);
    }

    // この商品のカテゴリ一覧を取り出せるようにする
    public function categories()
    {
        // たくさん 
        return $this->belongsToMany(Category::class);
    }

    // この商品の注文を取り出せるようにする(売れた時に1つ)
    public function order()
    {
        // 1つ
        return $this->hasOne(Order::class);
    }

    // 発送が必要な商品か調べる(マイページの表示)
    public function getNeedsShippingAttribute(): bool
    {
        // 売れている・注文がある・支払済み・未だ発送していない、の全部ならtrue
        return $this->is_sold
            && $this->order
            && $this->order->payment_status === 'paid'
            && !$this->order->is_shipped;
    }
}
