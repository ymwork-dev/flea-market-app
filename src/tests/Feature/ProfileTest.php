<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser()
    {
        return User::factory()->create([
            'name' => 'テスト太郎',
            'email_verified_at' => now(),
            'postcode' => '123-4567',
            'address' => '東京都渋谷区',
            'img_url' => 'profiles/test_avatar.jpg'
        ]);
    }

    public function test_ユーザー情報取得_必要な情報が取得できる(): void
    {
        $user = $this->createVerifiedUser();

        $myListedItem = Item::factory()->create([
            'name' => '私が出品した商品',
            'user_id' => $user->id
        ]);

        $myPurchasedItem = Item::factory()->create(['name' => '私が購入した商品']);
        Order::create([
            'user_id' => $user->id,
            'item_id' => $myPurchasedItem->id,
        ]);

        $response = $this->actingAs($user)->get('/mypage');
        $response->assertStatus(200);
        $response->assertSee('profiles/test_avatar.jpg');
        $response->assertSee('テスト太郎');
        $responseSell = $this->actingAs($user)->get('/mypage?page=sell');
        $responseSell->assertSee('私が出品した商品');
        $responseBuy = $this->actingAs($user)->get('/mypage?page=buy');
        $responseBuy->assertSee('私が購入した商品');
    }

    public function test_ユーザー情報変更_変更項目が初期値として過去設定されていること(): void
    {
        $user = $this->createVerifiedUser();
        $response = $this->actingAs($user)->get('/mypage/profile');
        $response->assertStatus(200);
        $response->assertSee('profiles/test_avatar.jpg');
        $response->assertSee('テスト太郎');
        $response->assertSee('123-4567');
        $response->assertSee('東京都渋谷区');
    }
}
