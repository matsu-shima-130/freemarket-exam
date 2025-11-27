@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/mypage-index.css') }}">
@endsection

@section('content')
    <div class="mypage">
        <div class="mypage__inner">

            {{-- ヘッダー（アイコン＋ユーザー名＋プロフィール編集ボタン） --}}
            <header class="mypage-header">
                <div class="mypage-header__left">
                    <div class="mypage-header__avatar">
                        @if (!empty($avatarUrl))
                            <img src="{{ $avatarUrl }}" alt="{{ $userName ?? 'ユーザー名' }}のプロフィール画像">
                        @else
                            {{-- 画像未設定時はグレー丸だけ表示 --}}
                            <span class="mypage-header__avatarPh"></span>
                        @endif
                    </div>
                    <div class="mypage-header__info">
                        <p class="mypage-header__name">
                            {{ $userName ?? 'ユーザー名' }}
                        </p>
                    </div>
                </div>

                <div class="mypage-header__right">
                    <a href="{{ route('profile.edit') }}" class="mypage-header__editBtn">
                        プロフィールを編集
                    </a>
                </div>
            </header>

            {{-- タブ --}}
            @php
                $currentTab = $tab ?? 'selling';
            @endphp

            <nav class="mypage-tabs mypage-tabs--bleed">
                <a href="{{ route('mypage.index', ['tab' => 'selling']) }}"
                    class="mypage-tab {{ $currentTab === 'selling' ? 'is-active' : '' }}">
                        出品した商品
                </a>
                <a href="{{ route('mypage.index', ['tab' => 'purchased']) }}"
                    class="mypage-tab {{ $currentTab === 'purchased' ? 'is-active' : '' }}">
                        購入した商品
                </a>
            </nav>


            {{-- 商品グリッド --}}
            <section class="mypage-items">
                <div class="mypage-grid">
                    @forelse ($items ?? [] as $item)
                        <a href="{{ route('items.show', $item) }}" class="mypage-item">
                            <div class="mypage-item__thumb">
                                @if ($item->image_path)
                                    <img src="{{ asset('storage/' . $item->image_path) }}"
                                        alt="{{ $item->name }}" loading="lazy">
                                @else
                                    <span class="mypage-item__thumbPh">商品画像</span>
                                @endif

                                @if ($item->is_sold ?? false)
                                    <span class="mypage-item__sold">Sold</span>
                                @endif
                            </div>
                            <div class="mypage-item__name">{{ $item->name }}</div>
                        </a>
                    @empty
                        <p class="mypage-items__empty">商品はまだありません。</p>
                    @endforelse
                </div>
            </section>

        </div>
    </div>
@endsection
