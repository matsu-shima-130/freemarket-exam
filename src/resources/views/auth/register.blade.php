@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <h2 class="auth-title">会員登録</h2>

    <form class="auth-form" method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <div class="form-group">
            <label for="name">ユーザー名</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" required>
            @error('password')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">確認用パスワード</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>

    <button type="submit" class="btn-submit">登録する</button>
</form>

    <p class="auth-link">
        {{-- いまは route('login') が未定義なので直URLでOK。後でFortify導入後に差し替え --}}
        <a href="/login">ログインはこちら</a>
    </p>
</div>
@endsection
