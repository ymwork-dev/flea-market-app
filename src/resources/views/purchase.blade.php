@extends('layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/purchase.blade.css') }}">
@endpush

@section('content')
<div class="purchase-container">
    <div class="purchase-content">
        <div class="purchase-main">
            <div class="item-info">
                <div class="item-image">
                    <img src="{{ asset('storage/' . $item->img_url) }}" alt="{{ $item->name }}">
                </div>
                <div class="item-detail">
                    <h1>{{ $item->name }}</h1>
                    <p>¥ {{ number_format($item->price) }}</p>
                </div>
            </div>

            <hr>

            <div class="selection-section">
                <h2>支払い方法</h2>
                <div class="select-wrapper">
                    <select name="payment_method_select" id="payment-select" class="select-box">
                        <option value="" disabled {{ !session('payment_method') ? 'selected' : '' }}>選択してください</option>
                        <option value="コンビニ払い" {{ session('payment_method') == 'コンビニ払い' ? 'selected' : '' }}>コンビニ払い</option>
                        <option value="カード支払い" {{ session('payment_method') == 'カード支払い' ? 'selected' : '' }}>カード支払い</option>
                    </select>
                </div>
            </div>

            <hr>

            <div class="selection-section">
                <div class="section-header">
                    <h2>配送先</h2>
                    <a href="{{ route('purchase.address.edit', ['item_id' => $item->id]) }}" class="change-link">変更する</a>
                </div>
                <div class="address-display">
                    <p>〒 {{ $user->postcode }}</p>
                    <p>{{ $user->address }}{{ $user->building }}</p>
                </div>
            </div>

            <hr>
        </div>

        <div class="purchase-side-area">
            <div class="purchase-side-box">
                <table class="summary-table">
                    <tr>
                        <th>商品代金</th>
                        <td>¥ {{ number_format($item->price) }}</td>
                    </tr>
                    <tr>
                        <th>支払い方法</th>
                        <td id="display-payment">{{ session('payment_method') ?: '未選択' }}</td>
                    </tr>
                </table>
            </div>

            <form action="{{ route('purchase.store', ['item_id' => $item->id]) }}" method="POST" class="purchase-form">
                @csrf
                <input type="hidden" name="payment_method" id="hidden-payment" value="{{ session('payment_method') }}">

                <button type="submit" class="purchase-button" id="purchase-btn" {{ session('payment_method') ? '' : 'disabled' }}>
                    購入する
                </button>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('js/purchase.js') }}"></script>
@endsection
