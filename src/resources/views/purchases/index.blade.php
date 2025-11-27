@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/purchases-index.css') }}">
@endsection

@section('content')
<div class="purchase">
    <div class="purchase__inner">

        {{-- 左カラム：商品情報 + 支払い方法 + 配送先 --}}
        <div class="purchase__main">
            {{-- 商品情報ヘッダー --}}
            <header class="purchase-header">
                <div class="purchase-header__thumb">
                    @if ($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                    @else
                        <span class="purchase-header__thumbPh">商品画像</span>
                    @endif
                </div>
                <div class="purchase-header__info">
                    <p class="purchase-header__name">{{ $item->name }}</p>
                    <p class="purchase-header__price">￥{{ number_format($item->price) }}</p>
                </div>
            </header>

            {{-- 支払い方法 --}}
            <section class="purchase-section">
                <h2 class="purchase-section__title">支払い方法</h2>
                <div class="purchase-section__body">
                    <select name="payment_method"
                            id="payment-method-select"
                            class="purchase-select"
                            form="purchase-form">
                        <option value="">選択してください</option>
                        <option value="1">コンビニ払い</option>
                        <option value="2">カード払い</option>
                    </select>
                </div>
            </section>

            {{-- 配送先 --}}
            <section class="purchase-section">
                <div class="purchase-section__head">
                    <h2 class="purchase-section__title">配送先</h2>
                    <a href="{{ route('purchase.address.edit', $item) }}" class="purchase-section__link">
                        変更する
                    </a>
                </div>
                <div class="purchase-section__body">
                    <p class="purchase-address">
                        〒 {{ $profile?->postal_code ?? 'XXX-YYYY' }}<br>
                        {{ $profile?->address ?? '住所が未登録です' }}<br>
                        @if (!empty($profile?->building))
                            {{ $profile->building }}<br>
                        @endif
                    </p>
                </div>
            </section>
        </div>

        {{-- 右カラム：金額 + 購入ボタン --}}
        <aside class="purchase__side">
            <div class="purchase-summary">
                <dl class="purchase-summary__row">
                    <dt>商品代金</dt>
                    <dd>￥{{ number_format($item->price) }}</dd>
                </dl>
                <dl class="purchase-summary__row">
                    <dt>支払い方法</dt>
                    {{-- 選択内容をJSでここに反映させる --}}
                    <dd id="summary-payment-label">未選択</dd>
                </dl>
            </div>

            {{-- 購入ボタン --}}
            <form id="purchase-form"
                    action="{{ route('purchase.store', $item) }}"
                    method="post"
                    class="purchase-form">
                @csrf
                <button type="submit" class="purchase-btn">
                    購入する
                </button>
            </form>
        </aside>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('payment-method-select');
        const label  = document.getElementById('summary-payment-label');

        if (!select || !label) return;

        const labels = {
            '1': 'コンビニ払い',
            '2': 'カード払い',
            '': '未選択',
        };

        function updateLabel() {
            const value = select.value;
            label.textContent = labels[value] || '未選択';
        }

        // 初期表示
        updateLabel();

        // 変更されたときに反映
        select.addEventListener('change', updateLabel);
    });
</script>

@endsection
