<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout()
    {
        // 1) ログイン済みユーザーを用意
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // 2) ユーザーとしてログイン状態にする
        $this->actingAs($user);

        // 3) /logout に POST してログアウト実行
        $response = $this->post(route('logout'));

        // 4) 商品一覧ページにリダイレクトされることを確認
        $response->assertRedirect(route('items.index'));

        // 5) ログアウトされている（＝未ログイン状態）ことを確認
        $this->assertGuest();
    }
}
