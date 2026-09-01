<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;

Route::middleware(['ensure.verified.profile'])->group(function () {
    Route::get('/', [ItemController::class, 'index'])->name('item.index');
    Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.show');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified', 'ensure.profile.completed'])->group(function () {

    Route::get('/mypage', [ProfileController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('mypage');

    Route::get('/sell', [SellController::class, 'sell'])->name('sell');
    Route::post('/sell', [SellController::class, 'store'])->name('item.store');

    Route::get('/purchase/{item_id}', [PurchaseController::class, 'showPurchasePage'])->name('purchase.show');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'purchase'])->name('purchase.store');
    Route::get('/purchase/success/{item_id}', [PurchaseController::class, 'success'])->name('purchase.success');

    Route::post('/comment/{item_id}/comment', [CommentController::class, 'storeComment'])->name('comment.store');

    Route::post('/like/{item_id}/like', [LikeController::class, 'toggleLike'])->name('like.toggle');

    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.address.edit');
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');

    Route::post('/purchase/payment/store-session', [PurchaseController::class, 'storePaymentSession']);
    });

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
