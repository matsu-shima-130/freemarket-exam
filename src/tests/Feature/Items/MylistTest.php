<?php

namespace Tests\Feature\Items;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;

class MylistTest extends TestCase
{
    use RefreshDatabase;

    // いいねした商品だけが表示される
    public function test_mylist_shows_only_liked_items_for_logged_in_user()
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();

        // 他人が出品した商品を3つ用意
        $itemLiked1 = Item::create([
            'seller_id'   => $otherUser->id,
            'name'        => 'いいね商品1',
            'brand_name'  => 'Brand1',
            'description' => '説明1',
            'price'       => 1000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        $itemLiked2 = Item::create([
            'seller_id'   => $otherUser->id,
            'name'        => 'いいね商品2',
            'brand_name'  => 'Brand2',
            'description' => '説明2',
            'price'       => 2000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        $itemNotLiked = Item::create([
            'seller_id'   => $otherUser->id,
            'name'        => 'いいねしてない商品',
            'brand_name'  => 'Brand3',
            'description' => '説明3',
            'price'       => 3000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 1,2 にだけ「いいね」する
        $user->likes()->create(['item_id' => $itemLiked1->id]);
        $user->likes()->create(['item_id' => $itemLiked2->id]);

        // 1. ログイン状態にする
        $this->actingAs($user);

        // 2. 「マイリスト」タブの一覧ページを開く
        $response = $this->get(route('items.index', ['tab' => 'mylist']));

        // いいねした商品だけ表示されていること
        $response->assertStatus(200);
        $response->assertSee($itemLiked1->name);
        $response->assertSee($itemLiked2->name);
        $response->assertDontSee($itemNotLiked->name);
    }

     // 未認証の場合は何も表示されない
    public function test_mylist_is_empty_for_guest()
    {
        $user = User::factory()->create();

        // ログインユーザーが「いいね」している商品
        $item = Item::create([
            'seller_id'   => $user->id,
            'name'        => 'ゲストからは見えない商品',
            'brand_name'  => 'BrandX',
            'description' => '説明X',
            'price'       => 1000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        $user->likes()->create(['item_id' => $item->id]);

        // ゲスト状態でマイリストページを開く
        $response = $this->get(route('items.index', ['tab' => 'mylist']));
        // この商品名は表示されない（＝何も表示されない想定）
        $response->assertStatus(200);
        $response->assertDontSee($item->name);
    }

    // 購入済み商品は「Sold」と表示される
    public function test_purchased_items_in_mylist_are_marked_as_sold()
    {
        $user   = User::factory()->create();
        $seller = User::factory()->create();

        // マイリストに表示される対象の商品
        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => 'マイリストの購入済み商品',
            'brand_name'  => 'BrandSold',
            'description' => 'マイリストSold表示テスト',
            'price'       => 5000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // いいね & 購入済み状態にしておく
        $user->likes()->create(['item_id' => $item->id]);

        Purchase::create([
            'user_id'          => $user->id,
            'item_id'          => $item->id,
            'payment_method'   => 1,
            'shipping_address' => "〒123-4567\n東京都テスト区テスト町1-2-3",
        ]);

        // 1. ログイン状態でマイリストを開く
        $this->actingAs($user);

        $response = $this->get(route('items.index', ['tab' => 'mylist']));

        // 商品名と「Sold」が両方表示されている
        $response->assertStatus(200);
        $response->assertSee($item->name);
        $response->assertSee('Sold');
    }
}
