<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\UserProfile;
use Mockery;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    // 「購入する」ボタンを押下すると購入が完了する
    public function test_user_can_purchase_item()
    {
        $buyer  = User::factory()->create();
        $seller = User::factory()->create();

        // 購入者のプロフィール（住所）を用意しておく
        UserProfile::create([
            'user_id'     => $buyer->id,
            'postal_code' => '123-4567',
            'address'     => 'テスト県テスト市1-2-3',
            'building'    => 'テストビル101',
        ]);

        // 購入対象の商品
        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '購入テスト商品',
            'brand_name'  => 'BrandPurchase',
            'description' => '購入機能テスト用の商品です。',
            'price'       => 3000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // Stripe のセッション作成をモック（ダミーのURLを返すだけ）
        Mockery::mock('alias:Stripe\Checkout\Session')
            ->shouldReceive('create')
            ->once()
            ->andReturn((object) [
                'id'  => 'cs_test_dummy',
                'url' => 'https://example.test/checkout',
            ]);

        // 1. ユーザーにログインする
        $this->actingAs($buyer);

        // 2. 「購入する」ボタン押下に相当する POST
        $response = $this->post(route('purchase.store', $item), [
            'payment_method' => 2,
        ]);

        // 購入処理が正常に終わってリダイレクトしていること
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // purchases テーブルにレコードが作成されていること
        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
        ]);
    }

    // 購入した商品は商品一覧画面にて「Sold」と表示される
    public function test_purchased_item_is_shown_as_sold_in_items_index()
    {
        $buyer  = User::factory()->create();
        $seller = User::factory()->create();

        // 購入者のプロフィール（住所）を用意
        UserProfile::create([
            'user_id'     => $buyer->id,
            'postal_code' => '987-6543',
            'address'     => 'テスト県テスト市9-8-7',
            'building'    => 'テストビル202',
        ]);

        // 購入対象の商品
        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => 'Sold表示テスト商品',
            'brand_name'  => 'BrandSold',
            'description' => 'Sold表示テスト用の商品です。',
            'price'       => 4000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

         // Stripe のセッション作成をモック
        Mockery::mock('alias:Stripe\Checkout\Session')
            ->shouldReceive('create')
            ->once()
            ->andReturn((object) [
                'id'  => 'cs_test_dummy',
                'url' => 'https://example.test/checkout',
            ]);

        // ログイン
        $this->actingAs($buyer);

        // 「購入する」ボタン押下に相当する POST
        $this->post(route('purchase.store', $item), [
            'payment_method' => 2,
        ])->assertStatus(302);

        // 商品一覧ページを表示
        $response = $this->get(route('items.index'));

        $response->assertStatus(200);

        // 一覧に商品名が出ている
        $response->assertSee($item->name);
        // 「Sold」のラベルが表示されている
        $response->assertSee('Sold');
    }

    // 「プロフィール/購入した商品一覧」に購入済みの商品だけが表示される
    public function test_purchased_items_appear_in_mypage_purchased_tab()
    {
        $buyer  = User::factory()->create();
        $seller = User::factory()->create();

        // 出品者が出品した商品を2つ用意
        $purchasedItem = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '購入済みの商品',
            'brand_name'  => 'BrandPurchased',
            'description' => '購入された商品です',
            'price'       => 5000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        $notPurchasedItem = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '未購入の商品',
            'brand_name'  => 'BrandNotPurchased',
            'description' => 'まだ購入されていない商品です',
            'price'       => 6000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 購入者が「購入した」というレコードを作成
        Purchase::create([
            'user_id'         => $buyer->id,
            'item_id'         => $purchasedItem->id,
            'payment_method'  => 1,
            'shipping_address'=> "〒123-4567\nテスト県テスト市1-2-3",
        ]);

        // ログイン状態にする
        $this->actingAs($buyer);

        // マイページの「購入した商品」タブにアクセス
        $response = $this->get(route('mypage.index', ['tab' => 'purchased']));

        $response->assertStatus(200);

        // 購入済み商品は表示されている
        $response->assertSee($purchasedItem->name);

        // 未購入の商品は表示されていない
        $response->assertDontSee($notPurchasedItem->name);
    }
}
