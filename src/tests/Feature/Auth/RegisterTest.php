<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** 名前が未入力だとエラーになる */
    public function test_name_is_required()
    {
        $response = $this->post(route('register'), [
            'name'                  => '',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'name' => 'お名前を入力してください',
            ]);
    }

    /** メールアドレスが未入力だとエラーになる */
    public function test_email_is_required()
    {
        $response = $this->post(route('register'), [
            'name'                  => 'テストユーザー',
            'email'                 => '',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
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
        $response = $this->post(route('register'), [
            'name'                  => 'テストユーザー',
            'email'                 => 'test@example.com',
            'password'              => '',
            'password_confirmation' => '',
        ]);

        $response
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'password' => 'パスワードを入力してください',
            ]);
    }

    /** パスワードが7文字以下だとエラーになる */
    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this->post(route('register'), [
            'name'                  => 'テストユーザー',
            'email'                 => 'test@example.com',
            'password'              => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'password' => 'パスワードは8文字以上で入力してください',
            ]);
    }

    /** パスワードと確認用が一致しないとエラーになる */
    public function test_password_confirmation_must_match()
    {
        $response = $this->post(route('register'), [
            'name'                  => 'テストユーザー',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'password' => 'パスワードと一致しません',
            ]);
    }

    /** 正しい入力ならユーザーが登録され、メール認証画面にリダイレクトされる */
    public function test_valid_data_registers_user_and_redirects_to_verification()
    {
        $response = $this->post(route('register'), [
            'name'                  => 'テストユーザー',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertAuthenticated();
        $this->assertAuthenticatedAs(
            User::where('email', 'test@example.com')->first()
        );

        $response->assertRedirect(route('verification.notice'));
    }
}
