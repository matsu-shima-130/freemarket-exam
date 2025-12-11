@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <h2 class="auth-title">ログイン</h2>

    @if ($errors->has('login'))
        <div class="form-error form-error--global">
            {{ $errors->first('login') }}
        </div>
    @endif

    <form class="auth-form" method="POST" action="{{ route('login.attempt') }}" novalidate>
        @csrf
        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
            class="{{ $errors->has('email') ? 'is-invalid' : '' }}" required>
            @error('email')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password"
            class="{{ $errors->has('password') ? 'is-invalid' : '' }}" required>
            @error('password')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-submit">ログインする</button>
    </form>

    <p class="auth-link">
        <a href="{{ route('register') }}">会員登録はこちら</a>
    </p>
</div>
@endsection
