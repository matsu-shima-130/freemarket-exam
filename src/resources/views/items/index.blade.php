@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/items-index.css') }}">
@endsection

@section('content')
    <div class="items">
        <div class="items__inner">
            {{-- タブ --}}
            <nav class="items__tabs items__tabs--bleed">
                <a href="{{ route('items.index', array_filter(['tab'=>'recommend','keyword'=>$keyword])) }}"
                    class="items__tab {{ $tab === 'recommend' ? 'is-active' : '' }}">
                    おすすめ
                </a>
                <a href="{{ route('items.index', array_filter(['tab'=>'mylist','keyword'=>$keyword])) }}"
                    class="items__tab {{ $tab === 'mylist' ? 'is-active' : '' }}">
                    マイリスト
                </a>
            </nav>

            {{-- 未ログインのマイリストは空表示 --}}
            @if ($tab === 'mylist' && !Auth::check())
                <div class="items__empty"></div>
            @else
                <div class="items__grid">
                    @forelse($items as $item)
                        <a href="{{ route('items.show', $item) }}" class="item">
                            <div class="item__thumb">
                                @if ($item->image_path)
                                    <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" loading="lazy">
                                @else
                                    <span class="item__thumb__ph">商品画像</span>
                                @endif

                                @if ($item->is_sold ?? false)
                                    <span class="item__sold">Sold</span>
                                @endif
                            </div>
                            <div class="item__name">{{ $item->name }}</div>
                        </a>
                        @empty
                    @endforelse
                </div>

            @endif
        </div>
    </div>
@endsection
