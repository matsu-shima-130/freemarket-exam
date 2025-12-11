@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
    <div class="verify-container">
        {{-- 上のメッセージ部分 --}}
        <p class="verify-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        {{-- 真ん中のグレーのボタン --}}
        <div class="verify-main-action">
            <a href="https://mailtrap.io"
                target="_blank"
                rel="noopener noreferrer"
                class="verify-button">
                    認証はこちらから
            </a>
        </div>

        {{-- 下の「認証メールを再送する」リンク風ボタン --}}
        <form method="POST" action="{{ route('verification.send') }}" class="verify-resend-form">
            @csrf
            <button type="submit" class="verify-resend-link">
                認証メールを再送する
            </button>
        </form>
    </div>
@endsection
