<?php

namespace Tests\Feature\Items;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;

class ItemSearchTest extends TestCase
{
    use RefreshDatabase;

    // 「商品名」で部分一致検索ができる
    public function test_search_by_name_returns_partial_matches()
    {
        $seller = User::factory()->create();

        // 「腕時計」を含む商品名2件
        $watch1 = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '青い腕時計',
            'brand_name'  => 'BrandA',
            'description' => '腕時計その1',
            'price'       => 5000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        $watch2 = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '赤い腕時計',
            'brand_name'  => 'BrandB',
            'description' => '腕時計その2',
            'price'       => 6000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // キーワードに一致しない商品
        $bag = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '黒いバッグ',
            'brand_name'  => 'BrandC',
            'description' => 'バッグ',
            'price'       => 7000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // keyword=「腕時計」で検索付きの一覧ページを開く
        // （「検索欄に入力してボタン押下」の代わりに、クエリパラメータで再現）
        $response = $this->get(route('items.index', ['keyword' => '腕時計']));

        // 「腕時計」を含む商品だけが表示される
        $response->assertStatus(200);
        $response->assertSee($watch1->name);
        $response->assertSee($watch2->name);
        $response->assertDontSee($bag->name);
    }

    // 検索状態がマイリストでも保持されている
    public function test_mylist_respects_search_keyword()
    {
        $user   = User::factory()->create();
        $seller = User::factory()->create();

        // 「腕時計」としてヒットさせたい & いいねもする商品
        $likedAndMatch = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '白い腕時計',
            'brand_name'  => 'Brand1',
            'description' => '腕時計',
            'price'       => 8000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // いいねはしているが、キーワード「腕時計」ではヒットさせたくない商品
        $likedButNotMatch = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '黒いバッグ',
            'brand_name'  => 'Brand2',
            'description' => 'バッグ',
            'price'       => 9000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 「腕時計」には一致するが、いいねしていない商品
        $notLikedButMatch = Item::create([
            'seller_id'   => $seller->id,
            'name'        => '青い腕時計',
            'brand_name'  => 'Brand3',
            'description' => '腕時計',
            'price'       => 10000,
            'condition'   => 1,
            'status'      => 0,
            'image_path'  => null,
        ]);

        // 1,2 にだけ「いいね」
        $user->likes()->create(['item_id' => $likedAndMatch->id]);
        $user->likes()->create(['item_id' => $likedButNotMatch->id]);

        // 1. ログイン状態にする
        $this->actingAs($user);

        // 2. 「マイリスト」タブ + keyword=腕時計 で一覧ページを開く
        //   （＝「検索状態がマイリストでも保持されている」状態をURLで再現）
        $response = $this->get(route('items.index', [
            'tab'     => 'mylist',
            'keyword' => '腕時計',
        ]));

        // ・「マイリスト」かつ「腕時計」で該当する likedAndMatch のみ表示される
        // ・いいね済みでもキーワード不一致の商品は表示されない
        // ・キーワード一致でも、いいねしていない商品は表示されない
        $response->assertStatus(200);
        $response->assertSee($likedAndMatch->name);
        $response->assertDontSee($likedButNotMatch->name);
        $response->assertDontSee($notLikedButMatch->name);
    }
}
