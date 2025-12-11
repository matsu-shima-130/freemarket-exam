<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;
use App\Models\UserProfile;
use App\Models\Purchase;

class PurchaseAddressTest extends TestCase
{
    use RefreshDatabase;

    // 送付先住所変更画面にて登録した住所が商品購入画面に反映されている
    public function test_updated_address_is_reflected_on_purchase_screen()
    {
        $buyer  = User::factory()->create();
        $seller = User::factory()->create();

        // 購入対象の商品
        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '住所反映テスト商品',
            'brand_name'  => 'BrandAddress',
            'description' => '住所反映テスト用の商品です。',
            'price'       => 3000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 初期プロフィールを作成（値はなんでもOK）
        $profile = UserProfile::create([
            'user_id'     => $buyer->id,
            'postal_code' => '000-0000',
            'address'     => '初期住所',
            'building'    => null,
        ]);

        // 1. ユーザーにログインする
        $this->actingAs($buyer);

        // 2. 送付先住所変更画面で住所を登録する、に相当する処理
        $profile->update([
            'postal_code' => '111-2222',
            'address'     => 'テスト県テスト市1-2-3',
            'building'    => 'テストビル101',
        ]);

        // 3. 商品購入画面を再度開く
        $response = $this->get(route('purchase.index', $item));

        $response->assertStatus(200);

        // purchases/index.blade.php の表示に更新後の住所がそのまま反映されていること
        $response->assertSee('111-2222');
        $response->assertSee('テスト県テスト市1-2-3');
        $response->assertSee('テストビル101');
    }

    // 購入した商品に送付先住所が紐づいて登録される
    public function test_purchase_record_has_shipping_address_from_profile()
    {
        $buyer  = User::factory()->create();
        $seller = User::factory()->create();

        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '住所紐づけテスト商品',
            'brand_name'  => 'BrandShip',
            'description' => '住所紐づけテスト用の商品です。',
            'price'       => 4000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // プロフィールに登録した住所
        $profile = UserProfile::create([
            'user_id'     => $buyer->id,
            'postal_code' => '333-4444',
            'address'     => 'テスト府テスト市9-9-9',
            'building'    => 'テストマンション303',
        ]);

        // プロフィール情報から shipping_address を組み立てている想定
        $shippingAddress = "〒{$profile->postal_code}\n{$profile->address}\n{$profile->building}";

        // 「購入が完了してこれが保存された」状態を再現
        $purchase = Purchase::create([
            'user_id'         => $buyer->id,
            'item_id'         => $item->id,
            'payment_method'  => 1,
            'shipping_address'=> $shippingAddress,
        ]);

        // 指定ユーザー＆商品で購入レコードがあること
        $this->assertDatabaseHas('purchases', [
            'id'      => $purchase->id,
            'user_id' => $buyer->id,
            'item_id' => $item->id,
        ]);

        // shipping_address に、プロフィールから組み立てた住所がそのまま入っていること
        $this->assertSame($shippingAddress, $purchase->shipping_address);
    }
}
