<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use App\Models\TradeRating;


class TradeController extends Controller
{
    public function show(Purchase $purchase)
    {
        $currentUserId = Auth::id();

        $purchase->loadMissing([
            'item.seller',
            'user',
            'messages' => function ($messageQuery) {
                $messageQuery->with('sender')->orderBy('created_at');
            },
        ]);


        // 売り手 or 買い手 以外は見れない
        if ($purchase->user_id !== $currentUserId && $purchase->item->seller_id !== $currentUserId) {
            abort(403);
        }

        $item = $purchase->item;
        $buyer = $purchase->user;
        $seller = $item->seller;

        $isSellerView = ($item->seller_id === $currentUserId);
        $isBuyerView  = ($purchase->user_id === $currentUserId);

        // 既読処理：この取引の「自分宛」の未読をまとめて既読にする
        Message::query()
            ->where('purchase_id', $purchase->id)
            ->where('receiver_id', $currentUserId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // 左サイドバー用：自分が関係する取引一覧（取引中＝purchaseがあるもの）
        // 取引の並び：最新メッセージが新しい順（FN004）
        $otherTrades = Purchase::query()
            ->where(function ($purchaseQuery) use ($currentUserId) {
                $purchaseQuery->where('user_id', $currentUserId)
                    ->orWhereHas('item', function ($itemQuery) use ($currentUserId) {
                        $itemQuery->where('seller_id', $currentUserId);
                    });
            })
            ->with(['item'])
            ->withMax('messages', 'created_at')
            ->orderByDesc('messages_max_created_at')
            ->get();

        // 未読件数（FN005）
        // purchase_id ごとに「自分宛未読」を集計して、[purchase_id => count] の形にする
        $unreadCountsByPurchaseId = Message::query()
            ->selectRaw('purchase_id, COUNT(*) as unread_count')
            ->where('receiver_id', $currentUserId)
            ->whereNull('read_at')
            ->groupBy('purchase_id')
            ->pluck('unread_count', 'purchase_id');

        $buyerId  = $purchase->user_id;
        $sellerId = $purchase->item->seller_id;

        $buyerHasRated = TradeRating::query()
            ->where('purchase_id', $purchase->id)
            ->where('rater_id', $buyerId)
            ->exists();

        $sellerHasRated = TradeRating::query()
            ->where('purchase_id', $purchase->id)
            ->where('rater_id', $sellerId)
            ->exists();

        // 購入者：未評価なら「ボタンで」開く
        $canBuyerRate = $isBuyerView && (! $buyerHasRated);

        // 出品者：購入者が先に評価済みで、自分が未評価なら「自動で」開く
        $canSellerRate = $isSellerView && $buyerHasRated && (! $sellerHasRated);

        $shouldAutoOpenRatingModal = $canSellerRate;

        $ratingTargetUserName = $isBuyerView ? $seller->name : $buyer->name;

        $editingMessageId = request()->query('edit_message_id');

        $editingMessage = null;
        if ($editingMessageId) {
            $editingMessage = Message::query()
                ->where('id', $editingMessageId)
                ->where('purchase_id', $purchase->id)
                ->where('sender_id', $currentUserId)
                ->first();
        }


        return view('trades.show', compact(
            'purchase',
            'item',
            'buyer',
            'seller',
            'isSellerView',
            'isBuyerView',
            'otherTrades',
            'unreadCountsByPurchaseId',
            'canBuyerRate',
            'canSellerRate',
            'shouldAutoOpenRatingModal',
            'ratingTargetUserName',
            'editingMessage'
        ));
    }
}
