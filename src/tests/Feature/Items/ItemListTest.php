<?php

namespace Tests\Feature\Items;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;

class ItemListTest extends TestCase
{
    use RefreshDatabase;

    // 全商品を取得できる
    public function test_guest_can_see_all_items()
    {
        // 出品者を2人作成
        $sellerA = User::factory()->create();
        $sellerB = User::factory()->create();

        // 商品を2つ作成（どちらも販売中）
        $itemA = Item::create([
            'seller_id'   => $sellerA->id,
            'name'        => '腕時計A',
            'brand_name'  => 'BrandA',
            'description' => '説明A',
            'price'       => 1000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        $itemB = Item::create([
            'seller_id'   => $sellerB->id,
            'name'        => '腕時計B',
            'brand_name'  => 'BrandB',
            'description' => '説明B',
            'price'       => 2000,
            'condition'   => 2,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 【手順】1. ゲストで商品一覧ページを開く
        $response = $this->get(route('items.index'));

        // 【期待】2. ステータス200 & 2つの商品名が画面に表示されている
        $response->assertStatus(200);
        $response->assertSee($itemA->name);
        $response->assertSee($itemB->name);
    }

    // 購入済み商品は「Sold」と表示される
    public function test_purchased_items_are_marked_as_sold_in_index()
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();

        // 販売中の商品を1件作成
        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '売れた商品',
            'brand_name'  => 'BrandX',
            'description' => '説明X',
            'price'       => 3000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 購入レコードを作成 → 「購入済み商品」という状態を再現
        Purchase::create([
            'user_id'          => $buyer->id,
            'item_id'          => $item->id,
            'payment_method'   => 1,
            'shipping_address' => "〒123-4567\n東京都テスト区テスト町1-2-3",
        ]);

        // 1. 商品一覧ページを開く
        $response = $this->get(route('items.index'));

        // ・商品名が表示されている
        // ・「Sold」というラベル文字列が表示されている
        $response->assertStatus(200);
        $response->assertSee($item->name);
        $response->assertSee('Sold');
    }

    // 自分が出品した商品は表示されない
    public function test_my_own_items_are_not_shown_in_index_when_logged_in()
    {
        $me      = User::factory()->create();
        $another = User::factory()->create();

        // 自分が出品した商品
        $myItem = Item::create([
            'seller_id'   => $me->id,
            'name'        => '自分の出品',
            'brand_name'  => 'MyBrand',
            'description' => '自分の商品です',
            'price'       => 4000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 他人が出品した商品
        $otherItem = Item::create([
            'seller_id'   => $another->id,
            'name'        => '他人の出品',
            'brand_name'  => 'OtherBrand',
            'description' => '他人の商品です',
            'price'       => 5000,
            'condition'   => 2,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 1. 自分としてログイン
        $this->actingAs($me);

        // 2. 商品一覧ページを開く
        $response = $this->get(route('items.index'));

        // ・自分の出品商品名は画面に出てこない
        // ・他人の商品は表示されている
        $response->assertStatus(200);
        $response->assertDontSee($myItem->name);
        $response->assertSee($otherItem->name);
    }
}
