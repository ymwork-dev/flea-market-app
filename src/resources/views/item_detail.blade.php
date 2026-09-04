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

        <div class="seller-info">
            <span class="seller-name">出品者: {{ $item->user->name }}</span>
            @if($sellerRatingsCount > 0)
                <span class="seller-rating">評価 {{ number_format($sellerRatingsAverage, 1) }} ({{ $sellerRatingsCount }}件)</span>
            @else
                <span class="seller-rating">評価なし</span>
            @endif
        </div>

        <div class="detail-actions">
            <div class="action-item">
                <button type="button" class="like-button" id="like-btn" data-item-id="{{ $item->id }}">
                    @if($isLiked)
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

        @if($isOwner && $item->is_sold)
            @if(!$isPaid)
                <button class="btn-purchase is-sold" disabled>支払い待ちです</button>
            @elseif($isReceived)
                <button class="btn-purchase is-sold" disabled>取引が完了しました</button>
            @elseif($isShipped)
                <button class="btn-purchase is-sold" disabled>発送済みです</button>
            @else
                <form action="{{ route('item.ship', ['item_id' => $item->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-purchase">発送する</button>
                </form>
            @endif
        @elseif($isOwner)
            <div class="owner-actions">
                <a href="{{ route('item.edit', ['item_id' => $item->id]) }}" class="btn-purchase">編集する</a>
                <button type="button" class="btn-purchase btn-secondary js-delete-open">削除する</button>
            </div>

            {{-- 削除確認ポップアップ(ブラウザ標準のconfirm()は使わず、独自デザインで表示する) --}}
            <div id="delete-modal" class="modal">
                <div class="modal-content">
                    <p class="modal-message">本当にこの商品を削除しますか？</p>
                    <div class="modal-actions">
                        <button type="button" class="btn-modal-cancel js-delete-cancel">キャンセル</button>
                        <form action="{{ route('item.destroy', ['item_id' => $item->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-modal-delete">削除する</button>
                        </form>
                    </div>
                </div>
            </div>
        @elseif($isBuyer)
            @if(!$isPaid)
                <button class="btn-purchase is-sold" disabled>コンビニでのお支払いを完了してください</button>
            @elseif(!$isShipped)
                <button class="btn-purchase is-sold" disabled>発送準備中です</button>
            @elseif(!$isReceived)
                <form action="{{ route('item.receive', ['item_id' => $item->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-purchase">受け取りました</button>
                </form>
            @else
                <button class="btn-purchase is-sold" disabled>取引が完了しました</button>
            @endif
        @elseif($item->is_sold)
            <button class="btn-purchase is-sold" disabled>売り切れました</button>
        @else
            <a href="/purchase/{{ $item->id }}" class="btn-purchase">購入手続きへ</a>
        @endif

        {{-- 購入者が受け取り確認をした後、出品者を評価するためのフォーム(未評価の時だけ表示) --}}
        @if($canRate)
            <div class="rating-form">
                <h2 class="section-title">出品者を評価する</h2>
                <form action="{{ route('rating.store', ['item_id' => $item->id]) }}" method="POST">
                    @csrf
                    <div>
                        <label for="score">評価</label>
                        <select name="score" id="score">
                            <option value="">選択してください</option>
                            <option value="5">5(とても良い)</option>
                            <option value="4">4(良い)</option>
                            <option value="3">3(普通)</option>
                            <option value="2">2(良くない)</option>
                            <option value="1">1(悪い)</option>
                        </select>
                        @error('score')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="comment">コメント(任意)</label>
                        <textarea name="comment" id="comment"></textarea>
                        @error('comment')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn-purchase">評価を送信する</button>
                </form>
            </div>
        @endif

        {{-- 購入者が付けた評価は、購入者本人にも出品者にも見えるようにする --}}
        @if($rating && ($isBuyer || $isOwner))
            <div class="rating-result">
                <h2 class="section-title">{{ $isOwner ? '購入者からの評価' : 'あなたが送った評価' }}</h2>
                <p>評価: {{ $rating->score }} / 5</p>
                @if($rating->comment)
                    <p>{{ $rating->comment }}</p>
                @endif
            </div>
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
            @foreach($comments as $comment)
                <div class="comment-item">
                    <div class="comment-user">
                        <div class="user-icon">
                            @if($comment->user->img_url)
                                <img src="{{ asset('storage/' . $comment->user->img_url) }}" alt="ユーザー画像">
                            @endif
                        </div>
                        <span class="user-name">{{ $comment->user->name }}</span>
                    </div>
                    <div class="comment-text">
                        {{ $comment->comment }}
                    </div>

                    {{-- 出品者からの返信一覧 --}}
                    @foreach($comment->replies as $reply)
                        <div class="comment-reply">
                            <div class="comment-user">
                                <div class="user-icon">
                                    @if($reply->user->img_url)
                                        <img src="{{ asset('storage/' . $reply->user->img_url) }}" alt="ユーザー画像">
                                    @endif
                                </div>
                                <span class="user-name">{{ $reply->user->name }}</span>
                                <span class="reply-badge">出品者</span>
                            </div>
                            <div class="comment-text">
                                {{ $reply->comment }}
                            </div>
                        </div>
                    @endforeach

                    {{-- 出品者本人だけが返信フォームを見られる(売却済みなら非表示) --}}
                    @if($isOwner && !$item->is_sold)
                        <form action="{{ route('comment.reply', ['item_id' => $item->id, 'comment_id' => $comment->id]) }}" method="POST" class="reply-form">
                            @csrf
                            <textarea name="comment" class="comment-textarea reply-textarea" placeholder="このコメントに返信する" maxlength="255">{{ old('comment') }}</textarea>
                            @error('comment')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="btn-comment-submit btn-reply-submit">返信する</button>
                        </form>
                    @endif
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
@if($isOwner)
    <script src="{{ asset('js/delete-confirm.js') }}"></script>
@endif
@endsection
