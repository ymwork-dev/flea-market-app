<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'postcode',
        'address',
        'building',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function likedItems()
    {
        return $this->belongsToMany(Item::class, 'likes');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function purchasedItems()
    {
        return $this->belongsToMany(Item::class, 'orders', 'user_id', 'item_id');
    }

    // 自分が出品した商品のうち、売れたけどまだ発送していないものの件数
    public function unshippedOrdersCount(): int
    {
        return Order::whereHas('item', function ($query) {
            $query->where('user_id', $this->id);
        })->where('is_shipped', false)->count();
    }

    // Ratingテーブルには item_id が無いので、'order.item' という書き方で
    // Rating → Order → Item と2段階たどり、その先の Item.user_id で
    // 「出品者ごとの評価」を絞り込んでいる。
    // 出品者として、これまでに購入者から受け取った評価の件数
    public function receivedRatingsCount(): int
    {
        return Rating::whereHas('order.item', function ($query) {
            $query->where('user_id', $this->id);
        })->count();
    }

    // 出品者として、これまでに購入者から受け取った評価の平均点(1件も無ければnull)
    public function receivedRatingsAverage(): ?float
    {
        return Rating::whereHas('order.item', function ($query) {
            $query->where('user_id', $this->id);
        })->avg('score');
    }
}
