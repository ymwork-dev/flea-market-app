<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Notifications\CommentPostedNotification;

class CommentTest extends TestCase
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

    public function test_コメント送信機能_ログイン済みのユーザーはコメントを送信できる(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post("/comment/{$item->id}/comment", [
            'comment' => '仕様書に準拠したテストコメントです'
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('comments', [
            'comment' => '仕様書に準拠したテストコメントです',
            'user_id' => $user->id,
            'item_id' => $item->id
        ]);

        $this->assertEquals(1, $item->comments()->count());
    }

    public function test_コメント送信機能_コメントすると出品者に通知メールが届く(): void
    {
        Notification::fake();

        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id]);

        $this->actingAs($buyer)->post("/comment/{$item->id}/comment", [
            'comment' => '質問があります'
        ]);

        Notification::assertSentTo($seller, CommentPostedNotification::class);
    }

    public function test_コメント送信機能_自分の商品に自分でコメントしても通知は届かない(): void
    {
        Notification::fake();

        $seller = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id]);

        $this->actingAs($seller)->post("/comment/{$item->id}/comment", [
            'comment' => '自分の商品への補足です'
        ]);

        Notification::assertNothingSent();
    }

    public function test_コメント送信機能_ログイン前のユーザーはコメントを送信できない(): void
    {
        $item = Item::factory()->create();

        $response = $this->post("/comment/{$item->id}/comment", [
            'comment' => '未ログインコメント'
        ]);

        $response->assertRedirect('/login');
        $this->assertEquals(0, $item->comments()->count());
    }

    public function test_コメント送信機能_コメントが入力されていない場合バリデーションメッセージが表示される(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->from("/item/{$item->id}")
            ->post("/comment/{$item->id}/comment", [
                'comment' => ''
            ]);

        $response->assertStatus(302);
        $response->assertRedirect("/item/{$item->id}");
        $response->assertSessionHasErrors(['comment']);
    }

    public function test_コメント送信機能_コメントが255字以上の場合バリデーションメッセージが表示される(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->from("/item/{$item->id}")
            ->post("/comment/{$item->id}/comment", [
                'comment' => str_repeat('あ', 256)
            ]);

        $response->assertStatus(302);
        $response->assertRedirect("/item/{$item->id}");
        $response->assertSessionHasErrors(['comment']);
    }
}
