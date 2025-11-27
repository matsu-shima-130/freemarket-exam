@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/items-sell.css') }}">
@endsection

@section('content')
<div class="sell">
    <div class="sell__inner">

        <h1 class="sell__title">商品の出品</h1>

        <form class="sell-form" method="POST" action="{{ route('sell.store') }}" enctype="multipart/form-data">
        @csrf

            {{-- 商品画像 --}}
            <section class="sell-section">
                <div class="sell-row">
                    <h2 class="sell-row__label">商品画像</h2>
                    <div class="sell-image">
                        <div class="sell-row__body">
                            <label class="sell-image__drop {{ $errors->has('image') ? 'is-invalid' : '' }}">
                                <input type="file" name="image" class="sell-image__input">
                                <span class="sell-image__placeholder">
                                    画像を選択する
                                </span>
                            </label>
                            @error('image')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- 商品の詳細 --}}
            <section class="sell-section">
                <h2 class="sell-section__heading">商品の詳細</h2>

                {{-- カテゴリー --}}
                <div class="sell-row">
                    <div class="sell-row__label">カテゴリー</div>
                    <div class="sell-row__body">
                            <div class="sell-category">
                                @foreach ($categories as $category)
                                    @php
                                        $checked = in_array($category->id, old('category_ids', []));
                                    @endphp

                                    <label class="chip">
                                        <input
                                            type="checkbox"
                                            name="category_ids[]"
                                            value="{{ $category->id }}"
                                            class="chip__checkbox"
                                            {{ $checked ? 'checked' : '' }}
                                        >
                                        <span class="chip__label">{{ $category->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                </div>

                {{-- 商品の状態 --}}
                <div class="sell-row">
                    <label for="condition" class="sell-row__label">商品の状態</label>
                    <div class="sell-row__body sell-row__body--select">
                        <select
                            name="condition"
                            id="condition"
                            class="sell-select {{ $errors->has('condition') ? 'is-invalid' : '' }}"
                        >
                            <option value="">選択してください</option>
                            <option value="1" {{ old('condition') == 1 ? 'selected' : '' }}>良好</option>
                            <option value="2" {{ old('condition') == 2 ? 'selected' : '' }}>目立った傷や汚れなし</option>
                            <option value="3" {{ old('condition') == 3 ? 'selected' : '' }}>やや傷や汚れあり</option>
                            <option value="4" {{ old('condition') == 4 ? 'selected' : '' }}>状態が悪い</option>
                        </select>
                    </div>
                </div>
            </section>

            {{-- 商品名と説明 --}}
            <section class="sell-section">
                <h2 class="sell-section__heading">商品名と説明</h2>

                {{-- 商品名 --}}
                <div class="sell-row">
                    <label for="name" class="sell-row__label">商品名</label>
                    <div class="sell-row__body">
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="sell-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                            value="{{ old('name') }}"
                        >
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ブランド名 --}}
                <div class="sell-row">
                    <label for="brand_name" class="sell-row__label">ブランド名</label>
                    <div class="sell-row__body">
                        <input
                            type="text"
                            id="brand_name"
                            name="brand_name"
                            class="sell-input"
                            value="{{ old('brand_name') }}"
                        >
                    </div>
                </div>

                {{-- 商品の説明 --}}
                <div class="sell-row">
                    <label for="description" class="sell-row__label">商品の説明</label>
                    <div class="sell-row__body">
                        <textarea
                            id="description"
                            name="description"
                            class="sell-textarea {{ $errors->has('description') ? 'is-invalid' : '' }}"
                            rows="5"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- 販売価格 --}}
                <div class="sell-row">
                    <label for="price" class="sell-row__label">販売価格</label>
                    <div class="sell-row__body">
                        <div class="sell-price">
                        <span class="sell-price__prefix">￥</span>
                        <input
                            type="text"
                            id="price"
                            name="price"
                            class="sell-input sell-input--price {{ $errors->has('price') ? 'is-invalid' : '' }}"
                            value="{{ old('price') }}"
                        >
                    </div>
                    @error('price')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </section>

            {{-- 送信ボタン --}}
            <div class="sell-submit">
                <button type="submit" class="btn btn--primary btn--wide">
                    出品する
                </button>
            </div>
        </form>

    </div>
</div>

{{-- 画像プレビュー用のJS --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.querySelector('.sell-image__input');
        const dropArea  = document.querySelector('.sell-image__drop');
        const placeholder = document.querySelector('.sell-image__placeholder');

        if (!fileInput || !dropArea) return;

        fileInput.addEventListener('change', function (e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            // 画像じゃなかったら何もしない
            if (!file.type.startsWith('image/')) {
                dropArea.style.backgroundImage = '';
                dropArea.classList.remove('sell-image__drop--has-image');
                if (placeholder) {
                    placeholder.textContent = '画像を選択する';
                }
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                dropArea.style.backgroundImage = `url(${event.target.result})`;
                dropArea.classList.add('sell-image__drop--has-image');
                if (placeholder) {
                    placeholder.textContent = '画像を変更する';
                }
            };
            reader.readAsDataURL(file);
        });
    });
</script>
@endsection
