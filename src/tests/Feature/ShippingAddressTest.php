<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingAddressTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    // 💡 先頭に test_ を付けました
    public function test_配送先変更機能_送付先住所変更画面にて登録した住所が商品購入画面に反映されている()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->update([
            'postcode' => '999-9999',
            'address' => '東京都住所変更テスト1-1',
            'building' => 'テストビル101',
        ]);

        $this->assertEquals('999-9999', $user->postcode);
        $this->assertEquals('東京都住所変更テスト1-1', $user->address);
        $this->assertEquals('テストビル101', $user->building);
    }

    /**
     * @test
     */
    // 💡 先頭に test_ を付けました
    public function test_配送先変更機能_購入した商品に送付先住所が紐づいて登録される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        $user->update([
            'postcode' => '888-8888',
            'address' => '大阪府購入紐づけテスト2-2',
            'building' => 'サンプルビル',
        ]);

        Order::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'postcode' => $user->postcode,
            'address' => $user->address,
            'building' => $user->building,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'postcode' => '888-8888',
            'address' => '大阪府購入紐づけテスト2-2',
        ]);
    }
}
