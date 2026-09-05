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

    public function test_受け取り確認_購入者は発送済みの商品を評価付きで受け取り確認できる(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        $order = Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true]);

        $response = $this->actingAs($buyer)->post("/item/{$item->id}/receive", [
            'score' => 5,
            'comment' => '丁寧な対応でした',
        ]);

        $response->assertRedirect(route('item.show', ['item_id' => $item->id]));
        $this->assertDatabaseHas('orders', [
            'item_id' => $item->id,
            'is_received' => 1,
        ]);
        $this->assertDatabaseHas('ratings', [
            'order_id' => $order->id,
            'score' => 5,
            'comment' => '丁寧な対応でした',
        ]);
    }

    public function test_受け取り確認_評価を選ばずに送信すると受け取り確認も評価も保存されない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true]);

        $response = $this->actingAs($buyer)->post("/item/{$item->id}/receive", []);

        $response->assertSessionHasErrors('score');
        $this->assertDatabaseHas('orders', [
            'item_id' => $item->id,
            'is_received' => 0,
        ]);
        $this->assertDatabaseCount('ratings', 0);
    }

    public function test_受け取り確認_1から5の範囲外の評価では受け取り確認できない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true]);

        $response = $this->actingAs($buyer)->post("/item/{$item->id}/receive", ['score' => 6]);

        $response->assertSessionHasErrors('score');
        $this->assertDatabaseCount('ratings', 0);
    }

    public function test_受け取り確認_購入者以外は受け取り確認できない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true]);

        $response = $this->actingAs($seller)->post("/item/{$item->id}/receive", ['score' => 5]);

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

        $response = $this->actingAs($buyer)->post("/item/{$item->id}/receive", ['score' => 5]);

        $response->assertStatus(403);
    }

    public function test_受け取り確認_受け取り確認済みの取引をもう一度受け取り確認することはできない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        $order = Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true, 'is_received' => true]);
        $order->rating()->create(['score' => 4]);

        $response = $this->actingAs($buyer)->post("/item/{$item->id}/receive", ['score' => 1]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('ratings', 1);
    }

    public function test_商品詳細_発送済みなら購入者に受け取り評価フォームが表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true]);

        $response = $this->actingAs($buyer)->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('受取と評価を完了する');
    }

    public function test_商品詳細_受け取り確認済みなら購入者にも出品者にも取引完了と表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        $order = Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true, 'is_received' => true]);
        $order->rating()->create(['score' => 5, 'comment' => 'また取引したいです']);

        $buyerResponse = $this->actingAs($buyer)->get("/item/{$item->id}");
        $buyerResponse->assertStatus(200);
        $buyerResponse->assertSee('取引が完了しました');

        $sellerResponse = $this->actingAs($seller)->get("/item/{$item->id}");
        $sellerResponse->assertStatus(200);
        $sellerResponse->assertSee('取引が完了しました');
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
        $order = Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true, 'is_received' => true]);
        $order->rating()->create(['score' => 5, 'comment' => 'また取引したいです']);

        $response = $this->actingAs($seller)->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('購入者からの評価');
        $response->assertSee('また取引したいです');
    }
}
