<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function edit(Item $item)
    {
        $user    = Auth::user();
        $profile = $user?->profile;   // あればプロフィール、なければ null

        return view('purchases.address', [
            'item'    => $item,
            'profile' => $profile,
        ]);
    }

    /* 住所更新処理 */
    public function update(Request $request, Item $item)
    {
        // バリデーション
        $validated = $request->validate([
            'postal_code' => ['required', 'string', 'max:10'],
            'address'     => ['required', 'string', 'max:255'],
            'building'    => ['nullable', 'string', 'max:255'],
        ]);

        $user    = Auth::user();
        $profile = $user->profile;

        // user_profiles に保存（あれば更新、なければ作成）
        if ($profile) {
            $profile->update($validated);
        } else {
            $user->profile()->create($validated);
        }

        // 更新後は「購入画面」に戻す
        return redirect()
            ->route('purchase.index', $item)
            ->with('success', '住所を更新しました。');
    }
}
