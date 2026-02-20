<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreTradeRatingRequest;
use App\Models\Purchase;
use App\Models\TradeRating;
use App\Mail\TradeCompletedMail;

class TradeRatingController extends Controller
{
    public function store(StoreTradeRatingRequest $request, Purchase $purchase)
    {
        $currentUserId = Auth::id();

        $purchase->loadMissing(['item.seller', 'user']);

        // 売り手 or 買い手 以外は評価できない
        if ($purchase->user_id !== $currentUserId && $purchase->item->seller_id !== $currentUserId) {
            abort(403);
        }

        $buyerId  = $purchase->user_id;
        $sellerId = $purchase->item->seller_id;

        $isBuyer  = ($currentUserId === $buyerId);
        $isSeller = ($currentUserId === $sellerId);

        // 既にこの取引を評価済みなら弾く（unique制約 + 念のための二重ガード）
        $alreadyRated = TradeRating::query()
            ->where('purchase_id', $purchase->id)
            ->where('rater_id', $currentUserId)
            ->exists();

        if ($alreadyRated) {
            return redirect()->route('items.index');
        }

        // FN013：出品者は「購入者が先に完了（評価）してから」評価できる
        if ($isSeller) {
            $buyerHasRated = TradeRating::query()
                ->where('purchase_id', $purchase->id)
                ->where('rater_id', $buyerId)
                ->exists();

            if (! $buyerHasRated) {
                abort(403);
            }
        }

        $rateeId = $isBuyer ? $sellerId : $buyerId;

        TradeRating::create([
            'purchase_id' => $purchase->id,
            'rater_id'    => $currentUserId,
            'ratee_id'    => $rateeId,
            'score'       => (int) $request->input('score'),
        ]);

        // US005/FN016：購入者が取引完了（評価）したら出品者へメール通知
        if ($isBuyer) {
            $sellerEmail = $purchase->item->seller->email;

            Mail::to($sellerEmail)->send(new TradeCompletedMail($purchase));
        }

        // FN014：評価送信後は商品一覧へ
        return redirect()->route('items.index');
    }
}
