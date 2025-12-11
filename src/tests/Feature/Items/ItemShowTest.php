<?php

namespace Tests\Feature\Items;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Comment;

class ItemsShowTest extends TestCase
{
    use RefreshDatabase;

    // 商品詳細ページで基本情報とカテゴリが表示される
    public function test_item_detail_shows_basic_information_and_categories()
    {
        $seller = User::factory()->create();

        // カテゴリを2つ作成（複数カテゴリ表示の確認用）
        $cat1 = Category::create(['name' => 'ファッション']);
        $cat2 = Category::create(['name' => 'メンズ']);

        // 商品を1件作成（商品名・ブランド名・説明をチェックする）
        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => 'テスト腕時計',
            'brand_name'  => 'TestBrand',
            'description' => 'これはテスト用の腕時計です。',
            'price'       => 12345,
            'condition'   => 1,   // 商品状態: 良好 など（ビュー側で表示）
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 中間テーブルでカテゴリを紐づけ
        $item->categories()->sync([$cat1->id, $cat2->id]);

        // 商品詳細ページへアクセス
        $response = $this->get(route('items.show', $item));

        $response->assertStatus(200);

        // 商品の基本情報が表示されているか
        $response->assertSee($item->name);
        $response->assertSee($item->brand_name);
        $response->assertSee($item->description);

        // 複数選択されたカテゴリ名が両方表示されているか
        $response->assertSee($cat1->name);
        $response->assertSee($cat2->name);
    }

    // コメント一覧とコメントユーザー情報が表示される
    public function test_item_detail_shows_comments_and_comment_users()
    {
        $seller        = User::factory()->create();
        $commentUser1  = User::factory()->create(['name' => 'コメントユーザー1']);
        $commentUser2  = User::factory()->create(['name' => 'コメントユーザー2']);

        // コメントを付ける対象の商品
        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => 'コメント付き商品',
            'brand_name'  => 'BrandC',
            'description' => 'コメントテスト用の商品です。',
            'price'       => 2000,
            'condition'   => 2,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // コメントを2件作成（ユーザーとコメント内容の表示確認用）
        Comment::create([
            'user_id' => $commentUser1->id,
            'item_id' => $item->id,
            'body'    => 'これは最初のコメントです。',
        ]);

        Comment::create([
            'user_id' => $commentUser2->id,
            'item_id' => $item->id,
            'body'    => 'これは2つ目のコメントです。',
        ]);

        // 商品詳細ページへアクセス
        $response = $this->get(route('items.show', $item));

        $response->assertStatus(200);

        // 商品名が表示されている
        $response->assertSee($item->name);

        // コメントしたユーザー名とコメント内容が表示されている
        $response->assertSee('コメントユーザー1');
        $response->assertSee('コメントユーザー2');
        $response->assertSee('これは最初のコメントです。');
        $response->assertSee('これは2つ目のコメントです。');
    }
}
