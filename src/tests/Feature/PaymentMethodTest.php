<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class PaymentMethodTest extends TestCase
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

    public function test_支払い方法選択機能_同じ商品ページに戻ると選択済みの支払い方法が表示される(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create();

        // まずこの商品の購入画面を開く(last_item_idがセッションに記録される)
        $this->actingAs($user)->get("/purchase/{$item->id}");

        // 支払い方法を選ぶと、実際の画面ではJavaScriptがこのURLに送って
        // セッションに保存している
        $this->actingAs($user)->post('/purchase/payment/store-session', [
            'payment_method' => 'コンビニ払い',
        ]);

        // 住所変更画面などから同じ商品の購入画面に戻ってきた場面を想定
        $response = $this->actingAs($user)->get("/purchase/{$item->id}");

        $response->assertStatus(200);
        // セレクトボックスの選択肢としてはコンビニ払いが常に表示されるので、
        // 「実際に選択が保持された結果」だけに現れる小計欄の表示で確認する
        $response->assertSee('<td id="display-payment">コンビニ払い</td>', false);
    }
}
