@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/purchases-address.css') }}">
@endsection

@section('content')
<div class="address">
    <div class="address__inner">

        <h1 class="address__title">住所の変更</h1>

        <form action="{{ route('purchase.address.update', $item) }}" method="post" class="address-form">
            @csrf

            <div class="address-form__group">
                <label for="postal_code" class="address-form__label">郵便番号</label>
                <input
                    type="text"
                    name="postal_code"
                    id="postal_code"
                    class="address-form__input"
                    value="{{ old('postal_code', $profile->postal_code ?? '') }}"
                >
                @error('postal_code')
                    <p class="address-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="address-form__group">
                <label for="address" class="address-form__label">住所</label>
                <input
                    type="text"
                    name="address"
                    id="address"
                    class="address-form__input"
                    value="{{ old('address', $profile->address ?? '') }}"
                >
                @error('address')
                    <p class="address-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="address-form__group">
                <label for="building" class="address-form__label">建物名</label>
                <input
                    type="text"
                    name="building"
                    id="building"
                    class="address-form__input"
                    value="{{ old('building', $profile->building ?? '') }}"
                >
                @error('building')
                    <p class="address-form__error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="address-form__btn">
                更新する
            </button>
        </form>

    </div>
</div>
@endsection
