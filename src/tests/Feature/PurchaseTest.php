<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;
use App\Services\StripeCheckoutService;

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

    public function test_商品購入機能_購入するボタンを押下するとStripeの決済ページに遷移する(): void
    {
        $seller = $this->createFullAccessUser();
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id, 'price' => 1000, 'is_sold' => false]);

        // 本物のStripeには問い合わせず、偽物の決済ページURLを返す
        $this->mock(StripeCheckoutService::class, function ($mock) {
            $mock->shouldReceive('createSession')
                ->once()
                ->andReturn((object) ['url' => 'https://checkout.stripe.com/test-session']);
        });

        $response = $this->actingAs($user)
            ->from("/purchase/{$item->id}")
            ->post("/purchase/{$item->id}", ['payment_method' => 'カード支払い']);

        $response->assertRedirect('https://checkout.stripe.com/test-session');
        // この時点ではまだ注文は作られない(Webhookが届いてから作られるため)
        $this->assertEquals(0, $item->fresh()->is_sold);
    }

    public function test_商品購入機能_注文が出来ていれば購入完了画面にメッセージが出る(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create(['is_sold' => true]);
        Order::create(['user_id' => $user->id, 'item_id' => $item->id]);

        // Webhookがすでに注文を作り終えている状態を想定
        $response = $this->actingAs($user)->get("/purchase/success/{$item->id}");

        $response->assertRedirect('/');
        $response->assertSessionHas('message', '商品を購入しました');
    }

    public function test_商品購入機能_注文がまだ出来ていなければメッセージは出ない(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create(['is_sold' => false]);

        // コンビニ払いなどでWebhookがまだ届いていない状態を想定
        $response = $this->actingAs($user)->get("/purchase/success/{$item->id}");

        $response->assertRedirect('/');
        $response->assertSessionMissing('message');
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
