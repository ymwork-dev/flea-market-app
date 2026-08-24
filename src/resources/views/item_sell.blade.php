@extends('layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/item_sell.css') }}">
@endpush

@section('content')
<div class="sell-container">
    <h1 class="sell-title">商品の出品</h1>

    @if ($errors->any())
        <div class="error-container">
            <ul class="error-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('item.store') }}" method="POST" enctype="multipart/form-data" class="sell-form">
        @csrf
        <section class="sell-section">
            <label for="img_url" class="form-label">商品画像</label>
            <div class="form-group-image">
                <label class="image-select-label">
                    画像を選択する
                    <input type="file" name="img_url" id="img_url" class="file-input">
                </label>
            </div>
        </section>

        <section class="sell-section">
            <h2 class="sell-section-title">商品の詳細</h2>

            <div class="form-group">
                <label for="category" class="form-label">カテゴリー</label>
                <div class="category-group">
                    @foreach($categories as $category)
                        <label class="category-label">
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}">
                            {{ $category->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label for="condition" class="form-label">商品の状態</label>
                <select name="condition" id="condition" class="form-select">
                    <option value="">選択してください</option>
                    <option value="良好">良好</option>
                    <option value="目立った傷や汚れなし">目立った傷や汚れなし</option>
                    <option value="やや傷や汚れあり">やや傷や汚れあり</option>
                    <option value="状態が悪い">状態が悪い</option>
                </select>
            </div>
        </section>

        <section class="sell-section">
            <h2 class="sell-section-title">商品名と説明</h2>

            <div class="form-group">
                <label for="name" class="form-label">商品名</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-input">
            </div>

            <div class="form-group">
                <label for="brand" class="form-label">ブランド名</label>
                <input type="text" name="brand" id="brand" value="{{ old('brand') }}" class="form-input">
            </div>

            <div class="form-group">
                <label for="description" class="form-label">商品の説明</label>
                <textarea name="description" id="description" class="form-textarea">{{ old('description') }}</textarea>
            </div>
        </section>

        <section class="sell-section">
            <div class="form-group">
                <label for="price" class="form-label">販売価格</label>
                <div class="price-input-container">
                    <span class="price-symbol">¥</span>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" class="form-input price-field">
                </div>
            </div>
        </section>

        <div class="form-submit">
            <button type="submit" class="sell-btn">出品する</button>
        </div>
    </form>
</div>

<script src="{{ asset('js/sell.js') }}"></script>

@endsection
