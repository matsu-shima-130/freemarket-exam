@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/trades-show.css') }}">
@endsection

@section('content')
    <div class="trade-layout">

        {{-- 左：その他の取引 --}}
        <aside class="trade-side">
            <div class="trade-side__title">その他の取引</div>

            <div class="trade-side__list">
                @foreach ($otherTrades as $trade)
                    @php
                        $unreadCount = (int) ($unreadCountsByPurchaseId[$trade->id] ?? 0);
                    @endphp

                    <a class="trade-side__item" href="{{ route('trades.show', $trade) }}">
                        {{ $trade->item->name }}

                        @if ($unreadCount > 0)
                            <span class="trade-side__badge">{{ $unreadCount }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </aside>

        {{-- 右：メイン --}}
        <main class="trade-main">

            {{-- 上：相手名 + 完了ボタン --}}
            <header class="trade-main__header">
                <div class="trade-main__partner">
                    <span class="trade-main__partnerIcon"></span>
                    「{{ $isSellerView ? $buyer->name : $seller->name }}」さんとの取引画面
                </div>

                {{-- ボタンは購入者/出品者で表示/文言を変えられる --}}
                @if ($canBuyerRate)
                    <button class="trade-main__finishBtn" type="button" id="openRatingModalBtn">
                        取引を完了する
                    </button>
                @endif
            </header>

            {{-- 商品情報 --}}
            <section class="trade-product">
                <div class="trade-product__thumb">
                    @if ($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                    @else
                        <div class="trade-product__thumbPh">商品画像</div>
                    @endif
                </div>

                <div class="trade-product__info">
                    <div class="trade-product__name">{{ $item->name }}</div>
                    <div class="trade-product__price">¥{{ number_format($item->price) }}</div>
                </div>
            </section>

            {{-- メッセージ一覧 --}}
            <section class="trade-messages">
                @foreach ($purchase->messages as $message)
                    @php
                        $isMyMessage = ($message->sender_id === auth()->id());
                    @endphp

                    <div class="trade-message {{ $isMyMessage ? 'trade-message--right' : 'trade-message--left' }}">
                        <div class="trade-message__user">
                            {{ $message->sender->name }}
                        </div>

                        <div class="trade-message__bubble">
                            @if ($message->body)
                                <p class="trade-message__text">{{ $message->body }}</p>
                            @endif

                            @if ($message->image_path)
                                <img class="trade-message__image" src="{{ asset('storage/' . $message->image_path) }}" alt="添付画像">
                            @endif
                        </div>

                        @if ($isMyMessage)
                            <div class="trade-message__actions">
                                <a href="{{ route('trades.show', $purchase) }}?edit_message_id={{ $message->id }}">編集</a>

                                <form method="POST" action="{{ route('trades.messages.destroy', [$purchase, $message]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="trade-message__actionBtn">削除</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </section>


            {{-- 下：入力欄 --}}
            {{-- エラー表示（FN008） --}}
            @if ($errors->any())
                <div class="trade-form__errors">
                    <ul>
                        @foreach ($errors->all() as $errorMessage)
                            <li>{{ $errorMessage }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <footer class="trade-form">
                @if (!empty($editingMessage))
                    <div class="trade-form__editingNotice">
                        メッセージを編集中です
                    </div>
                @endif
                <form class="trade-form__row"
                    method="POST"
                    action="{{ !empty($editingMessage)
                        ? route('trades.messages.update', [$purchase, $editingMessage])
                        : route('trades.messages.store', $purchase) }}"
                    enctype="multipart/form-data"
                    autocomplete="off">

                    @csrf
                    @if (!empty($editingMessage))
                        @method('PATCH')
                    @endif

                    <input
                        class="trade-form__input"
                        type="text"
                        name="body"
                        id="tradeMessageBody"
                        value="{{ old('body', !empty($editingMessage) ? $editingMessage->body : '') }}"
                        placeholder="取引メッセージを記入してください"
                        autocomplete="off"
                    >

                    <input
                        class="trade-form__file"
                        type="file"
                        name="image"
                        accept="image/png,image/jpeg"
                        id="tradeMessageImage"
                    >

                    <button class="trade-form__imageBtn" type="button"
                        onclick="document.getElementById('tradeMessageImage').click()">
                            画像を追加
                    </button>

                    @if (!empty($editingMessage))
                        <a class="trade-form__cancelEdit" href="{{ route('trades.show', $purchase) }}">キャンセル</a>
                    @endif

                    <button class="trade-form__sendBtn" type="submit" aria-label="送信">
                        <img src="{{ asset('images/send.png') }}" alt="送信">
                    </button>
                </form>
            </footer>

            @if ($canBuyerRate || $canSellerRate)
                <div class="trade-ratingModal" id="tradeRatingModal" aria-hidden="true">
                    <div class="trade-ratingModal__backdrop" id="tradeRatingModalBackdrop"></div>

                    <div class="trade-ratingModal__panel" role="dialog" aria-modal="true">
                        <div class="trade-ratingModal__title">取引が完了しました。</div>
                        <div class="trade-ratingModal__sub">今回の取引相手はどうでしたか？</div>

                        <form method="POST" action="{{ route('trades.ratings.store', $purchase) }}">
                            @csrf

                            <div class="trade-ratingModal__stars">
                                <input type="radio" id="rate5" name="score" value="5">
                                <label for="rate5" class="trade-ratingModal__star">★</label>

                                <input type="radio" id="rate4" name="score" value="4">
                                <label for="rate4" class="trade-ratingModal__star">★</label>

                                <input type="radio" id="rate3" name="score" value="3">
                                <label for="rate3" class="trade-ratingModal__star">★</label>

                                <input type="radio" id="rate2" name="score" value="2">
                                <label for="rate2" class="trade-ratingModal__star">★</label>

                                <input type="radio" id="rate1" name="score" value="1">
                                <label for="rate1" class="trade-ratingModal__star">★</label>
                            </div>

                            <button type="submit" class="trade-ratingModal__submitBtn">
                                送信する
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        </main>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('tradeMessageBody');
            if (!input) return;

            const isEditingMessage = @json(!empty($editingMessage));
            if (isEditingMessage) return;

            // Blade から確実に埋め込む（これが最強）
            const purchaseId = @json($purchase->id);
            const userId = @json(auth()->id());
            const key = `trade_message_draft_user_${userId}_purchase_${purchaseId}`;

            const hasOldBody = @json(old('body') !== null);

            function applyDraftForce() {
                if (hasOldBody) return;

                const saved = sessionStorage.getItem(key);
                // 何もなければ必ず空にする
                input.value = saved ?? '';
            }

            // ブラウザの復元より強くする（少し長めに何回か）
            [0, 50, 150, 300, 600, 1000].forEach((ms) => {
                setTimeout(applyDraftForce, ms);
            });

            window.addEventListener('pageshow', function () {
                [0, 50, 150, 300, 600, 1000].forEach((ms) => {
                    setTimeout(applyDraftForce, ms);
                });
            });

            // 入力するたび保存（purchaseごと）
            input.addEventListener('input', function () {
                sessionStorage.setItem(key, input.value);
            });

            // 送信成功後は削除（sessionStorage版）
            @if (session('success'))
                sessionStorage.removeItem(key);
            @endif
        });

        @if ($canBuyerRate || $canSellerRate)
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('tradeRatingModal');
                const openButton = document.getElementById('openRatingModalBtn');
                const backdrop = document.getElementById('tradeRatingModalBackdrop');

                function openModal() {
                    if (!modal) return;
                    modal.setAttribute('aria-hidden', 'false');
                    modal.classList.add('is-open');
                }

                function closeModal() {
                    if (!modal) return;
                    modal.setAttribute('aria-hidden', 'true');
                    modal.classList.remove('is-open');
                }

                if (openButton) {
                    openButton.addEventListener('click', openModal);
                }

                if (backdrop) {
                    backdrop.addEventListener('click', closeModal);
                }

                const shouldAutoOpen = @json($shouldAutoOpenRatingModal ?? false);
                if (shouldAutoOpen) {
                    openModal();
                }
            });
        @endif

    </script>
@endpush


