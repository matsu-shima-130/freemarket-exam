@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage-profile_edit.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <h2 class="auth-title">プロフィール設定</h2>

    <form class="auth-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        {{-- 画像URL + 更新時刻（キャッシュ回避用）を作る --}}
        @php
            $path = $profile->avatar_path
                ? asset('storage/'.$profile->avatar_path)
                : asset('images/avatar-placeholder.png');

            // updated_at があるときだけ ?v=… を付ける（ないときは付けない）
            $ver  = $profile->updated_at ? ('?v='.$profile->updated_at->timestamp) : '';
        @endphp

        <div class="avatar-row">
            <img
                class="avatar-preview"
                src="{{ $avatarUrl . $ver }}"
                alt="プロフィール画像"
            >
            <input id="avatar" type="file" name="avatar" accept="image/*" class="visually-hidden">
            <label for="avatar" class="btn-avatar">画像を選択する</label>
        </div>
            @error('avatar')
                <div class="form-error">{{ $message }}</div>
            @enderror

        <div class="form-group">
            <label>ユーザー名</label>
            <input type="text" name="name" value="{{ old('name', $userNameForForm ?? '') }}" placeholder="" autocomplete="off" >
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>郵便番号</label>
            <input type="text" name="postal_code" value="{{ old('postal_code', $profile->postal_code) }}">
            @error('postal_code') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>住所</label>
            <input type="text" name="address" value="{{ old('address', $profile->address) }}">
            @error('address') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>建物名</label>
            <input type="text" name="building" value="{{ old('building', $profile->building) }}">
            @error('building') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn-submit">更新する</button>
    </form>
</div>
@endsection
