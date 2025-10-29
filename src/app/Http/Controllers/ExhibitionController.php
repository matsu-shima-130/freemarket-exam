<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExhibitionController extends Controller
{
    public function create()
    {
        return view('items.sell');
    }

    /** 出品登録 */
    public function store(Request $request)
    {
        return back()->with('status', '（ダミー）出品処理は後で実装します');
    }
}
