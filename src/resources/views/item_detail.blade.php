@extends('layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/item_detail.css') }}">
@endpush

@section('content')
<div class="detail-container">
    <div class="detail-image">
        <img src="{{ asset('storage/' . $item->img_url) }}" alt="{{ $item->name }}">
    </div>

    <div class="detail-info">
        <h1 class="detail-name">{{ $item->name }}</h1>
        <p class="detail-brand">{{ $item->brand ?? 'ブランド名なし' }}</p>
        <p class="detail-price">
            ¥{{ number_format($item->price) }} <span>(税込)</span>
        </p>

        <div class="detail-actions">
            <div class="action-item">
                <button type="button" class="like-button" id="like-btn" data-item-id="{{ $item->id }}">
                    @if(Auth::check() && Auth::user()->likedItems->contains($item->id))
                        <img src="{{ asset('img/liked.png') }}" alt="いいね済み" class="heart-icon" id="heart-img">
                    @else
                        <img src="{{ asset('img/HeartLogo.png') }}" alt="いいね" class="heart-icon" id="heart-img">
                    @endif
                </button>
                <span class="like-count" id="like-count">{{ $item->likedByUsers()->count() }}</span>
            </div>

            <div class="action-item">
                <img src="{{ asset('img/CommentLogo.png') }}" alt="コメント" class="comment-icon">
                <span class="count">{{ $item->comments->count() }}</span>
            </div>
        </div>

        @if(Auth::check() && Auth::id() === $item->user_id)
            @if($item->is_sold)
                <button class="btn-purchase is-sold" disabled style="background-color: #888; cursor: not-allowed;">売り切れました</button>
            @else
                <div class="owner-actions">
                    <a href="{{ route('item.edit', ['item_id' => $item->id]) }}" class="btn-purchase">編集する</a>
                    <form action="{{ route('item.destroy', ['item_id' => $item->id]) }}" method="POST"
                        onsubmit="return confirm('本当に削除しますか？');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-purchase" style="background-color: #888;">削除する</button>
                    </form>
                </div>
            @endif
        @elseif($item->is_sold)
            <button class="btn-purchase is-sold" disabled style="background-color: #888; cursor: not-allowed;">売り切れました</button>
        @else
            <a href="/purchase/{{ $item->id }}" class="btn-purchase">購入手続きへ</a>
        @endif

        <div class="detail-description">
            <h2 class="section-title">商品説明</h2>
            <p>{{ $item->description }}</p>
        </div>

        <div class="detail-info-section">
            <h2 class="section-title">商品情報</h2>
            <table class="info-table">
                <tr>
                    <th>カテゴリー</th>
                    <td>
                        @if($item->categories && $item->categories->count() > 0)
                            @foreach($item->categories as $category)
                                <span class="category-tag">{{ $category->name }}</span>
                            @endforeach
                        @else
                            <span>カテゴリーなし</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>商品の状態</th>
                    <td>{{ $item->condition }}</td>
                </tr>
            </table>
        </div>

        <div class="detail-comments">
            <h2 class="section-title">コメント({{ $item->comments->count() }})</h2>
            @foreach($item->comments as $comment)
                <div class="comment-item">
                    <div class="comment-user">
                        <div class="user-icon">
                            @if($comment->user->img_url)
                                <img src="{{ asset('storage/' . $comment->user->img_url) }}" alt="ユーザー画像" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            @endif
                        </div>
                        <span class="user-name">{{ $comment->user->name }}</span>
                    </div>
                    <div class="comment-text">
                        {{ $comment->comment }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="comment-form-section">
            <h2 class="section-title">商品へのコメント</h2>
            @if($item->is_sold)
                <p class="sold-out-message">※この商品は売り切れているため、コメントできません。</p>
            @else
                <form action="{{ route('comment.store', ['item_id' => $item->id]) }}" method="POST">
                    @csrf
                    <textarea name="comment" class="comment-textarea">{{ old('comment') }}</textarea>

                    @error('comment')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="btn-comment-submit">コメントを送信する</button>
                </form>
            @endif
        </div>
    </div>
</div>

<script src="{{ asset('js/like.js') }}"></script>
@endsection
