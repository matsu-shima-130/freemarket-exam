<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    // 会員登録後、「認証メールを送信」すると登録したアドレス宛にメールが飛ぶ
    public function test_verification_email_can_be_sent_to_registered_user()
    {
        Notification::fake();

        // 1. まだメール未認証のユーザーを用意
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 2. ログイン状態にする
        $this->actingAs($user);

        // 3. 「認証メールを送信する」ボタン押下に相当するリクエスト
        $response = $this->post('/email/verification-notification');

        // 4. 正常にリダイレクトしていること
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // 5. VerifyEmail 通知がそのユーザー宛に送られていること
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    // メール認証誘導画面で「認証はこちらから」ボタンが表示されている
    public function test_verification_notice_page_has_link_button()
    {
        // 1. 未認証ユーザーでログイン
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        // 2. メール認証誘導画面を開く（/email/verify 想定）
        $response = $this->get('/email/verify');

        // 3. 画面が表示されている
        $response->assertStatus(200);

        // 4. 「認証はこちらから」というボタン(リンク)の文言があること
        $response->assertSee('認証はこちらから');
    }

    // メール認証完了後、プロフィール設定画面に遷移する
    public function test_email_verification_redirects_to_profile_edit_page()
    {
        // 1. 未認証ユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        // 2. メール内の「認証リンク」に相当する URL を生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id'   => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        // 3. 認証リンクにアクセス（= メール認証完了）
        $response = $this->get($verificationUrl);

        // 4. プロフィール設定画面にリダイレクトされる想定
        //    ※ プロフィール設定のルート名が profile.edit である前提
        $response->assertRedirect(
            route('profile.edit', ['verified' => 1])
        );

        // 5. ユーザーが「メール認証済み」になっていること
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
