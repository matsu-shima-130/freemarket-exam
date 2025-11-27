@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/items-show.css') }}">
@endsection

@section('content')
    <div class="item-show">
        <div class="item-show__inner">

            {{-- 左：商品画像 --}}
            <div class="item-show__media">
                <div class="item-show__thumb">
                    @if ($item->image_path)
                        <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}">
                    @else
                        <span class="item-show__ph">商品画像</span>
                    @endif
                </div>
            </div>

            {{-- 右：情報ブロック --}}
            <div class="item-show__body">

                <h1 class="item-show__title">{{ $item->name }}</h1>
                    @if(!empty($item->brand_name))
                        <div class="item-show__brand">{{ $item->brand_name }}</div>
                    @endif

                <div class="item-show__price">
                    ￥{{ number_format($item->price) }}<span class="item-show__tax"> (税込)</span>
                </div>

                {{-- いいね/コメント --}}
                <div class="item-show__meta">
                    <div class="item-show__icon item-show__icon--like">

                        {{-- いいねボタン（ログイン時のみ） --}}
                        <form action="{{ route('likes.toggle', $item) }}" method="post" class="like-form">
                            @csrf
                            <button
                                type="submit"
                                class="like-btn"
                                aria-label="いいね"
                                aria-pressed="{{ $liked ? 'true' : 'false' }}"
                            >
                                {{-- いいね状態でアイコンの見た目を切り替え --}}
                                <i
                                    class="{{ $liked ? 'fa-solid' : 'fa-regular' }} fa-heart like-icon"
                                    aria-hidden="true"
                                ></i>
                                <span class="sr-only">
                                    {{ $liked ? 'いいね済み' : '未いいね' }}
                                </span>
                            </button>
                        </form>
                        <span class="like-count">{{ $item->likes_count }}</span>
                    </div>

                    <div class="item-show__icon item-show__icon--comment">
                        <i class="fa-regular fa-comment comment-icon" aria-hidden="true"></i>
                        <span class="comment-count">{{ $item->comments_count }}</span>
                    </div>
                </div>

                {{-- 購入ボタン（売り切れなら無効） --}}
                <div class="item-show__buy">
                    @if($item->is_sold ?? false)
                        <button class="btn btn--disabled" disabled>Sold</button>
                    @else
                        <a href="{{ route('purchase.index', $item) }}" class="btn btn--primary btn--wide">
                            購入手続きへ
                        </a>
                    @endif
                </div>

                <section class="item-show__section">
                    <h2 class="item-show__sectionTitle">商品説明</h2>
                    <div class="item-show__desc">
                        {!! nl2br(e($item->description)) !!}
                    </div>
                </section>

                <section class="item-show__section">
                    <h2 class="item-show__sectionTitle">商品の情報</h2>
                    <div class="item-show__infoRow">
                        <div class="item-show__infoKey">カテゴリー</div>
                        <div class="item-show__infoVal">
                            @foreach ($item->categories as $category)
                                <span class="chip">{{ $category->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="item-show__infoRow">
                        <div class="item-show__infoKey">商品の状態</div>
                        <div class="item-show__infoVal">{{ $item->condition_label ?? '—' }}</div>
                    </div>
                </section>

                <section class="item-show__section">
                    <h2 class="item-show__sectionTitle">コメント({{ $item->comments_count }})</h2>

                    @forelse ($item->comments as $comment)
                        @php
                            // プロフィールのパスを取得
                            $avatarPath = optional(optional($comment->user)->profile)->avatar_path;

                            // 文字列なら前後の空白をカット
                            if (is_string($avatarPath)) {
                                $avatarPath = trim($avatarPath);
                            }

                            // パスがあって、かつ storage/app/publicに実ファイルがあるかどうか
                            $avatarExists = $avatarPath &&
                                file_exists(storage_path('app/public/' . $avatarPath));
                        @endphp

                        <article class="comment">
                            <div class="comment__avatar">
                                @if ($avatarExists)
                                    <img
                                        src="{{ asset('storage/' . $avatarPath) }}"
                                        alt="{{ $comment->user->name }}のアイコン"
                                    >
                                @endif
                                    {{-- 画像がない場合は <img> を出さない → CSS のグレー丸だけになる --}}
                            </div>

                            <div class="comment__body">
                                <p class="comment__name">
                                    {{ $comment->user->name }}
                                </p>

                                <div class="comment__box">{{ $comment->body }}</div>

                                @can('delete', $comment)
                                    <form action="{{ route('comments.destroy', $comment) }}" method="post" class="comment__actions">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="comment__delete">削除</button>
                                    </form>
                                @endcan
                            </div>
                        </article>
                    @empty
                        <p class="comment__empty">まだコメントはありません。</p>
                    @endforelse
                </section>

                <section class="item-show__section">
                    <h2 class="item-show__sectionTitle">商品へのコメント</h2>

                    <form action="{{ route('comments.store', $item) }}" method="post">
                        @csrf

                        <textarea
                            name="body"
                            class="commentForm__textarea"
                            rows="5"
                            placeholder="コメントを入力…"
                        >{{ old('body') }}</textarea>

                        @error('body')
                            <p class="form-error">{{ $message }}</p>
                        @enderror

                        <div class="commentForm__actions">
                            <button class="btn btn--primary btn--wide" type="submit">
                                コメントを送信する
                            </button>
                        </div>
                    </form>
                </section>

            </div>
        </div>
    </div>
@endsection
