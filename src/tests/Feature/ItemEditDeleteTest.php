<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;

class ItemEditDeleteTest extends TestCase
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

    public function test_出品編集_出品者本人は自分の商品を編集できる(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $user->id, 'is_sold' => false]);
        $category = Category::create(['name' => 'ファッション']);

        $response = $this->actingAs($user)->put("/sell/{$item->id}", [
            'name'         => '編集後の商品名',
            'description'  => '編集後の説明',
            'condition'    => '良好',
            'price'        => 2000,
            'category_ids' => [$category->id],
        ]);

        $response->assertRedirect(route('item.show', ['item_id' => $item->id]));
        $this->assertDatabaseHas('items', [
            'id'   => $item->id,
            'name' => '編集後の商品名',
        ]);
        $this->assertTrue($item->fresh()->categories->contains($category->id));
    }

    public function test_出品編集_他人の商品は編集できない(): void
    {
        $owner = $this->createFullAccessUser();
        $otherUser = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $owner->id, 'is_sold' => false]);

        $response = $this->actingAs($otherUser)->get("/sell/{$item->id}/edit");

        $response->assertStatus(403);
    }

    public function test_出品編集_売却済みの商品は編集できない(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $user->id, 'is_sold' => true]);

        $response = $this->actingAs($user)->get("/sell/{$item->id}/edit");

        $response->assertStatus(403);
    }

    public function test_出品削除_出品者本人は自分の商品を削除できる(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $user->id, 'is_sold' => false]);

        $response = $this->actingAs($user)->delete("/sell/{$item->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    public function test_出品削除_売却済みの商品は削除できない(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $user->id, 'is_sold' => true]);

        $response = $this->actingAs($user)->delete("/sell/{$item->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }

    public function test_商品詳細_出品者本人が見ると編集削除ボタンが表示される(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $user->id, 'is_sold' => false]);

        $response = $this->actingAs($user)->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('編集する');
        $response->assertSee('削除する');
        $response->assertDontSee('購入手続きへ');
    }
}
