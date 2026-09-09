<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 出品者役のユーザーを作成(ログイン画面に表示するデモ用アカウント)
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'テスト太郎',
                'password' => Hash::make('password'),
                'postcode' => '160-0022',
                'address' => '東京都新宿区新宿1-1-1',
                'email_verified_at' => now(),
            ]
        );

        // 購入者役のユーザーを作成(出品者アカウントは自分の商品を買えないため、
        // 購入~発送~受け取り~評価の一連の流れを試すにはこちらでログインする)
        User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'デモ次郎',
                'password' => Hash::make('demo1234'),
                'postcode' => '150-0001',
                'address' => '東京都渋谷区神宮前1-1-1',
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            ItemSeeder::class,
        ]);
    }
}
