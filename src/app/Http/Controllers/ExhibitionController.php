<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExhibitionRequest;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class ExhibitionController extends Controller
{
    public function create()
    {
        $categories = Category::all();

        return view('items.sell', [
            'categories' => $categories,
        ]);
    }

    /** 出品登録 */
    public function store(ExhibitionRequest $request)
    {
        // バリデーション済みの値を取得
        $validated = $request->validated();

        // カテゴリIDだけ先に取り出しておく（itemsテーブルにはカラムがないので）
        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);

        // 画像アップロード
        if (isset($validated['image'])) {
            // storage/app/public/items に保存
            $path = $validated['image']->store('items', 'public');
            $validated['image_path'] = $path;
            unset($validated['image']);
        }

        // ログインユーザーIDをセット
        $validated['seller_id'] = Auth::id();

        // Itemを保存
        $item = Item::create($validated);

        // カテゴリーを中間テーブルに紐づけ
        if (!empty($categoryIds)) {
        $item->categories()->sync($categoryIds);
        }

        return redirect()
            ->route('items.index')
            ->with('status', '商品を出品しました');
    }
}
