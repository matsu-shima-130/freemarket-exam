<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Message;
use App\Models\TradeRating;

class MypageController extends Controller
{
    public function index(Request $request)
    {
        // ログイン中のユーザー
        $user = Auth::user();

        $tab = $request->query('tab', 'selling');
        $userName = $user->name;

        // プロフィール画像のパス（なくてもエラーにならないように optional()）
        $avatarPath = optional($user->profile)->avatar_path;

        $avatarUrl = $avatarPath ? asset('storage/' . $avatarPath) : null;

        // 評価平均
        $averageRatingScore = TradeRating::query()
            ->where('ratee_id', $user->id)
            ->avg('score');

        $roundedAverageRating = is_null($averageRatingScore)
            ? null
            : (int) round($averageRatingScore);


        $totalUnreadCount = Message::query()
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // 初期化（trading 以外では空でOK）
        $unreadCountsByPurchaseId = collect();
        $items = collect();   // selling/purchased 用
        $trades = collect();  // trading 用

        // タブごとの設定
        if ($tab === 'selling') {

            // 出品した商品タブ → 自分が seller_id の商品だけ取得
            $items = Item::query()
                ->where('seller_id', $user->id)
                ->with('purchase')
                ->orderByDesc('id')
                ->get();

        } elseif ($tab === 'purchased') {

            $items = Item::query()
                ->whereHas('purchase', function ($purchaseQuery) use ($user) {
                    $purchaseQuery->where('user_id', $user->id);
                })
                ->with('purchase')
                ->orderByDesc('id')
                ->get();

        } elseif ($tab === 'trading') {

            // 取引中 = 自分が購入者 or 自分が出品者（購入済みの取引）
            $trades = Purchase::query()
                ->where(function ($purchaseQuery) use ($user) {

                    // 自分が購入者の取引  or  自分が出品者の取引
                    $purchaseQuery->where('user_id', $user->id)
                        ->orWhereHas('item', function ($itemQuery) use ($user) {
                            $itemQuery->where('seller_id', $user->id);
                        });
                })
                ->with(['item']) // 一覧で商品情報を使う
                ->withMax('messages', 'created_at')
                ->orderByDesc('messages_max_created_at')
                ->get();

                $unreadCountsByPurchaseId = Message::query()
                    ->selectRaw('purchase_id, COUNT(*) as unread_count')
                    ->where('receiver_id', $user->id)
                    ->whereNull('read_at')
                    ->groupBy('purchase_id')
                    ->pluck('unread_count', 'purchase_id');

                $totalUnreadCount = (int) $unreadCountsByPurchaseId->sum();

        } else {
            // 想定外の値が来たとき用の保険
            $items = collect();
        }

        return view('mypage.index', [
            'tab'       => $tab,
            'userName'  => $userName,
            'avatarUrl' => $avatarUrl,
            'items'     => $items,
            'trades'    => $trades,
            'totalUnreadCount' => $totalUnreadCount,
            'unreadCountsByPurchaseId' => $unreadCountsByPurchaseId,
            'roundedAverageRating' => $roundedAverageRating,
        ]);
    }
}