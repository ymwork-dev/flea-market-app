@extends('layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/purchase_address.css') }}">
@endpush

@section('content')
<div class="address-container">
    <h1>住所の変更</h1>

    <form action="{{ route('purchase.address.update', ['item_id' => $item->id]) }}" method="POST">
        @csrf
        <div>
            <label for="postcode">郵便番号</label>
            <input type="text" name="postcode" id="postcode" value="{{ old('postcode', $user->postcode) }}">
            @error('postcode')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label for="address">住所</label>
            <input type="text" name="address" id="address" value="{{ old('address', $user->address) }}">
            @error('address')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label for="building">建物名</label>
            <input type="text" name="building" id="building" value="{{ old('building', $user->building) }}">
            @error('building')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit">更新する</button>
    </form>
</div>
@endsection
