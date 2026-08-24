<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    private function seedItems()
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();

        $items = [
            ['name' => '腕時計', 'price' => 15000, 'brand' => 'Rolax', 'description' => 'スタイリッシュなデザインのメンズ腕時計', 'img_url' => 'items/腕時計.jpg', 'condition' => '良好'],
            ['name' => 'HDD', 'price' => 5000, 'brand' => '西芝', 'description' => '高速で信頼性の高いハードディスク', 'img_url' => 'items/HDD.jpg', 'condition' => '目立った傷や汚れなし'],
            ['name' => '玉ねぎ3束', 'price' => 300, 'brand' => 'なし', 'description' => '新鮮な玉ねぎ3束のセット', 'img_url' => 'items/玉ねぎ3束.jpg', 'condition' => 'やや傷や汚れあり'],
            ['name' => '革靴', 'price' => 4000, 'brand' => '', 'description' => 'クラシックなデザインの革靴', 'img_url' => 'items/革靴.jpg', 'condition' => '状態が悪い'],
            ['name' => 'ノートPC', 'price' => 45000, 'brand' => '', 'description' => '高性能なノートパソコン', 'img_url' => 'items/ノートPC.jpg', 'condition' => '良好'],
            ['name' => 'マイク', 'price' => 8000, 'brand' => 'なし', 'description' => '高音質のレコーディング用マイク', 'img_url' => 'items/マイク.jpg', 'condition' => '目立った傷や汚れなし'],
            ['name' => 'ショルダーバッグ', 'price' => 3500, 'brand' => '', 'description' => 'おしゃれなショルダーバッグ', 'img_url' => 'items/ショルダーバッグ.jpg', 'condition' => 'やや傷や汚れあり'],
            ['name' => 'タンブラー', 'price' => 500, 'brand' => 'なし', 'description' => '使いやすいタンブラー', 'img_url' => 'items/タンブラー.jpg', 'condition' => '状態が悪い'],
            ['name' => 'コーヒーミル', 'price' => 4000, 'brand' => 'Starbacks', 'description' => '手動のコーヒーミル', 'img_url' => 'items/コーヒーミル.jpg', 'condition' => '良好'],
            ['name' => 'メイクセット', 'price' => 2500, 'brand' => '', 'description' => '便利なメイクアップセット', 'img_url' => 'items/メイクセット.jpg', 'condition' => '目立った傷や汚れなし'],
        ];

        foreach ($items as $item) {
            Item::create(array_merge($item, ['user_id' => $user->id]));
        }

        return $user;
    }

    public function test_商品一覧取得_全商品を取得できる(): void
    {
        $this->seedItems();

        $response = $this->get('/');

        $response->assertStatus(200);

        $expectedItems = [
            ['name' => '腕時計', 'img' => 'items/腕時計.jpg'],
            ['name' => 'HDD', 'img' => 'items/HDD.jpg'],
            ['name' => '玉ねぎ3束', 'img' => 'items/玉ねぎ3束.jpg'],
            ['name' => '革靴', 'img' => 'items/革靴.jpg'],
            ['name' => 'ノートPC', 'img' => 'items/ノートPC.jpg'],
            ['name' => 'マイク', 'img' => 'items/マイク.jpg'],
            ['name' => 'ショルダーバッグ', 'img' => 'items/ショルダーバッグ.jpg'],
            ['name' => 'タンブラー', 'img' => 'items/タンブラー.jpg'],
            ['name' => 'コーヒーミル', 'img' => 'items/コーヒーミル.jpg'],
            ['name' => 'メイクセット', 'img' => 'items/メイクセット.jpg'],
        ];

        foreach ($expectedItems as $item) {
            $response->assertSee($item['name']);
            $response->assertSee($item['img']);
        }
    }

    public function test_商品一覧取得_購入済み商品はSoldと表示される(): void
    {
        $user = User::factory()->create();
        Item::create([
            'name' => '売り切れの商品',
            'price' => 1000,
            'description' => 'テスト説明',
            'condition' => '良好',
            'user_id' => $user->id,
            'is_sold' => true,
            'img_url' => 'items/test.jpg'
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Sold');
    }

    public function test_商品一覧取得_自分が出品した商品は表示されない(): void
    {
        $me = User::factory()->create([
            'email_verified_at' => now(),
            'postcode' => '123-4567'
        ]);
        $otherUser = User::factory()->create();

        Item::create([
            'name' => '自分の出品した商品',
            'price' => 1000,
            'description' => 'テスト説明',
            'condition' => '良好',
            'user_id' => $me->id,
            'img_url' => 'items/test.jpg'
        ]);

        Item::create([
            'name' => '他人の出品した商品',
            'price' => 2000,
            'description' => 'テスト説明',
            'condition' => '良好',
            'user_id' => $otherUser->id,
            'img_url' => 'items/test.jpg'
        ]);

        $response = $this->actingAs($me)->get('/');
        $response->assertStatus(200);
        $response->assertSee('他人の出品した商品');
        $response->assertDontSee('自分の出品した商品');
    }
}
