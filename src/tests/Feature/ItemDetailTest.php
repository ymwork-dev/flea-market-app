<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_商品詳細情報取得_必要な情報が表示される(): void
    {
        $otherUser = User::factory()->create();

        $commentUser = User::factory()->create(['name' => 'コメントしたユーザー名']);
        $item = Item::create([
            'name' => '腕時計',
            'price' => 15000,
            'brand' => 'Rolax',
            'description' => 'スタイリッシュなデザインのメンズ腕時計',
            'condition' => '良好',
            'user_id' => $otherUser->id,
            'img_url' => 'Rolax+Mens+Clock.jpg'
        ]);

        $category = Category::create(['name' => 'テストカテゴリ']);

        if (method_exists($item, 'categories')) {
            $item->categories()->attach($category->id);
        }

        Comment::create([
            'comment' => 'テスト用の具体的なコメント内容です。',
            'user_id' => $commentUser->id,
            'item_id' => $item->id,
        ]);

        $response = $this->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('Rolax+Mens+Clock.jpg');
        $response->assertSee('腕時計');
        $response->assertSee('Rolax');
        $response->assertSee('15,000');
        $response->assertSee('スタイリッシュなデザインのメンズ腕時計');
        $response->assertSee('良好');
        $response->assertSee('テストカテゴリ');
        $response->assertSee('コメントしたユーザー名');
        $response->assertSee('テスト用の具体的なコメント内容です。');
    }

    public function test_商品詳細情報取得_複数選択されたカテゴリが表示されているか(): void
    {
        $otherUser = User::factory()->create();

        $item = Item::create([
            'name' => '複数カテゴリ商品',
            'price' => 5000,
            'brand' => 'テストブランド',
            'description' => '商品説明文です。',
            'condition' => '普通',
            'user_id' => $otherUser->id,
            'img_url' => 'amazonaws.com'
        ]);

        $category1 = Category::create(['name' => 'ファッション']);
        $category2 = Category::create(['name' => 'メンズ']);
        $category3 = Category::create(['name' => 'アウター']);

        if (method_exists($item, 'categories')) {
            $item->categories()->attach([$category1->id, $category2->id, $category3->id]);
        }

        $response = $this->get("/item/{$item->id}");
        $response->assertStatus(200);
        $response->assertSee('ファッション');
        $response->assertSee('メンズ');
        $response->assertSee('アウター');
    }
}
