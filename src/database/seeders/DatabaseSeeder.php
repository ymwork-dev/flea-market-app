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
        // 出品者役のユーザーを作成
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'テスト太郎',
                'password' => Hash::make('password123'),
            ]
        );

        $this->call([
            ItemSeeder::class,
        ]);
    }
}
