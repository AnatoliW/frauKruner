<?php

use App\Http\Controllers\Seller\BoostController;
use App\Http\Controllers\Seller\PagesController;
use App\Http\Controllers\Seller\PaymentController;
use App\Http\Controllers\Seller\ProductsController;
use App\Models\Boost;
use Illuminate\Support\Facades\Route;


Route::get('/', [PagesController::class, 'dashboard'])->name('dashboard');
Route::get('sales', [PagesController::class, 'sales'])->name('sales');
Route::get('news', [PagesController::class, 'news'])->name('news');
Route::get('payments', [PagesController::class, 'payments'])->name('payments');
Route::get('review', [PagesController::class, 'review'])->name('reviews');
Route::get('user-data', [PagesController::class, 'userData'])->name('user_data');
Route::get('address', [PagesController::class, 'address'])->name('address');
Route::post('visibility', [PagesController::class, 'visibility'])->name('visibility');
Route::post('order/update/{order}', [PagesController::class, 'orderUpdate'])->name('order.update');
Route::post('order/payouts/request/{order}', [PagesController::class, 'payoutsRequest'])->name('payouts.request');

Route::get('products', [ProductsController::class, 'index'])->name('products');
Route::get('products/create', [ProductsController::class, 'create'])->name('products.create');
Route::post('products/create', [ProductsController::class, 'store'])->name('products.store');
Route::post('Images/store', [ProductsController::class, 'image'])->name('image.store');
Route::get('products/{product:slug}/edit', [ProductsController::class, 'edit'])->name('products.edit');
Route::post('products/{product}/update', [ProductsController::class, 'update'])->name('products.update');
Route::post('products/{product}/delete', [ProductsController::class, 'delete'])->name('products.delete');

Route::post('photo/upload', [PagesController::class, 'photoUpload'])->name('photo.upload');
Route::get('photos/{order}', [PagesController::class, 'photos'])->name('photos');
Route::post('photo/delete/{orderimage}', [PagesController::class, 'photoDelete'])->name('photo.delete');
Route::post('video/upload', [PagesController::class, 'VideoUpload'])->name('video.upload');
Route::get('/product-active/{product}', [ProductsController::class, 'productActive'])->name('product.active');
Route::post('boots/store/{product?}', [BoostController::class, 'boostStore'])->name('boost.store');

Route::get('/payment/{payment}', [PaymentController::class, 'payment'])->name('payment');
Route::get('payment/process/{payment}', [PaymentController::class, 'paymentProcess'])->name('payment.process');
Route::get('payment/success/{payment}', [PaymentController::class, 'success'])->name('payment.success');

Route::get('charges', [PagesController::class, 'charges'])->name('charges');
Route::get('charges/invoice/{boost}', [PagesController::class, 'chargeInvoice'])->name('charges.invoice');