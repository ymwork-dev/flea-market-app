<?php

// URL振り分け表オブジェクトを使えるように設定
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\DemoLoginController;

// 商品一覧・詳細ページは、プロフィール未設定でも閲覧可能。
// ログイン済みでメール未認証や郵便番号未設定の場合、該当画面にリダイレクト
Route::middleware(['ensure.verified.profile'])->group(function () {
    Route::get('/', [ItemController::class, 'index'])->name('item.index');
    Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.show');
});

// ログイン済み・メール認証済みの場合、プロフィールの閲覧が可能。
// demoアカウントは編集不可
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/mypage/profile', [ProfileController::class, 'update'])
        ->middleware('restrict.demo.account')
        ->name('profile.update');
});

// ログイン済み・メール認証済み・プロフィール設定済みの場合、出品・購入・コメント・いいね・配送先変更が可能
Route::middleware(['auth', 'verified', 'ensure.profile.completed'])->group(function () {

    // マイページ操作可能
    Route::get('/mypage', [ProfileController::class, 'index'])
        ->name('mypage');

    Route::get('/sell', [SellController::class, 'sell'])->name('sell');
    Route::post('/sell', [SellController::class, 'store'])->name('item.store');
    Route::get('/sell/{item_id}/edit', [SellController::class, 'edit'])->name('item.edit');
    Route::put('/sell/{item_id}', [SellController::class, 'update'])->name('item.update');
    Route::delete('/sell/{item_id}', [SellController::class, 'destroy'])->name('item.destroy');

    Route::get('/purchase/{item_id}', [PurchaseController::class, 'showPurchasePage'])->name('purchase.show');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'purchase'])->name('purchase.store');
    Route::get('/purchase/success/{item_id}', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::post('/item/{item_id}/ship', [PurchaseController::class, 'ship'])->name('item.ship');
    Route::post('/item/{item_id}/receive', [PurchaseController::class, 'receive'])->name('item.receive');

    Route::post('/comment/{item_id}/comment', [CommentController::class, 'storeComment'])->name('comment.store');
    Route::post('/comment/{item_id}/comment/{comment_id}/reply', [CommentController::class, 'storeReply'])->name('comment.reply');

    Route::post('/like/{item_id}/like', [LikeController::class, 'toggleLike'])->name('like.toggle');

    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.address.edit');
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');

    // 選択した支払方法を一時的にセッションへ保存する。
    Route::post('/purchase/payment/store-session', [PurchaseController::class, 'storePaymentSession']);
    });

Route::post('/demo-login/{type}', [DemoLoginController::class, 'login'])->name('demo-login');

// Stripeから直接呼ばれるURL。ログインもCSRFトークンも持っていないのでauthミドルウェアのグループには入れない
// コントローラー側で、Stripe-Signature署名検証
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');


Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// 修正後：ボタンを押したらメールボックスに行かず、認証を完了させて次に進む
Route::get('/email/go-to-mailpit', function () {
    $user = Auth::user(); // 現在新規登録してログイン状態になっているユーザーを取得

    // ユーザーのメール認証がまだ済んでいなければ
    if ($user && !$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified(); // データベースの email_verified_at に現在時刻を強制書き込み
    }

    // マイページ（プロフィール設定画面へのミドルウェアが走る場所）へリダイレクト
    return redirect()->route('mypage');
})->middleware('auth')->name('verification.show');
