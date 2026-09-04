<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;

class RatingTest extends TestCase
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

    public function test_評価_受け取り確認済みなら購入者は出品者を評価できる(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        $order = Order::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'is_shipped' => true,
            'is_received' => true,
        ]);

        $response = $this->actingAs($buyer)->post("/item/{$item->id}/rating", [
            'score' => 5,
            'comment' => '丁寧な対応でした',
        ]);

        $response->assertRedirect(route('item.show', ['item_id' => $item->id]));
        $this->assertDatabaseHas('ratings', [
            'order_id' => $order->id,
            'score' => 5,
            'comment' => '丁寧な対応でした',
        ]);
    }

    public function test_評価_受け取り確認をする前は評価できない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'is_shipped' => true,
            'is_received' => false,
        ]);

        $response = $this->actingAs($buyer)->post("/item/{$item->id}/rating", ['score' => 5]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('ratings', 0);
    }

    public function test_評価_購入者以外は評価できない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'is_shipped' => true,
            'is_received' => true,
        ]);

        $response = $this->actingAs($seller)->post("/item/{$item->id}/rating", ['score' => 5]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('ratings', 0);
    }

    public function test_評価_1つの取引に2回評価はできない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        $order = Order::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'is_shipped' => true,
            'is_received' => true,
        ]);
        $order->rating()->create(['score' => 4, 'comment' => '最初の評価']);

        $response = $this->actingAs($buyer)->post("/item/{$item->id}/rating", ['score' => 1]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('ratings', 1);
    }

    public function test_評価_1から5の範囲外は評価できない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'is_shipped' => true,
            'is_received' => true,
        ]);

        $response = $this->actingAs($buyer)->post("/item/{$item->id}/rating", ['score' => 6]);

        $response->assertSessionHasErrors('score');
        $this->assertDatabaseCount('ratings', 0);
    }

    public function test_商品詳細_受け取り確認済みで未評価なら購入者に評価フォームが表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'is_shipped' => true,
            'is_received' => true,
        ]);

        $response = $this->actingAs($buyer)->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('出品者を評価する');
    }

    public function test_商品詳細_評価が無い出品者には評価なしと表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => false]);

        $response = $this->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('評価なし');
    }

    public function test_商品詳細_評価済みの取引があると出品者の平均評価が表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $anotherItem = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        $order = Order::create([
            'user_id' => $buyer->id,
            'item_id' => $anotherItem->id,
            'is_shipped' => true,
            'is_received' => true,
        ]);
        $order->rating()->create(['score' => 4]);

        // 評価対象と別の、この出品者が出している商品ページでも平均評価が見える
        $anotherOfSameSeller = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => false]);

        $response = $this->get("/item/{$anotherOfSameSeller->id}");

        $response->assertStatus(200);
        $response->assertSee('評価 4.0 (1件)');
    }

    public function test_商品詳細_評価済みなら出品者にも購入者からの評価が表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        $order = Order::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'is_shipped' => true,
            'is_received' => true,
        ]);
        $order->rating()->create(['score' => 5, 'comment' => 'また取引したいです']);

        $response = $this->actingAs($seller)->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('購入者からの評価');
        $response->assertSee('また取引したいです');
    }
}
