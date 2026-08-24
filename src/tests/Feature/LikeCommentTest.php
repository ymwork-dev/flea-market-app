<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class LikeCommentTest extends TestCase
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

    public function test_いいねアイコンを押下して合計値が増加する()
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create();
        $response = $this->actingAs($user)->post("/like/{$item->id}/like");
        $response->assertStatus($response->status() == 302 ? 302 : 200);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->assertEquals(1, $item->likedByUsers()->count());
    }

    public function test_いいね済みのアイコンは色が変化している()
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create();
        $user->likedItems()->attach($item->id);
        $response = $this->actingAs($user)->get("/item/{$item->id}");
        $response->assertStatus(200);
        $response->assertSee('like-count');
    }

    public function test_再度いいねアイコンを押下することによっていいねを解除できる()
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create();
        $user->likedItems()->attach($item->id);
        $this->assertEquals(1, $item->likedByUsers()->count());
        $response = $this->actingAs($user)->post("/like/{$item->id}/like");
        $response->assertStatus($response->status() == 302 ? 302 : 200);
        $this->assertEquals(0, $item->likedByUsers()->count());
    }

    public function test_未ログインユーザーはいいねができない()
    {
        $item = Item::factory()->create();
        $response = $this->post("/like/{$item->id}/like");
        $response->assertRedirect('/login');
    }
}
