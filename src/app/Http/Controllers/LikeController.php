<?php

namespace App\Http\Controllers;

use App\Models\Item;

class LikeController extends Controller
{
    // POST /items/{item}/like  （付いていれば外す／無ければ付ける）
    public function toggle(Item $item)
    {
        $user = auth()->user();

        //そのユーザーがすでにこの商品にいいねしているかをチェック
        $already = $user->likes()->where('item_id', $item->id)->exists();

        if ($already) {
            $user->likes()->where('item_id', $item->id)->delete();
            return back()->with('success', 'いいねを外しました。');
        } else {
            $user->likes()->create(['item_id' => $item->id]);
            return back()->with('success', 'いいねしました。');
        }
    }
}
