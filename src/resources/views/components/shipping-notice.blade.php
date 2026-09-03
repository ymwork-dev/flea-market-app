@if($count > 0)
    <div class="shipping-notice">
        発送が必要な商品が{{ $count }}件あります。
        <a href="{{ route('mypage') }}">確認する</a>
    </div>
@endif
