<?php

namespace Tests\Feature\Like;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    // いいねアイコン押下で「いいねした商品として登録される」
    // 合計いいね数が1件増えていることをDBで確認
    public function test_logged_in_user_can_like_an_item()
    {
        $user   = User::factory()->create();
        $seller = User::factory()->create();

        // いいね対象の商品
        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => 'いいねテスト商品',
            'brand_name'  => 'BrandLike',
            'description' => 'いいね機能テスト用の商品です。',
            'price'       => 1000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 1. ユーザーにログインする
        $this->actingAs($user);

        // 2. まだ likes テーブルにレコードがないことを確認（初期状態）
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // 3. 商品詳細ページの「いいねアイコン押下」に相当するPOST
        $response = $this->post(route('likes.toggle', $item));

        // 戻り先は back() のため 302 リダイレクト
        $response->assertStatus(302);

        // → 「いいねした商品として登録される」ことをDBで確認
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // → 合計いいね数が1件になっている（画面のカウント表示の元になる値）
        $this->assertEquals(1, $item->likes()->count());
    }

    // 再度いいねアイコンを押下すると、いいねが解除される
    // 合計いいね数が1件減って0になることをDBで確認
    public function test_toggling_like_again_will_unlike_the_item()
    {
        $user   = User::factory()->create();
        $seller = User::factory()->create();

        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => 'いいね解除テスト商品',
            'brand_name'  => 'BrandUnlike',
            'description' => 'いいね解除テスト用の商品です。',
            'price'       => 2000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 事前に「いいね済み」状態を作っておく
        $user->likes()->create(['item_id' => $item->id]);

        $this->actingAs($user);

        // 1. 最初は likes にレコードがあること（いいね済み）
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // 2. 再度アイコン押下に相当するPOST（トグル）
        $response = $this->post(route('likes.toggle', $item));

        $response->assertStatus(302);

        // → レコードが削除され、「いいね解除」されていること
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // → 合計いいね数も0件になっている
        $this->assertEquals(0, $item->likes()->count());
    }

    // いいね済みのときはアイコンのクラスが変わる（＝クラスで色が変化した状態を表現）
    public function test_liked_icon_class_changes_after_like()
    {
        $user   = User::factory()->create();
        $seller = User::factory()->create();

        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => 'アイコンクラステスト商品',
            'brand_name'  => 'BrandIcon',
            'description' => 'アイコン色変化テスト用の商品です。',
            'price'       => 3000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 1. ログインする
        $this->actingAs($user);

        // 2. いいね前の詳細ページを開く（未いいね：fa-regular）
        $before = $this->get(route('items.show', $item));
        $before->assertStatus(200);
        $before->assertSee('fa-regular fa-heart like-icon');
        $before->assertDontSee('fa-solid fa-heart like-icon');

        // 3. いいねアイコン押下に相当するPOST
        $this->post(route('likes.toggle', $item));

        // 4. 再度詳細ページを開き、クラスが fa-solid に変わっていることを確認
        $after = $this->get(route('items.show', $item));
        $after->assertStatus(200);
        $after->assertSee('fa-solid fa-heart like-icon');
        $after->assertDontSee('fa-regular fa-heart like-icon');
    }
}
