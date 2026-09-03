<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;
use App\Notifications\ItemSoldNotification;
use App\Notifications\PaymentCompletedNotification;
use App\Services\StripeCheckoutService;
use Illuminate\Support\Facades\Notification;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_カード払いの決済完了Webhookを受け取ると注文が作られる(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create(['postcode' => '123-4567', 'address' => '東京都渋谷区']);
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => false]);

        Notification::fake();

        $this->mock(StripeCheckoutService::class, function ($mock) use ($item, $buyer) {
            $mock->shouldReceive('constructWebhookEvent')
                ->once()
                ->andReturn((object) [
                    'type' => 'checkout.session.completed',
                    'data' => (object) [
                        'object' => (object) [
                            'id' => 'test_session_card',
                            'payment_status' => 'paid',
                            'metadata' => (object) [
                                'item_id' => $item->id,
                                'user_id' => $buyer->id,
                                'postcode' => $buyer->postcode,
                                'address' => $buyer->address,
                                'building' => '',
                            ],
                        ],
                    ],
                ]);
        });

        $response = $this->postJson('/stripe/webhook', [], ['Stripe-Signature' => 'dummy']);

        $response->assertStatus(200);
        $this->assertEquals(1, $item->fresh()->is_sold);
        $this->assertDatabaseHas('orders', [
            'item_id' => $item->id,
            'user_id' => $buyer->id,
            'payment_status' => 'paid',
            'stripe_session_id' => 'test_session_card',
        ]);
        Notification::assertSentTo($seller, ItemSoldNotification::class);
    }

    public function test_コンビニ払いの決済完了Webhookを受け取るとunpaidで注文が作られる(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create(['postcode' => '123-4567', 'address' => '東京都渋谷区']);
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => false]);

        Notification::fake();

        $this->mock(StripeCheckoutService::class, function ($mock) use ($item, $buyer) {
            $mock->shouldReceive('constructWebhookEvent')
                ->once()
                ->andReturn((object) [
                    'type' => 'checkout.session.completed',
                    'data' => (object) [
                        'object' => (object) [
                            'id' => 'test_session_konbini',
                            // コンビニ払いは、この時点ではまだ支払われていない
                            'payment_status' => 'unpaid',
                            'metadata' => (object) [
                                'item_id' => $item->id,
                                'user_id' => $buyer->id,
                                'postcode' => $buyer->postcode,
                                'address' => $buyer->address,
                                'building' => '',
                            ],
                        ],
                    ],
                ]);
        });

        $response = $this->postJson('/stripe/webhook', [], ['Stripe-Signature' => 'dummy']);

        $response->assertStatus(200);
        $this->assertEquals(1, $item->fresh()->is_sold);
        $this->assertDatabaseHas('orders', [
            'item_id' => $item->id,
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_すでに売却済みの商品には決済完了Webhookが届いても注文を重複作成しない(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        // すでに別の注文で売却済みの商品
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        Order::create(['user_id' => $buyer->id, 'item_id' => $item->id]);

        $this->mock(StripeCheckoutService::class, function ($mock) use ($item, $buyer) {
            $mock->shouldReceive('constructWebhookEvent')
                ->once()
                ->andReturn((object) [
                    'type' => 'checkout.session.completed',
                    'data' => (object) [
                        'object' => (object) [
                            'id' => 'test_session_duplicate',
                            'payment_status' => 'paid',
                            'metadata' => (object) [
                                'item_id' => $item->id,
                                'user_id' => $buyer->id,
                                'postcode' => '999-9999',
                                'address' => '重複してはいけない住所',
                                'building' => '',
                            ],
                        ],
                    ],
                ]);
        });

        $response = $this->postJson('/stripe/webhook', [], ['Stripe-Signature' => 'dummy']);

        $response->assertStatus(200);
        // 注文は最初の1件のままで、2件目は作られない
        $this->assertEquals(1, Order::where('item_id', $item->id)->count());
    }

    public function test_支払い完了のWebhookを受け取るとpayment_statusがpaidになる(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        $order = Order::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_status' => 'unpaid',
            'stripe_session_id' => 'test_session_789',
        ]);

        Notification::fake();

        // 本物のStripeの署名確認はせず、「この内容のイベントが届いた」という
        // 偽物のイベントに差し替えてテストする
        $this->mock(StripeCheckoutService::class, function ($mock) {
            $mock->shouldReceive('constructWebhookEvent')
                ->once()
                ->andReturn((object) [
                    'type' => 'checkout.session.async_payment_succeeded',
                    'data' => (object) [
                        'object' => (object) ['id' => 'test_session_789'],
                    ],
                ]);
        });

        $response = $this->postJson('/stripe/webhook', [], ['Stripe-Signature' => 'dummy']);

        $response->assertStatus(200);
        $this->assertEquals('paid', $order->fresh()->payment_status);
        // 出品者に「支払いが確認できました」の通知が送られようとしたことを確認
        Notification::assertSentTo($seller, PaymentCompletedNotification::class);
    }

    public function test_支払い失敗のWebhookを受け取るとpayment_statusがfailedになる(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        $order = Order::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_status' => 'unpaid',
            'stripe_session_id' => 'test_session_999',
        ]);

        $this->mock(StripeCheckoutService::class, function ($mock) {
            $mock->shouldReceive('constructWebhookEvent')
                ->once()
                ->andReturn((object) [
                    'type' => 'checkout.session.async_payment_failed',
                    'data' => (object) [
                        'object' => (object) ['id' => 'test_session_999'],
                    ],
                ]);
        });

        $response = $this->postJson('/stripe/webhook', [], ['Stripe-Signature' => 'dummy']);

        $response->assertStatus(200);
        $this->assertEquals('failed', $order->fresh()->payment_status);
    }

    public function test_署名確認に失敗したら400が返り注文は変更されない(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id, 'is_sold' => true]);
        $order = Order::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_status' => 'unpaid',
            'stripe_session_id' => 'test_session_bad',
        ]);

        $this->mock(StripeCheckoutService::class, function ($mock) {
            $mock->shouldReceive('constructWebhookEvent')
                ->once()
                ->andThrow(new \Exception('invalid signature'));
        });

        $response = $this->postJson('/stripe/webhook', [], ['Stripe-Signature' => 'invalid']);

        $response->assertStatus(400);
        $this->assertEquals('unpaid', $order->fresh()->payment_status);
    }
}
