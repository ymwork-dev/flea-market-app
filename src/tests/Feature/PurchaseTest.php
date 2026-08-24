<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function createFullAccessUser()
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'postcode' => '123-4567',
            'address' => '東京都渋谷区',
            'name' => 'テストユーザー'
        ]);
    }

    public function test_商品購入機能_購入するボタンを押下すると購入が完了する(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create(['price' => 1000, 'is_sold' => false]);
        $response = $this->actingAs($user)->get("/purchase/success/{$item->id}");
        $response->assertStatus(302);
        $this->assertEquals(1, $item->fresh()->is_sold);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_商品購入機能_購入した商品は商品一覧画面にてsoldと表示される(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create(['is_sold' => true]);
        $response = $this->get("/");
        $response->assertStatus(200);
        $response->assertSee('sold');
    }

    public function test_商品購入機能_プロフィール購入した商品一覧に追加されている(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create(['name' => '購入済み確認商品']);
        Order::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->get("/mypage?page=buy");
        $response->assertStatus(200);
        $response->assertSee('購入済み確認商品');
    }
}
