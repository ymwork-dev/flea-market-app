@extends('layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endpush

@section('content')
<div class="index-container">
    <div class="index-tabs">
    <a href="/?{{ http_build_query(array_merge(request()->query(), ['tab' => null])) }}"
        class="tab-item {{ !request()->has('tab') ? 'active' : '' }}">おすすめ</a>

    <a href="/?{{ http_build_query(array_merge(request()->query(), ['tab' => 'mylist'])) }}"
        class="tab-item {{ request()->get('tab') == 'mylist' ? 'active' : '' }}">マイリスト</a>
</div>

    <div class="product-grid">
        @foreach($items as $item)
            <a href="/item/{{ $item->id }}" class="product-card">
                <div class="product-image-wrapper">
                    <img src="{{ asset('storage/' . $item->img_url) }}" alt="{{ $item->name }}">

                    @if($item->is_sold)
                        <div class="sold-badge">Sold</div>
                    @endif
                </div>
                <p class="product-name">{{ $item->name }}</p>
            </a>
        @endforeach
    </div>
</div>
@endsection
