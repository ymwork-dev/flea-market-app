<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;

class CommentReplyTest extends TestCase
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

    public function test_返信機能_出品者は質問コメントに返信できる(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id]);
        $comment = Comment::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'comment' => '色は何色ですか？',
        ]);

        $response = $this->actingAs($seller)->post(
            "/comment/{$item->id}/comment/{$comment->id}/reply",
            ['comment' => '黒色です']
        );

        $response->assertStatus(302);
        $this->assertDatabaseHas('comments', [
            'item_id' => $item->id,
            'parent_id' => $comment->id,
            'user_id' => $seller->id,
            'comment' => '黒色です',
        ]);
    }

    public function test_返信機能_出品者以外は返信できない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id]);
        $comment = Comment::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'comment' => '色は何色ですか？',
        ]);

        $response = $this->actingAs($buyer)->post(
            "/comment/{$item->id}/comment/{$comment->id}/reply",
            ['comment' => '黒色です']
        );

        $response->assertStatus(403);
    }

    public function test_商品詳細_返信は元のコメントの下に出品者ラベル付きで表示される(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id]);
        $comment = Comment::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'comment' => '色は何色ですか？',
        ]);
        Comment::create([
            'user_id' => $seller->id,
            'item_id' => $item->id,
            'comment' => '黒色です',
            'parent_id' => $comment->id,
        ]);

        $response = $this->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('色は何色ですか？');
        $response->assertSee('黒色です');
        $response->assertSee('出品者');
    }

    public function test_商品詳細_出品者本人以外には返信フォームが表示されない(): void
    {
        $seller = $this->createFullAccessUser();
        $buyer = $this->createFullAccessUser();
        $item = Item::factory()->create(['user_id' => $seller->id]);
        Comment::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'comment' => '色は何色ですか？',
        ]);

        $response = $this->actingAs($buyer)->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertDontSee('このコメントに返信する');
    }
}
