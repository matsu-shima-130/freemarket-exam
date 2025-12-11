<?php

namespace Tests\Feature\Comments;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    // ログイン済みのユーザーはコメントを送信できる
    public function test_logged_in_user_can_post_comment()
    {
        $user   = User::factory()->create();
        $seller = User::factory()->create();

        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => 'コメント対象商品',
            'brand_name'  => 'BrandX',
            'description' => '説明テスト',
            'price'       => 1000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('comments.store', $item), [
            'body' => 'テストコメントです。',
        ]);

        // バリデーションエラーなくリダイレクトしていること
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // コメントが保存されているか確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'body'    => 'テストコメントです。',
        ]);
    }

    // ログイン前のユーザーはコメントを送信できない
    public function test_guest_cannot_post_comment()
    {
        $seller = User::factory()->create();

        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => 'ゲストコメント商品',
            'brand_name'  => 'BrandY',
            'description' => '説明テスト',
            'price'       => 2000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // ログインしていない状態でコメント投稿を試みる
        $response = $this->post(route('comments.store', $item), [
            'body' => 'ゲストのコメント',
        ]);

        $response->assertRedirect(route('login'));

        // 保存されていないか確認
        $this->assertDatabaseMissing('comments', [
            'body' => 'ゲストのコメント',
        ]);
    }

    // コメントが入力されていない場合、バリデーションメッセージが表示される
    public function test_body_is_required()
    {
        $user   = User::factory()->create();
        $seller = User::factory()->create();

        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => 'バリデーション商品1',
            'brand_name'  => 'BrandZ',
            'description' => '説明テスト',
            'price'       => 3000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        $this->actingAs($user);

        $response = $this->from(route('items.show', $item))
            ->post(route('comments.store', $item), [
                // 空文字 or 空白のみ
                'body' => '   ',
            ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'body' => 'コメントを入力してください。',
        ]);

        // 保存されていないか確認
        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'user_id' => $user->id,
        ]);
    }

    // コメントが255字以上の場合、バリデーションメッセージが表示される
    public function test_body_must_not_exceed_255_characters()
    {
        $user   = User::factory()->create();
        $seller = User::factory()->create();

        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => 'バリデーション商品2',
            'brand_name'  => 'BrandZ',
            'description' => '説明テスト',
            'price'       => 4000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        $this->actingAs($user);

        // 256文字の長文コメント
        $longText = str_repeat('あ', 256);

        $response = $this->from(route('items.show', $item))
            ->post(route('comments.store', $item), [
                'body' => $longText,
            ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'body' => 'コメントは255文字以内で入力してください。',
        ]);

        // 保存されていないか確認
        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'user_id' => $user->id,
        ]);
    }
}
