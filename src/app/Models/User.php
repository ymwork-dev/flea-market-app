<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// ログインできて、メール認証が必要なユーザーテーブルを扱うクラス
class User extends Authenticatable implements MustVerifyEmail
{
    // ダミーデータ作成と通知送信の準備
    use HasFactory, Notifiable;

    // デモ用アカウントの2つのメールアドレス
    public const DEMO_EMAILS = ['test@example.com', 'demo@example.com'];

    // デモアカウントか調べるメソッド
    public function isDemoAccount(): bool
    {
        // デモアカウントか調べ、2つのメールアドレスのどちらかならtrue
        return in_array($this->email, self::DEMO_EMAILS, true);
    }

    // まとめて保存して良い列
    // ユーザー名・メールアドレス・パスワード・郵便番号・住所・建物名
    protected $fillable = [
        'name',
        'email',
        'password',
        'postcode',
        'address',
        'building',
    ];

    // まとめて使うときに隠す列(パスワード・トークン)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // DBから取得するときの形を決める
    protected function casts(): array
    {
        // メール認証は日時として扱う・パスワードはハッシュ化
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // いいねした商品を扱うメソッド
    public function likedItems()
    {
        // いいねはたくさん
        return $this->belongsToMany(Item::class, 'likes');
    }

    // 出品した商品をコントローラーや画面で取得するためのメソッド
    public function items()
    {
        // 商品はたくさん
        return $this->hasMany(Item::class);
    }

    // 発送が必要な注文の件数を数えるメソッド
    public function unshippedOrdersCount(): int
    {
        // 出品者は自分・支払済み・未発送の注文を数える
        return Order::whereHas('item', function ($query) {
            $query->where('user_id', $this->id);
        })->where('payment_status', 'paid')->where('is_shipped', false)->count();
    }

    // 出品者としてもらった評価件数を数えるメソッド
    public function receivedRatingsCount(): int
    {
        // 評価・注文・商品をたどり、出品者の評価を数える
        return Rating::whereHas('order.item', function ($query) {
            $query->where('user_id', $this->id);
        })->count();
    }

    // 評価スコアの平均値を扱うメソッド
    public function receivedRatingsAverage(): ?float
    {
        // 評価・注文・商品をたどり、スコア平均値を計算
        return Rating::whereHas('order.item', function ($query) {
            $query->where('user_id', $this->id);
        })->avg('score');
    }
}
