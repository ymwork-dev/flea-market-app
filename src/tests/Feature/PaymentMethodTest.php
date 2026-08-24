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

    public function test_支払い方法選択機能_小計画面で変更が反映される(): void
    {
        $user = $this->createFullAccessUser();
        $item = Item::factory()->create();
        $response = $this->actingAs($user)
            ->from("/purchase/{$item->id}")
            ->post("/purchase/{$item->id}", [
                'payment_method' => 'konbini'
            ]);

        $response = $this->actingAs($user)->get("/purchase/{$item->id}");
        $response->assertStatus(200);
        $response->assertSee('コンビニ払い');
    }
}
