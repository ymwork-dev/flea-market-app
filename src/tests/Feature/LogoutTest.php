<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログアウトができる()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
                        ->post('/logout');
        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
