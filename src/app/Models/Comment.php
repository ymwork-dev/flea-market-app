<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// コメントテーブルを扱うクラス
class Comment extends Model
{
    // ダミーデータを作成準備
    use HasFactory;

    // まとめて保存してよい列
    // コメントしたユーザーID・商品ID・コメント文・返信先のコメントID
    protected $fillable = [
        'user_id',
        'item_id',
        'comment',
        'parent_id',
    ];

    // コメントしたユーザーの情報をコントローラーや画面で取得するためのメソッド
    public function user()
    {
        // コメントしたユーザー情報は一人
        return $this->belongsTo(User::class);
    }

    // 商品情報をコントローラーや画面で取得するためのメソッド
    public function item()
    {
        // 商品情報は一つ
        return $this->belongsTo(Item::class);
    }

    // コメントの返信一覧をコントローラーや画面で取得するためのメソッド
    public function replies()
    {
        // コメントの返信一覧はたくさん
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
