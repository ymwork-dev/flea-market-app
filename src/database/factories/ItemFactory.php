<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => 'テスト商品',
            'price' => 1000,
            'description' => 'テスト説明文',
            'condition' => '良好',
            'img_url' => 'https://example.com',
            'is_sold' => false,
        ];
    }
}
