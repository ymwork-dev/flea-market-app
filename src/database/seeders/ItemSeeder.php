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

// 商品のデモデータを入れるシーダ―クラス
class ItemSeeder extends Seeder
{
    public function run(): void
    {
        // 外部キー制約(注文があるのに商品が消える)ようなつながりの矛盾を止める仕組みを一旦とめる
        Schema::disableForeignKeyConstraints();
        // テーブルの中身を全部消す。カテゴリとのつながり・評価・注文・いいね・コメント・商品そのもの
        DB::table('category_item')->truncate();
        DB::table('ratings')->truncate();
        DB::table('orders')->truncate();
        DB::table('likes')->truncate();
        DB::table('comments')->truncate();
        Item::truncate();
        // 外部キー制約を元に戻す
        Schema::enableForeignKeyConstraints();

        // デモ出品者を取得
        $user = User::where('email', 'test@example.com')->first();

        // カテゴリ名10個一覧
        $categories = [
            'ファッション', 'インテリア', 'レディース', 'メンズ',
            'コスメ', 'スポーツ', 'ハンドメイド', 'アクセサリー',
            'おもちゃ', 'ベビー・キッズ'
        ];

        // カテゴリ名を1つずつ処理する
        foreach ($categories as $cat) {
            // あれば取り出し、なければ作る
            Category::firstOrCreate(['name' => $cat]);
        }

        // カテゴリ名とIDの対応表を作る
        $categoryMap = Category::pluck('id', 'name');

        // 商品10個のデータ
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

        // 画像を置くフォルダ
        $targetDirectory = storage_path('app/public/items');
        // フォルダがない場合
        if (!File::isDirectory($targetDirectory)) {
            // フォルダを作る
            File::makeDirectory($targetDirectory, 0755, true, true);
        }

        // 商品データを1つずつ処理する
        foreach ($items as $itemData) {
            // カテゴリ名を取り出す
            $categoryNames = $itemData['categories'];
            // カテゴリはitemテーブルの列ではないので、商品データーから外しておく
            unset($itemData['categories']);

            // 商品をDBに保存する
            $item = Item::create($itemData);

            // 画像の場所からファイル名だけ取り出す
            $fileName = basename($itemData['img_url']);
            // コピーして商品画像の置き場所に置く
            File::copy(
                public_path('img/dummy/' . $fileName),
                storage_path('app/public/items/' . $fileName)
            );

            // カテゴリ名をIDに変える
            $categoryIds = [];
            foreach ($categoryNames as $name) {
                $categoryIds[] = $categoryMap[$name];
            }
            // 商品とカテゴリをつなぐ
            $item->categories()->attach($categoryIds);
        }
    }
}
