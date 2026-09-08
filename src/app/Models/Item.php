<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','price','brand','description','img_url','condition','user_id','is_sold'
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'likes');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    // 商品が売れた時に作られる注文（1商品につき1件だけの想定）
    public function order()
    {
        return $this->hasOne(Order::class);
    }

    // 売れていて、支払いも確認できていて、まだ発送していない商品かどうか
    // (コンビニ払いは支払い確認前に「発送準備中」と出てしまわないよう、支払い済みかも見る)
    // $item->needs_shipping でアクセスできる(データベースに同名の列は無い)
    public function getNeedsShippingAttribute(): bool
    {
        return $this->is_sold
            && $this->order
            && $this->order->payment_status === 'paid'
            && !$this->order->is_shipped;
    }
}
