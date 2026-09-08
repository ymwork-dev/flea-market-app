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
        // 外部キー制約を無効にしている間はItemが消えてもordersなどが連動削除されないので、
        // item_idに紐づくテーブルはここで手動ですべて空にしておく
        Schema::disableForeignKeyConstraints();
        DB::table('category_item')->truncate();
        DB::table('ratings')->truncate();
        DB::table('orders')->truncate();
        DB::table('likes')->truncate();
        DB::table('comments')->truncate();
        Item::truncate();
        Schema::enableForeignKeyConstraints();

        $user = User::where('email', 'test@example.com')->first();

        $categories = [
            'ファッション', 'インテリア', 'レディース', 'メンズ',
            'コスメ', 'スポーツ', 'ハンドメイド', 'アクセサリー',
            'おもちゃ', 'ベビー・キッズ'
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat]);
        }

        $categoryMap = Category::pluck('id', 'name');

        // 商品ごとに、付けるカテゴリーもここへ直接書いておく。
        // (以前は商品名でカテゴリーを振り分けていたが、「デニムパンツ」のように
        // 同じ名前の商品が複数あると正しく振り分けられなかったため)
        $items = [
            [
                'name' => 'スエードシューズ',
                'price' => 16000,
                'brand' => '',
                'description' => 'メンズスエードシューズ',
                'img_url' => 'items/メンズスエードシューズ.jpg',
                'condition' => '良好',
                'user_id' => $user->id,
                'categories' => ['ファッション', 'メンズ'],
            ],
            [
                'name' => 'デニムジャケット',
                'price' => 15000,
                'brand' => '',
                'description' => 'メンズデニムジャケット',
                'img_url' => 'items/メンズデニムジャケット.jpg',
                'condition' => '目立った傷や汚れなし',
                'user_id' => $user->id,
                'categories' => ['ファッション', 'メンズ'],
            ],
            [
                'name' => 'デニムパンツ',
                'price' => 15000,
                'brand' => '',
                'description' => 'メンズデニムパンツ',
                'img_url' => 'items/メンズデニムパンツ.jpg',
                'condition' => 'やや傷や汚れあり',
                'user_id' => $user->id,
                'categories' => ['ファッション', 'メンズ'],
            ],
            [
                'name' => 'メンズパーカー',
                'price' => 6000,
                'brand' => '',
                'description' => 'メンズパーカー',
                'img_url' => 'items/メンズパーカー.jpg',
                'condition' => '状態が悪い',
                'user_id' => $user->id,
                'categories' => ['ファッション', 'メンズ'],
            ],
            [
                'name' => 'ベルト',
                'price' => 5000,
                'brand' => '',
                'description' => 'メンズベルト',
                'img_url' => 'items/メンズベルト.jpg',
                'condition' => '良好',
                'user_id' => $user->id,
                'categories' => ['ファッション', 'メンズ'],
            ],
            [
                'name' => 'スニーカー',
                'price' => 8000,
                'brand' => '',
                'description' => 'レディーススニーカー',
                'img_url' => 'items/レディーススニーカー.jpg',
                'condition' => '目立った傷や汚れなし',
                'user_id' => $user->id,
                'categories' => ['ファッション', 'レディース'],
            ],
            [
                'name' => 'ダウンコート',
                'price' => 45000,
                'brand' => '',
                'description' => 'レディースダウンコート',
                'img_url' => 'items/レディースダウンコート.jpg',
                'condition' => '良好',
                'user_id' => $user->id,
                'categories' => ['ファッション', 'レディース'],
            ],
            [
                'name' => 'デニムパンツ',
                'price' => 7000,
                'brand' => '',
                'description' => 'レディースデニムパンツ',
                'img_url' => 'items/レディースデニムパンツ.jpg',
                'condition' => '目立った傷や汚れなし',
                'user_id' => $user->id,
                'categories' => ['ファッション', 'レディース'],
            ],
            [
                'name' => 'トレンチコート',
                'price' => 20000,
                'brand' => '',
                'description' => 'レディーストレンチコート',
                'img_url' => 'items/レディーストレンチコート.jpg',
                'condition' => '良好',
                'user_id' => $user->id,
                'categories' => ['ファッション', 'レディース'],
            ],
            [
                'name' => 'ニット',
                'price' => 2500,
                'brand' => '',
                'description' => 'レディースニット',
                'img_url' => 'items/レディースニット.jpg',
                'condition' => '目立った傷や汚れなし',
                'user_id' => $user->id,
                'categories' => ['ファッション', 'レディース'],
            ],
        ];

        $targetDirectory = storage_path('app/public/items');
        if (!File::isDirectory($targetDirectory)) {
            File::makeDirectory($targetDirectory, 0755, true, true);
        }

        foreach ($items as $itemData) {
            // categoriesはitemsテーブルのカラムでは無いので、
            // Item::create()に渡す前に取り出しておく
            $categoryNames = $itemData['categories'];
            unset($itemData['categories']);

            $item = Item::create($itemData);

            $fileName = basename($itemData['img_url']);

            File::copy(
                public_path('img/dummy/' . $fileName),
                storage_path('app/public/items/' . $fileName)
            );

            $categoryIds = [];
            foreach ($categoryNames as $name) {
                $categoryIds[] = $categoryMap[$name];
            }
            $item->categories()->attach($categoryIds);
        }
    }
}
