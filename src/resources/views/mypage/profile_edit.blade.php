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

        <div class="avatar-row">
            <img
                class="avatar-preview"
                src="{{ $avatarUrl . $ver }}"
                alt="プロフィール画像"
            >
            <input id="avatar" type="file" name="avatar" accept=".jpg,.jpeg,.png" class="visually-hidden">
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

{{-- アバター画像プレビュー用JS --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput   = document.getElementById('avatar');
        const imgPreview  = document.querySelector('.avatar-preview');

        if (!fileInput || !imgPreview) return;

        fileInput.addEventListener('change', function (e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            // 画像ファイルじゃなければ何もしない
            if (!file.type.startsWith('image/')) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                imgPreview.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    });
</script>
@endsection
