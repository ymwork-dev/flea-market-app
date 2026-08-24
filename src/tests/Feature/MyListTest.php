<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyListTest extends TestCase
{
    use RefreshDatabase;

    public function test_マイリスト一覧取得_いいねした商品だけが表示される(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'postcode' => '123-4567'
        ]);
        $otherUser = User::factory()->create();

        $likedItem = Item::create([
            'name' => 'いいねした商品',
            'price' => 1000,
            'description' => 'テスト',
            'condition' => '良好',
            'user_id' => $otherUser->id,
            'img_url' => 'items/liked.jpg'
        ]);

        $notLikedItem = Item::create([
            'name' => 'いいねしていない商品',
            'price' => 2000,
            'description' => 'テスト',
            'condition' => '良好',
            'user_id' => $otherUser->id,
            'img_url' => 'items/not-liked.jpg'
        ]);

        $user->likedItems()->attach($likedItem->id);

        $response = $this->actingAs($user)->get('/?tab=mylist');
        $response->assertStatus(200);
        $response->assertSee('いいねした商品');
        $response->assertSee('items/liked.jpg');
        $response->assertDontSee('いいねしていない商品');
    }

    public function test_マイリスト一覧取得_購入済み商品はSoldと表示される(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'postcode' => '123-4567'
        ]);
        $otherUser = User::factory()->create();

        $soldItem = Item::create([
            'name' => '購入済み商品',
            'price' => 1000,
            'description' => 'テスト',
            'condition' => '良好',
            'user_id' => $otherUser->id,
            'img_url' => 'items/sold.jpg',
            'is_sold' => true
        ]);

        $user->likedItems()->attach($soldItem->id);
        $response = $this->actingAs($user)->get('/?tab=mylist');
        $response->assertStatus(200);
        $response->assertSee('Sold');
    }

    public function test_マイリスト一覧取得_未認証の場合は何も表示されない(): void
    {
        $response = $this->get('/?tab=mylist');
        $response->assertStatus(200);
        $response->assertDontSee('product-card'); // 商品カード等が描画されていないことを確認
    }
}
