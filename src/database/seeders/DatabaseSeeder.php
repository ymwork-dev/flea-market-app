<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

// 最初のデータ(デモアカウントと商品)をDBに入れるクラス
class DatabaseSeeder extends Seeder
{
    // シーダ―を動かした時に実行されるメソッド
    public function run(): void
    {
        // 出品者役のデモアカウントを作る
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'テスト太郎',
                'password' => Hash::make('password'),
                'postcode' => '163-8001',
                'address' => '東京都新宿区西新宿2-8-1',
                'email_verified_at' => now(),
            ]
        );

        // 購入者役のデモアカウントを作る
        User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'デモ次郎',
                'password' => Hash::make('demo1234'),
                'postcode' => '100-0005',
                'address' => '東京都千代田区丸の内1-9-1',
                'email_verified_at' => now(),
            ]
        );

        // 商品のシーダ―も続けて動かす
        $this->call([
            ItemSeeder::class,
        ]);
    }
}
