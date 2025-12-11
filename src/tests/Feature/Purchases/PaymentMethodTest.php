<?php

namespace Tests\Feature\Purchase;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_method_select_and_summary_are_rendered()
    {
        // 購入者 & 出品者
        $buyer  = User::factory()->create();
        $seller = User::factory()->create();

        // 購入画面に表示する商品
        $item = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '支払い方法テスト商品',
            'brand_name'  => 'BrandPay',
            'description' => '支払い方法テスト用の商品です。',
            'price'       => 3000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 1. ユーザーにログインする
        $this->actingAs($buyer);

        // 2. 支払い方法選択画面（購入画面）を開く
        //    → purchases/index.blade.php が表示されるルート
        $response = $this->get(route('purchase.index', $item));

        // 3. 画面が正常表示されること
        $response->assertStatus(200);

        // 4. プルダウンの選択肢（コンビニ払い / カード払い）が表示されていること
        $response->assertSee('コンビニ払い');
        $response->assertSee('カード払い');

        // 5. 小計欄に「支払い方法」と初期値「未選択」が表示されていること
        $response->assertSee('支払い方法');
        $response->assertSee('未選択');

        // 6. JavaScript が参照する id 属性が HTML 上に存在していること
        //    第2引数に false を渡して「エスケープなし」で生のHTML文字列をチェック
        $response->assertSee('id="payment-method-select"', false);
        $response->assertSee('id="summary-payment-label"', false);

        // ※ 実際の「セレクト変更でラベルが書き換わる」動作はブラウザ上の JS の役割なので、
        //    PHPUnit（サーバーサイドのテスト）ではここまでをサーバー側の責務として確認している。
    }
}
