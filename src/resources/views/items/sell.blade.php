@extends('layouts.app')

@section('css')
    <style>
        .sell-dummy {
            max-width: 640px;
            margin: 80px auto;
            text-align: center;
            line-height: 2;
        }
        .sell-dummy h1 { font-size: 28px; margin-bottom: 24px; }
        .sell-dummy p  { color:#555; }
    </style>
@endsection

@section('content')
<div class="sell-dummy">
    <h1>（ダミー）商品出品</h1>
    <p>ここに出品フォームを実装していく予定です。</p>

    <form method="POST" action="{{ route('sell.store') }}">
        @csrf
        <button class="btn-submit" type="submit" style="margin-top:24px;">（ダミー）この内容で出品</button>
    </form>
</div>
@endsection
