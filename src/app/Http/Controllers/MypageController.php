<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;

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
                ->whereHas('purchase', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
            ->with('purchase')
            ->orderByDesc('id')
            ->get();

        } else {
            // 想定外の値が来たとき用の保険
            $items = collect();
        }

        return view('mypage.index', [
            'tab'       => $tab,
            'userName'  => $userName,
            'avatarUrl' => $avatarUrl,
            'items'     => $items,
        ]);
    }
}