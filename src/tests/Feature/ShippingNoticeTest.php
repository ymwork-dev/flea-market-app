<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;

class ShippingNoticeTest extends TestCase
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

    public function test_未発送の商品があるとヘッダー下に通知が表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => false]);

        $response = $this->actingAs($seller)->get('/');

        $response->assertStatus(200);
        $response->assertSee('発送が必要な商品が1件あります');
    }

    public function test_未発送の商品が無ければ通知は表示されない(): void
    {
        $seller = $this->createFullAccessUser();

        $response = $this->actingAs($seller)->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('発送が必要な商品が');
    }

    public function test_発送済みなら通知は表示されない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true]);

        $response = $this->actingAs($seller)->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('発送が必要な商品が');
    }

    public function test_マイページ_未発送の商品には発送準備中バッジが表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => false]);

        $response = $this->actingAs($seller)->get('/mypage?page=sell');

        $response->assertStatus(200);
        $response->assertSee('発送準備中');
    }

    public function test_マイページ_発送済みの商品はSoldバッジが表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id, 'is_shipped' => true]);

        $response = $this->actingAs($seller)->get('/mypage?page=sell');

        $response->assertStatus(200);
        $response->assertSee('Sold');
        $response->assertDontSee('発送準備中');
    }
}
