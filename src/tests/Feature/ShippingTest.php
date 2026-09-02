<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;

class ShippingTest extends TestCase
{
    use RefreshDatabase;

    private function createFullAccessUser()
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'postcode' => '123-4567',
            'address' => '東京都渋谷区',
        ]);
    }

    public function test_発送機能_出品者は売れた商品を発送済みにできる(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id]);

        $response = $this->actingAs($seller)->post("/item/{$item->id}/ship");

        $response->assertRedirect(route('item.show', ['item_id' => $item->id]));
        $this->assertDatabaseHas('orders', [
            'item_id' => $item->id,
            'is_shipped' => 1,
        ]);
    }

    public function test_発送機能_出品者以外は発送操作できない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id]);

        $response = $this->actingAs($buyer)->post("/item/{$item->id}/ship");

        $response->assertStatus(403);
        $this->assertDatabaseHas('orders', [
            'item_id' => $item->id,
            'is_shipped' => 0,
        ]);
    }

    public function test_発送機能_売れていない商品は発送できない(): void
    {
        $seller = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => false]);

        $response = $this->actingAs($seller)->post("/item/{$item->id}/ship");

        $response->assertStatus(403);
    }

    public function test_商品詳細_購入者には発送状況が表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => false]);

        $response = $this->actingAs($buyer)->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('発送準備中です');
    }

    public function test_商品詳細_発送済みなら購入者にもその表示になる(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true]);

        $response = $this->actingAs($buyer)->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('発送済みです');
    }

    public function test_商品詳細_出品者本人には発送するボタンが表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => false]);

        $response = $this->actingAs($seller)->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('発送する');
    }
}
