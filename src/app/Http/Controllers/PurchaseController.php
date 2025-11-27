<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{

    public function index(Item $item)
    {
        $user    = Auth::user();
        $profile = $user?->profile;

        return view('purchases.index', [
            'item'    => $item,
            'profile' => $profile,
        ]);
    }

    public function store(Request $request, Item $item)
    {
        // 1) 自分の出品は購入NG
        if ($item->seller_id === auth()->id()) {
            return back()->with('error', '自分の商品は購入できません。');
        }

        // 2) 既に購入済みならNG
        if ($item->purchase()->exists() || (int)($item->status ?? 0) === 1) {
            return back()->with('error', 'この商品は売り切れです。');
        }

        // 3) 支払い方法のバリデーション
        $validated = $request->validate([
            'payment_method' => ['required', 'integer', 'in:1,2'],  // 1 or 2 だけ許可
        ]);

        // 4) プロフィールから住所情報を取得
        $profile = Auth::user()->profile;

        if (!$profile) {
            // プロフィール未登録ならエラーにして戻す
            return back()->with('error', 'プロフィールに住所が登録されていません。');
        }

        // 5) 購入時点の住所を1つの文字列にまとめる
        $shippingAddress = implode("\n", array_filter([
            '〒 ' . $profile->postal_code,
            $profile->address,
            $profile->building,   // null のときは自動でスキップされる
        ]));

        // 6) 購入レコード作成
        Purchase::create([
            'user_id'         => auth()->id(),
            'item_id'         => $item->id,
            'payment_method'  => (int) $validated['payment_method'],
            'shipping_address'=> $shippingAddress,
        ]);

        // 7) 必要ならステータス更新（今はコメントアウトのままでOK）
        // $item->update(['status' => 1]);

        return redirect()->route('items.index')->with('success', '購入が完了しました。');
    }
}
