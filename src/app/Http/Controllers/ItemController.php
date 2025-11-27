<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        //URLクエリから抜き取り
        $currentTab = $request->query('tab', 'recommend');
        $searchKeyword = $request->query('keyword');

        //ベースクエリ
        $baseQuery = Item::query()
        //自分が出品した商品は表示しない(出品者のIDとログインしてるIDが同じじゃない)
        ->when(Auth::check(), function ($queryBuilder){
            $queryBuilder->where('seller_id', '!=', Auth::id());
        })

        //商品名の部分一致検索
        //もし$searchKeywordに中身があればfunctionの条件を付与する
        ->when($searchKeyword,function ($queryBuilder,$searchKeyword){
            $queryBuilder->where('name','like',"%{$searchKeyword}%");
        })

        //いいね数を取得
        ->withCount('likes')
        //新着順
        ->orderByDesc('id');

        // タブ別の上乗せ条件
        if ($currentTab === 'mylist') {
            if (!Auth::check()) {
                // 未ログイン時のマイリストは空表示
                $itemsForView = collect();
            } else {
                $itemsForView = $baseQuery
                    ->whereHas('likes', function ($queryBuilder) {
                        $queryBuilder->where('user_id', Auth::id());
                    })
                    ->get();
            }
        } else {
            // 共通条件のまま
            $itemsForView = $baseQuery
                ->get();
        }

        return view('items.index', [
            'items'   => $itemsForView,
            'tab'     => $currentTab,
            'keyword' => $searchKeyword,
        ]);
    }

    public function show(Item $item)
    {
        // コメント+ユーザーの同時ロード、件数の付与
        $item->load(['comments.user','categories'])->loadCount(['comments', 'likes']);

        $liked = auth()->check()
            ? auth()->user()->likes()->where('item_id', $item->id)->exists()
            : false;

        return view('items.show', [
            'item'  => $item,
            'liked' => $liked,
        ]);
    }
}
