<?php

namespace Tests\Feature\Mypage;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\UserProfile;

class MypageProfileEditTest extends TestCase
{
    use RefreshDatabase;

    // 変更項目が初期値として過去設定されていること（プロフィール画像、ユーザー名、郵便番号、住所）
    public function test_profile_edit_form_shows_initial_values()
    {
        // 1. ユーザーとプロフィールを用意
        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        $profile = UserProfile::create([
            'user_id'     => $user->id,
            'postal_code' => '123-4567',
            'address'     => 'テスト県テスト市1-2-3',
            'building'    => 'テストビル101',
            'avatar_path' => 'avatars/test-avatar.png',
        ]);

        // 2. ログイン状態にする
        $this->actingAs($user);

        // 3. プロフィール編集ページを開く
        $response = $this->get('/mypage/profile');

        $response->assertStatus(200);

        // 4. 画面に初期値が表示されていることを確認
        // ユーザー名
        $response->assertSee('テストユーザー');

        // 郵便番号
        $response->assertSee('123-4567');

        // 住所・建物名
        $response->assertSee('テスト県テスト市1-2-3');
        $response->assertSee('テストビル101');

        // プロフィール画像のパスがHTML内に含まれていること
        $response->assertSee('storage/' . $profile->avatar_path);
    }
}
