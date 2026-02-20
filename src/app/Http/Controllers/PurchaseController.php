<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

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

    public function store(PurchaseRequest $request, Item $item)
    {
        $user = Auth::user();

        // 1) 自分の出品は購入NG
        if ($item->seller_id === $user->id) {
            return back()->with('error', '自分の商品は購入できません。');
        }

        // 2) 既に購入済みならNG
        if ($item->purchase()->exists() || (int)($item->status ?? 0) === 1) {
            return back()->with('error', 'この商品は売り切れです。');
        }

        // 3) 支払い方法のバリデーション
        $validated = $request->validated();

        // 4) プロフィールから住所情報を取得
        $profile = $user->profile;

        if (!$profile) {
            // プロフィール未登録ならエラーにして戻す
            return back()->with('error', 'プロフィールに住所が登録されていません。');
        }

        // 5) 購入時点の住所を1つの文字列にまとめる
        $shippingAddress = implode("\n", array_filter([
            '〒 ' . $profile->postal_code,
            $profile->address,
            $profile->building,
        ]));

        // 6)支払い方法 = 1（コンビニ払い）のときは Stripe に飛ばさず、一覧へ戻す
        if ((int)$validated['payment_method'] === 1) {

            // ★ここで購入確定（コンビニ払いは即完了扱いのまま）
            Purchase::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'payment_method' => (int) $validated['payment_method'],
                'shipping_address' => $shippingAddress,
            ]);

            return redirect()
                ->route('items.index')
                ->with('status', 'コンビニ払いでの購入が完了しました。');
        }

        session([
            "purchase_pending.{$item->id}" => [
                'payment_method'   => (int) $validated['payment_method'], // 2
                'shipping_address' => $shippingAddress,
            ],
        ]);

        // それ以外（ここでは 2 = カード払い）は Stripe に飛ばす
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $item->name,
                    ],
                    // 金額は「円 × 100」で指定（例：1000円 → 100000）
                    'unit_amount' => $item->price * 100,
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('purchase.success', $item) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('purchase.index', $item) . '?canceled=1',
        ]);

        // 7) Stripe の決済画面へリダイレクト
        return redirect()->away($session->url);
    }

    public function success(Item $item)
    {
        $user = Auth::user();

        // Stripeへ飛ばす直前に一時保存した購入情報を取得
        $pending = session("purchase_pending.{$item->id}");

        if (!$pending) {
            return redirect()->route('items.index')
                ->with('error', '購入情報が見つかりません。');
        }

        // 二重購入防止
        if ($item->purchase()->exists() || (int)($item->status ?? 0) === 1) {
            session()->forget("purchase_pending.{$item->id}");
            return redirect()->route('items.index')
                ->with('error', 'この商品は売り切れです。');
        }

        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => (int) $pending['payment_method'], // 2（カード）
            'shipping_address' => $pending['shipping_address'],
        ]);

        // 後片付け（同じ情報で再実行されないように）
        session()->forget("purchase_pending.{$item->id}");

        return redirect()->route('items.index')
            ->with('status', 'カード決済が完了しました。');
    }

}
