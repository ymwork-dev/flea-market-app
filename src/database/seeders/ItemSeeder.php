<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('category_item')->truncate();
        Item::truncate();
        Schema::enableForeignKeyConstraints();

        $user = User::where('email', 'test@example.com')->first();

        $categories = [
            'ファッション', '家電', 'インテリア', 'レディース', 'メンズ',
            'コスメ', '本', 'ゲーム', 'スポーツ', 'キッチン',
            'ハンドメイド', 'アクセサリー', 'おもちゃ', 'ベビー・キッズ'
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat]);
        }

        $categoryMap = Category::pluck('id', 'name');

        $items = [
            [
                'name' => '腕時計',
                'price' => 15000,
                'brand' => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'img_url' => 'items/腕時計.jpg',
                'condition' => '良好',
                'user_id' => $user->id,
            ],
            [
                'name' => 'HDD',
                'price' => 5000,
                'brand' => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'img_url' => 'items/HDD.jpg',
                'condition' => '目立った傷や汚れなし',
                'user_id' => $user->id,
            ],
            [
                'name' => '玉ねぎ3束',
                'price' => 300,
                'brand' => 'なし',
                'description' => '新鮮な玉ねぎ3束のセット',
                'img_url' => 'items/玉ねぎ3束.jpg',
                'condition' => 'やや傷や汚れあり',
                'user_id' => $user->id,
            ],
            [
                'name' => '革靴',
                'price' => 4000,
                'brand' => '',
                'description' => 'クラシックなデザインの革靴',
                'img_url' => 'items/革靴.jpg',
                'condition' => '状態が悪い',
                'user_id' => $user->id,
            ],
            [
                'name' => 'ノートPC',
                'price' => 45000,
                'brand' => '',
                'description' => '高性能なノートパソコン',
                'img_url' => 'items/ノートPC.jpg',
                'condition' => '良好',
                'user_id' => $user->id,
            ],
            [
                'name' => 'マイク',
                'price' => 8000,
                'brand' => 'なし',
                'description' => '高音質のレコーディング用マイク',
                'img_url' => 'items/マイク.jpg',
                'condition' => '目立った傷や汚れなし',
                'user_id' => $user->id,
            ],
            [
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'brand' => '',
                'description' => 'おしゃれなショルダーバッグ',
                'img_url' => 'items/ショルダーバッグ.jpg',
                'condition' => 'やや傷や汚れあり',
                'user_id' => $user->id,
            ],
            [
                'name' => 'タンブラー',
                'price' => 500,
                'brand' => 'なし',
                'description' => '使いやすいタンブラー',
                'img_url' => 'items/タンブラー.jpg',
                'condition' => '状態が悪い',
                'user_id' => $user->id,
            ],
            [
                'name' => 'コーヒーミル',
                'price' => 4000,
                'brand' => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'img_url' => 'items/コーヒーミル.jpg',
                'condition' => '良好',
                'user_id' => $user->id,
            ],
            [
                'name' => 'メイクセット',
                'price' => 2500,
                'brand' => '',
                'description' => '便利なメイクアップセット',
                'img_url' => 'items/メイクセット.jpg',
                'condition' => '目立った傷や汚れなし',
                'user_id' => $user->id,
            ],
        ];

        $targetDirectory = storage_path('app/public/items');
        if (!File::isDirectory($targetDirectory)) {
            File::makeDirectory($targetDirectory, 0755, true, true);
        }

        foreach ($items as $itemData) {
            $item = Item::create($itemData);

            $fileName = basename($itemData['img_url']);

            File::copy(
                public_path('img/dummy/' . $fileName),
                storage_path('app/public/items/' . $fileName)
            );

            $targetNames = match ($item->name) {
                '腕時計' => ['ファッション', 'メンズ'],
                'HDD' => ['家電'],
                '玉ねぎ3束' => ['キッチン'],
                '革靴' => ['ファッション', 'メンズ'],
                'ノートPC' => ['家電'],
                'マイク' => ['家電'],
                'ショルダーバッグ' => ['ファッション', 'レディース'],
                'タンブラー' => ['キッチン', 'インテリア'],
                'コーヒーミル' => ['キッチン'],
                'メイクセット' => ['コスメ', 'レディース'],
                default => ['インテリア'],
            };

            $categoryIds = [];
            foreach ($targetNames as $name) {
                $categoryIds[] = $categoryMap[$name];
            }
            $item->categories()->attach($categoryIds);
        }
    }
}
