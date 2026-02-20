<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTradeMessageRequest;
use App\Models\Message;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TradeMessageController extends Controller
{
    public function store(StoreTradeMessageRequest $request, Purchase $purchase)
    {
        $currentUserId = Auth::id();

        $purchase->loadMissing(['item.seller', 'user']);

        // 権限（売り手 or 買い手のみ）
        $isBuyer = ($purchase->user_id === $currentUserId);
        $isSeller = ($purchase->item->seller_id === $currentUserId);

        if (!$isBuyer && !$isSeller) {
            abort(403);
        }

        $receiverId = $isBuyer ? $purchase->item->seller_id : $purchase->user_id;

        $storedImagePath = null;
        if ($request->hasFile('image')) {
            $storedImagePath = $request->file('image')->store('messages', 'public');
        }

        Message::create([
            'purchase_id' => $purchase->id,
            'sender_id' => $currentUserId,
            'receiver_id' => $receiverId,
            'body' => $request->input('body'),
            'image_path' => $storedImagePath,
        ]);

        return redirect()
            ->route('trades.show', $purchase)
            ->with('success', 'メッセージを送信しました');
    }

    public function update(StoreTradeMessageRequest $request, Purchase $purchase, Message $message)
{
    $currentUserId = Auth::id();

    // この取引のメッセージかチェック
    if ($message->purchase_id !== $purchase->id) {
        abort(404);
    }

    // 自分のメッセージだけ編集OK
    if ($message->sender_id !== $currentUserId) {
        abort(403);
    }

    $purchase->loadMissing(['item.seller', 'user']);

    // 取引の当事者以外は弾く
    $isBuyer = ($purchase->user_id === $currentUserId);
    $isSeller = ($purchase->item->seller_id === $currentUserId);
    if (!$isBuyer && !$isSeller) {
        abort(403);
    }

    $newImagePath = $message->image_path;

    if ($request->hasFile('image')) {
        // 既存画像があれば削除して差し替え
        if ($message->image_path) {
            Storage::disk('public')->delete($message->image_path);
        }
        $newImagePath = $request->file('image')->store('messages', 'public');
    }

    $message->update([
        'body' => $request->input('body'),
        'image_path' => $newImagePath,
    ]);

    return redirect()
        ->route('trades.show', $purchase)
        ->with('success', 'メッセージを編集しました');
    }

    public function destroy(Purchase $purchase, Message $message)
    {
        $currentUserId = Auth::id();

        if ($message->purchase_id !== $purchase->id) {
            abort(404);
        }

        if ($message->sender_id !== $currentUserId) {
            abort(403);
        }

        // 画像があれば削除
        if ($message->image_path) {
            Storage::disk('public')->delete($message->image_path);
        }

        $message->delete();

        return redirect()
            ->route('trades.show', $purchase)
            ->with('success', 'メッセージを削除しました');
    }
}
