<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Testing\FileFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SellTest extends TestCase
{
    use RefreshDatabase;

    public function test_出品商品情報登録_商品出品画面にて必要な情報が保存できること(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'postcode' => '123-4567',
            'address' => '東京都渋谷区',
        ]);

        $responseGet = $this->actingAs($user)->get('/sell');
        $responseGet->assertStatus(200);
        $category = Category::create(['name' => 'ファッション']);

        Storage::fake('public');
        $file = UploadedFile::fake()->create('Armani_Mens_Clock.png', 100, 'image/png');

        $itemData = [
            'category_ids' => [$category->id], // 必須カテゴリーを選択
            'condition'    => '良好',
            'name'         => '腕時計',
            'brand'        => 'Rolax',
            'description'  => 'スタイリッシュなデザイン of メンズ腕時計',
            'price'        => 15000,
            'img_url'      => $file,
        ];

        $responsePost = $this->actingAs($user)
            ->from('/sell')
            ->post('/sell', $itemData);

        if (session()->has('errors')) {
            $responsePost->dumpSession();
        }

        $responsePost->assertStatus(302);

        $this->assertDatabaseHas('items', [
            'name'         => '腕時計',
            'price'        => 15000,
            'brand'        => 'Rolax',
            'description'  => 'スタイリッシュなデザイン of メンズ腕時計',
            'condition'    => '良好',
            'user_id'      => $user->id,
        ]);
    }
}
