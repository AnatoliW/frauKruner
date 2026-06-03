<?php


use App\Http\Controllers\Buyer\BuyerController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\Seller\PaymentController;
use Illuminate\Support\Facades\Route;


Route::get('/', [BuyerController::class, 'dashboard'])->name('dashboard');
Route::get('orders', [BuyerController::class, 'orders'])->name('orders');
Route::get('news', [BuyerController::class, 'news'])->name('news');
Route::get('user-data', [BuyerController::class, 'userData'])->name('user.data');
Route::get('data/verification', [BuyerController::class, 'verify'])->name('data.verify');
Route::get('address', [BuyerController::class, 'address'])->name('address');
Route::get('photos/{order}', [BuyerController::class, 'photos'])->name('photos');
Route::get('favoriten', [FavoriteController::class, 'index'])->name('favorites');
Route::post('delete/{favorite}', [FavoriteController::class, 'delete'])->name('favorite.delete');
Route::get('pre-payemnt/{order}', [PaymentController::class, 'prepayment'])->name('pre.payment');
Route::post('pre-payemnt/prove/{order}', [PaymentController::class, 'prepaymentProve'])->name('pre.payment.prove');
Route::get('video/play/{order}',[BuyerController::class,'videoPlayer'])->name('video.player');
