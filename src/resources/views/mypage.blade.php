@extends('layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endpush

@section('content')
<div class="main-container">
    <div class="profile-header">
        <div class="profile-info">
            <div class="profile-image">
                @if($user->img_url)
                    <img src="{{ asset('storage/' . $user->img_url) }}" alt="プロフィール画像">
                @endif
            </div>
            <h1 class="user-name">{{ $user->name }}</h1>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn-profile-edit">プロフィールを編集</a>
    </div>

    <div class="index-tabs">
        <a href="/mypage?page=sell" class="tab-item {{ request('page') != 'buy' ? 'active' : '' }}">出品した商品</a>
        <a href="/mypage?page=buy" class="tab-item {{ request('page') == 'buy' ? 'active' : '' }}">購入した商品</a>
    </div>

    <div class="product-grid">
        @foreach($items as $item)
            <a href="/item/{{ $item->id }}" class="product-card">
                <div class="product-image-wrapper">
                    <img src="{{ asset('storage/' . $item->img_url) }}" alt="{{ $item->name }}">
                    @if($item->needs_shipping)
                        <span class="sold-badge shipping-badge">発送準備中</span>
                    @elseif($item->is_sold)
                        <span class="sold-badge">Sold</span>
                    @endif
                </div>
                <p class="product-name">{{ $item->name }}</p>
            </a>
        @endforeach
    </div>
</div>
@endsection
