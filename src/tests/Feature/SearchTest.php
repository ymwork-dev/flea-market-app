<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_商品検索機能_商品名で部分一致検索ができる(): void
    {
        $this->seedSearchItems();
        $response = $this->get('/?keyword=PC');
        $response->assertStatus(200);
        $response->assertSee('ノートPC');
        $response->assertDontSee('腕時計');
        $response->assertDontSee('玉ねぎ3束');
    }

    public function test_商品検索機能_検索状態がマイリストでも保持されている(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'postcode' => '123-4567'
        ]);

        $this->seedSearchItems();

        $pcItem = Item::where('name', 'ノートPC')->first();
        if ($pcItem) {
            $user->likedItems()->attach($pcItem->id);
        }

        $response = $this->actingAs($user)->get('/?keyword=PC&tab=mylist');
        $response->assertStatus(200);
        $response->assertSee('PC');
        $response->assertSee('ノートPC');
        $response->assertDontSee('腕時計');
    }

    private function seedSearchItems(): void
    {
        $otherUser = User::factory()->create();

        Item::create([
            'name' => 'ノートPC',
            'price' => 50000,
            'description' => 'ハイスペックPC',
            'condition' => '良好',
            'user_id' => $otherUser->id,
            'img_url' => 'items/pc.jpg'
        ]);

        Item::create([
            'name' => '腕時計',
            'price' => 15000,
            'description' => '高級時計',
            'condition' => '普通',
            'user_id' => $otherUser->id,
            'img_url' => 'items/watch.jpg'
        ]);

        Item::create([
            'name' => '玉ねぎ3束',
            'price' => 300,
            'description' => '新鮮な野菜',
            'condition' => '良好',
            'user_id' => $otherUser->id,
            'img_url' => 'items/onion.jpg'
        ]);
    }
}
