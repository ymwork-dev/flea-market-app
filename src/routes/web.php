<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\DemoLoginController;
use App\Http\Controllers\EmailVerificationController;

// 商品一覧・詳細ページは、プロフィール未設定でも閲覧可能。
// ログイン済みでメール未認証や郵便番号未設定の場合、該当画面にリダイレクト
Route::middleware(['ensure.verified.profile'])->group(function () {
    Route::get('/', [ItemController::class, 'index'])->name('item.index');
    Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.show');
});

// ログイン済み・メール認証済みの場合、プロフィールの表示と編集が可能。
// demoアカウントは編集不可
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/mypage/profile', [ProfileController::class, 'update'])
        ->middleware('restrict.demo.account')
        ->name('profile.update');
});

// ログイン済み・メール認証済み・プロフィール設定済みの場合、出品・購入・コメント・いいね・配送先変更が可能
Route::middleware(['auth', 'verified', 'ensure.profile.completed'])->group(function () {

    // マイページの表示
    Route::get('/mypage', [ProfileController::class, 'index'])
        ->name('mypage');

    // 出品画面の表示
    Route::get('/sell', [SellController::class, 'sell'])->name('sell');
    // 出品した商品の保存
    Route::post('/sell', [SellController::class, 'store'])->name('item.store');
    // 出品の編集画面を表示
    Route::get('/sell/{item_id}/edit', [SellController::class, 'edit'])->name('item.edit');
    // 編集した商品の更新
    Route::put('/sell/{item_id}', [SellController::class, 'update'])->name('item.update');
    // 出品の削除
    Route::delete('/sell/{item_id}', [SellController::class, 'destroy'])->name('item.destroy');

    // 購入画面の表示
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'showPurchasePage'])->name('purchase.show');
    // 購入の処理でStripe支払画面へ進む
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'purchase'])->name('purchase.store');
    // 購入完了後の画面表示
    Route::get('/purchase/success/{item_id}', [PurchaseController::class, 'success'])->name('purchase.success');
    // 商品発送の処理
    Route::post('/item/{item_id}/ship', [PurchaseController::class, 'ship'])->name('item.ship');
    // 商品受取りの処理
    Route::post('/item/{item_id}/receive', [PurchaseController::class, 'receive'])->name('item.receive');

    // コメントの処理
    Route::post('/comment/{item_id}/comment', [CommentController::class, 'storeComment'])->name('comment.store');
    // コメント返信の処理
    Route::post('/comment/{item_id}/comment/{comment_id}/reply', [CommentController::class, 'storeReply'])->name('comment.reply');

    // いいねの処理
    Route::post('/like/{item_id}/like', [LikeController::class, 'toggleLike'])->name('like.toggle');

    // 発送先住所画面の表示
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.address.edit');
    // 発送先住所編集の処理
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');

    // 選択した支払方法を一時的にセッションへ保存する処理
    Route::post('/purchase/payment/store-session', [PurchaseController::class, 'storePaymentSession']);
    });

// デモログインの処理
Route::post('/demo-login/{type}', [DemoLoginController::class, 'login'])->name('demo-login');

// Stripeから直接呼ばれるURL。ログインもCSRFトークンも持っていないのでauthミドルウェアのグループには入れない
// コントローラー側で、Stripe-Signature署名検証処理
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// メール認証画面で認証はこちらボタンを押せば認証完了させるルート
Route::get('/email/go-to-mailpit', [EmailVerificationController::class, 'completeVerification'])
    ->middleware('auth')
    ->name('verification.show');

