<?php

namespace Tests\Feature\Items;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;

class ItemStoreTest extends TestCase
{
    use RefreshDatabase;

    // 商品出品画面にて必要な情報が保存できること（カテゴリ、商品の状態、商品名、ブランド名、商品の説明、販売価格）
    public function test_user_can_create_item_with_all_required_fields()
    {
        // 画像保存先をテスト用に差し替え
        Storage::fake('public');

        // 1. ログインユーザー（出品者）を用意
        $seller = User::factory()->create();

        // 2. カテゴリを複数作成しておく
        $cat1 = Category::create(['name' => 'ファッション']);
        $cat2 = Category::create(['name' => 'メンズ']);

        // 3. ログイン状態にする
        $this->actingAs($seller);

        // ダミーの画像ファイルを用意
        $imageFile = UploadedFile::fake()->create(
            'item.jpg',      // ファイル名
            200,             // ファイルサイズ(kB) 適当でOK
            'image/jpeg'     // MIMEタイプ
        );

        // 4. 出品フォーム送信（= 商品出品画面で各項目を入力して保存するイメージ）
        $response = $this->post(route('sell.store'), [
            'name'        => 'テスト商品',
            'brand_name'  => 'テストブランド',
            'description' => 'これはテスト用の商品説明です。',
            'price'       => 12345,
            'condition'   => 2,
            'category_ids'  => [$cat1->id, $cat2->id],
            'image'       => $imageFile,
        ]);

        // 5. バリデーションエラーなどなくリダイレクトしていること
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // 6. items テーブルに正しく保存されているか確認
        $item = Item::first();
        $this->assertNotNull($item, 'itemsテーブルにレコードが1件もありません');

        // 出品者ID
        $this->assertEquals($seller->id, $item->seller_id);

        // 商品名
        $this->assertEquals('テスト商品', $item->name);

        // ブランド名
        $this->assertEquals('テストブランド', $item->brand_name);

        // 商品説明
        $this->assertEquals('これはテスト用の商品説明です。', $item->description);

        // 価格
        $this->assertEquals(12345, $item->price);

        // 商品の状態
        $this->assertEquals(2, $item->condition);

        // 7. 中間テーブル（item_categories）にもカテゴリが紐づいていることを確認
        $this->assertEquals(2, $item->categories()->count());
        $this->assertTrue($item->categories->contains('id', $cat1->id));
        $this->assertTrue($item->categories->contains('id', $cat2->id));

        // 画像パスが保存されていて、実際にストレージにあること
        $this->assertNotNull($item->image_path);
        Storage::disk('public')->assertExists($item->image_path);
    }
}
