<?php

namespace Tests\Feature\Mypage;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\UserProfile;

class MypageProfileTest extends TestCase
{
    use RefreshDatabase;

    // 必要な情報が取得できる（プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧）
    public function test_profile_page_shows_user_info_and_item_lists()
    {
        // 1. ログインユーザーとプロフィールを用意
        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        UserProfile::create([
            'user_id'     => $user->id,
            'postal_code' => '123-4567',
            'address'     => 'テスト県テスト市1-2-3',
            'building'    => 'テストビル101',
        ]);

        // 2. ログインユーザーが「出品した商品」を1件作成
        $myListedItem = Item::create([
            'seller_id'   => $user->id,
            'name'        => '自分が出品した商品',
            'brand_name'  => 'MyBrand',
            'description' => '自分が出品した商品の説明',
            'price'       => 5000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 3. 他ユーザー & 「自分が購入した商品」を作成
        $otherSeller = User::factory()->create();

        $purchasedItem = Item::create([
            'seller_id'   => $otherSeller->id,
            'name'        => '自分が購入した商品',
            'brand_name'  => 'PurchasedBrand',
            'description' => '自分が購入した商品の説明',
            'price'       => 6000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 購入レコードを作成 → 「購入した商品一覧」に出てきてほしい対象
        Purchase::create([
            'user_id'         => $user->id,
            'item_id'         => $purchasedItem->id,
            'payment_method'  => 1,
            'shipping_address'=> "〒123-4567\nテスト県テスト市1-2-3\nテストビル101",
        ]);

        // 4. 混ざってほしくない「他人の商品」も用意
        $noiseItem = Item::create([
            'seller_id'   => $otherSeller->id,
            'name'        => '他人の商品（表示されてほしくない）',
            'brand_name'  => 'NoiseBrand',
            'description' => 'ノイズ用の商品です',
            'price'       => 7000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 5. ログイン状態にする
        $this->actingAs($user);

        // 6-1. 出品した商品タブ（tab=selling）を開く
        $responseSelling = $this->get(route('mypage.index', ['tab' => 'selling']));

        $responseSelling->assertStatus(200);

        // ユーザー名が表示されている
        $responseSelling->assertSee('テストユーザー');

        // 出品した商品一覧に「自分が出品した商品」が含まれている
        $responseSelling->assertSee($myListedItem->name);

        // このタブでは「自分が購入した商品」は表示されない想定
        $responseSelling->assertDontSee($purchasedItem->name);

        // ノイズ用の他人の商品も表示されない想定
        $responseSelling->assertDontSee($noiseItem->name);

        // 6-2. 購入した商品タブ（tab=purchased）を開く
        $responsePurchased = $this->get(route('mypage.index', ['tab' => 'purchased']));

        $responsePurchased->assertStatus(200);

        // ユーザー名が表示されている（どのタブでも共通）
        $responsePurchased->assertSee('テストユーザー');

        // 購入した商品一覧に「自分が購入した商品」が含まれている
        $responsePurchased->assertSee($purchasedItem->name);

        // このタブでは「自分が出品した商品」は表示されない想定
        $responsePurchased->assertDontSee($myListedItem->name);

        // ノイズ用の他人の商品も表示されない想定
        $responsePurchased->assertDontSee($noiseItem->name);
    }
}
