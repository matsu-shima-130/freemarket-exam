<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ExhibitionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\AddressController;

Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item}', [ItemController::class, 'show'])->name('items.show');

// ★ デザイン確認用（ブラウザで http://localhost/debug/verify-email にアクセス）
Route::view('/debug/verify-email', 'auth.verify-email')->name('debug.verify-email');

Route::get('/mail-test', function () {
    Mail::raw('Mailtrap テストメールです。', function ($message) {
        $message->to('test@example.com')
                ->subject('Mailtrap テストメール');
    });

    return 'テストメールを送信しました。ブラウザはこのメッセージが出ればOK！';
});

// 未ログイン時のみアクセスOK（登録/ログイン）
Route::middleware('guest')->group(function () {
    // 会員登録
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register.create');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');

    // ログイン
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.attempt');
});

// auth だけあればOKなもの（＝メール未認証でも使えるもの）
Route::middleware('auth')->group(function () {
    // ログアウトだけは「未認証でも」押させてあげたいのでここに置いておく
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// ログイン + メール認証必須エリア
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index');

    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/sell',  [ExhibitionController::class, 'create'])->name('sell.create');
    Route::post('/sell', [ExhibitionController::class, 'store'])->name('sell.store');

    // コメント
    Route::post('/items/{item}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // いいね（トグル方式）
    Route::post('/items/{item}/like', [LikeController::class, 'toggle'])->name('likes.toggle');

    Route::get('/purchase/{item}', [PurchaseController::class, 'index'])->name('purchase.index');
    Route::post('/purchase/{item}', [PurchaseController::class, 'store'])->name('purchase.store');

    Route::get('/purchase/address/{item}', [AddressController::class, 'edit'])->name('purchase.address.edit');
    Route::post('/purchase/address/{item}', [AddressController::class, 'update'])->name('purchase.address.update');
});
