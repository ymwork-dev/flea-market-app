<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;

class ReceiveTest extends TestCase
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

    public function test_受け取り確認_購入者は発送済みの商品を受け取り確認できる(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true]);

        $response = $this->actingAs($buyer)->post("/item/{$item->id}/receive");

        $response->assertRedirect(route('item.show', ['item_id' => $item->id]));
        $this->assertDatabaseHas('orders', [
            'item_id' => $item->id,
            'is_received' => 1,
        ]);
    }

    public function test_受け取り確認_購入者以外は受け取り確認できない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true]);

        $response = $this->actingAs($seller)->post("/item/{$item->id}/receive");

        $response->assertStatus(403);
        $this->assertDatabaseHas('orders', [
            'item_id' => $item->id,
            'is_received' => 0,
        ]);
    }

    public function test_受け取り確認_発送される前は受け取り確認できない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => false]);

        $response = $this->actingAs($buyer)->post("/item/{$item->id}/receive");

        $response->assertStatus(403);
    }

    public function test_商品詳細_発送済みなら購入者に受け取りましたボタンが表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true]);

        $response = $this->actingAs($buyer)->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('受け取りました');
    }

    public function test_商品詳細_受け取り確認済みなら購入者にも出品者にも取引完了と表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true, 'is_received' => true]);

        $buyerResponse = $this->actingAs($buyer)->get("/item/{$item->id}");
        $buyerResponse->assertStatus(200);
        $buyerResponse->assertSee('取引が完了しました');

        $sellerResponse = $this->actingAs($seller)->get("/item/{$item->id}");
        $sellerResponse->assertStatus(200);
        $sellerResponse->assertSee('取引が完了しました');
    }
}
