<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** メールアドレスが未入力だとエラーになる */
    public function test_email_is_required()
    {
        $response = $this->post(route('login.attempt'), [
            'email'    => '',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'email' => 'メールアドレスを入力してください',
            ]);
    }

    /** パスワードが未入力だとエラーになる */
    public function test_password_is_required()
    {
        $response = $this->post(route('login.attempt'), [
            'email'    => 'test@example.com',
            'password' => '',
        ]);

        $response
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'password' => 'パスワードを入力してください',
            ]);
    }

    /** 間違った情報だと「ログイン情報が登録されていません」と出る */
    public function test_invalid_credentials_show_login_error()
    {
        // ユーザーは一応作っておく（でも違う情報でログインを試す）
        User::factory()->create([
            'email' => 'real@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->from(route('login'))
            ->post(route('login.attempt'), [
                'email'    => 'wrong@example.com',
                'password' => 'password123',
            ]);

        $response
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'login' => 'ログイン情報が登録されていません',
            ])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /** 正しい情報ならログインして商品一覧へリダイレクトされる */
    public function test_valid_credentials_log_in_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            // verified ミドルウェアを通るようにメール認証済み状態にしておく
            'email_verified_at' => now(),
        ]);

        $response = $this->post(route('login.attempt'), [
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('items.index'));

        $this->assertAuthenticatedAs($user);
    }
}
