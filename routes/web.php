<?php

use App\Http\Controllers\AdminController;
use App\Mail\LoginAlertEMail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SellerRegistrationController;
use Intervention\Image\Facades\Image as ImageIntervention;
use Intervention\Image\Constraint;
use App\Http\Controllers\PayoutsController;
use App\Mail\NotifyEmail;
use App\Mail\OrderPlaced;
use App\Mail\ResendEmail;
use App\Mail\UserNotifyEmail;
use App\Mail\UserOrderEmail;
use App\Mail\VendorOrderEmail;
use App\Mail\VerifyEmail;
use App\Models\Boost;
use App\Models\Image;
use App\Models\Log;
use App\Models\User;
use App\Order;
use App\Product;
use App\Rating;
use App\Services\Turnstile;
use App\Services\TurnstileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Stripe\Payout;

use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\X264;
use FFMpeg\Format\Video\WMV;
use FFMpeg\Format\Video\WebM;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/nur-fuer-volljaehrige', [PageController::class, 'ageRestricted'])->name('age.restricted');
Route::get('product/{slug}', [PageController::class, 'product'])->name('product');
Route::post('add-cart', [CartController::class, 'add'])->name('cart.store');


Route::get('shop', [PageController::class, 'shop'])->name('shop');
Route::get('/Neuigkeiten', [PageController::class, 'blog'])->name('blog');
Route::get('/Neuigkeiten/{slug}', [PageController::class, 'post_details'])->name('post_details');
Route::get('/Neuigkeiten/{slug}/next', [PageController::class, 'nextpost'])->name('posts.next');
Route::get('/Neuigkeiten/{slug}/previus', [PageController::class, 'previouspost'])->name('posts.previous');
Route::get('/cart', [PageController::class, 'cart'])->name('cart');
Route::get('/checkout', [PageController::class, 'checkout'])->name('checkout');
Route::get('/thankyou', [PageController::class, 'thankyou'])->name('thankyou');
Route::get('/per-payment-thank/{order}', [PageController::class, 'preThankyou'])->name('pre.thankyou');
Route::get('/page/{slug}', [PageController::class, 'page'])->name('page');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact-store', [PageController::class, 'contact_store'])->name('contact.store');

Route::get('home/seller/{user}/{order?}', [PageController::class, 'userProfile'])->name('user.profile');
Route::get('bestseller', [PageController::class, 'bestseller'])->name('bestseller');
Route::get('about', [PageController::class, 'about'])->name('about');
Route::get('profile', [PageController::class, 'profile'])->name('profile');
Route::get('faq', [PageController::class, 'faq'])->name('faq');
Route::get('vendors', [PageController::class, 'vendors'])->name('vendors');
Route::get('/reviews', [PageController::class, 'reviews'])->name('reviews');
Route::get('/getragene-unterwaesche-kaufen', [PageController::class, 'newpage'])->name('newpage');
Route::get('sitemap.xml',[PageController::class,'sitemap'])->name('sitemap');





// seller registration
Route::group(['prefix' => 'seller/registration/', 'as' => 'seller.'], function () {
    Route::get('/', [SellerRegistrationController::class, 'regStepFirst'])->name('registration')->middleware('guest');
    Route::post('/', [SellerRegistrationController::class, 'regStepFirstStore'])->name('registration.step.two');
    Route::get('verification', [SellerRegistrationController::class, 'regStepSecond'])->name('verification');
});


//Route::get('register', [RegistrationController::class, 'regStepFirst'])->name('first')->middleware('guest');

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'role:admin']], function () {
    Route::get('/user/{user}/active', [HomeController::class, 'userActive'])->name('admin.user.active');
    Route::get('/user/{user}/deactive', [HomeController::class, 'userDeactive'])->name('admin.user.deactive');
    Route::get('/order/{order}/cancel', [HomeController::class, 'orderCancel'])->name('admin.order.cancel');
    Route::post('user-is-commercial/{user}', [HomeController::class, 'isCommercial'])->name('admin.isCommercial');
});

Auth::routes();



//cart routes
Route::post('/add-cart', [CartController::class, 'add'])->name('cart.store');
Route::post('/add-update', [CartController::class, 'update'])->name('cart.update');
Route::get('/cart-destroy/{id}', [CartController::class, 'destroy'])->name('cart.destroy');

//checkout routes
Route::post('/store-checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/payment/{order}', [CheckoutController::class, 'payment'])->name('payment');
Route::post('/payment/process', [CheckoutController::class, 'processPayment'])->name('payment.process');
Route::post('/payment/store/{order}', [CheckoutController::class, 'paymentStore'])->name('payment.store');

Route::post('add-favorites/{id}', [FavoriteController::class, 'store'])->name('favorite.store');
Route::post('delete/{favorite}', [FavoriteController::class, 'delete'])->name('buyer.favorite.delete');

//shipping routes
Route::post('/shipping', [ShopController::class, 'shipping'])->name('shipping');


//coupon routes
Route::post('/add-coupon', [CouponController::class, 'add'])->name('coupon');
Route::get('/delete-coupon', [CouponController::class, 'destroy'])->name('coupon.destroy');

//rating routes
Route::post('rating/{user}/{order?}', [PageController::class, 'rating'])->name('rating')->middleware('auth');
//search routes
Route::get('search', [PageController::class, 'search'])->name('search');

//user routes
Route::get('dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
Route::get('orders', [HomeController::class, 'orders'])->name('orders');
Route::get('invoice/{order}', [HomeController::class, 'invoice'])->name('invoice')->middleware('auth');
Route::post('user-update', [HomeController::class, 'update'])->name('user.update');
Route::post('change-password', [HomeController::class, 'ChangePassword'])->name('change.password');
Route::get('verify/email', [HomeController::class, 'verifyEmail'])->name('verify.token');
Route::get('verify-email', [HomeController::class, 'verifyMassage'])->name('verify.massage');

Route::get('user/delete', [HomeController::class, 'userDelete'])->name('user.delete');


Route::middleware('auth')->group(function () {
    Route::post('/info/updated', [ProfileController::class, 'info'])->name('info.update');
    Route::post('/address/updated', [ProfileController::class, 'address'])->name('address.update');
    Route::post('/verification/updated', [ProfileController::class, 'verification'])->name('verification.update');
});
Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth', 'role:admin']
], function () {
    Route::get('payout/{order}/{page?}', [PayoutsController::class, 'payouts'])->name('payout');

    Route::get('payment/{payment}', [PayoutsController::class, 'payment'])->name('admin.payment');
    Route::post('payment/{payment}/process', [PayoutsController::class, 'paymentProcess'])->name('admin.payment.process');

    Route::get('boost/{type}/{id}', [PayoutsController::class, 'boost'])->name('admin.boost');
    Route::post('boost/{type}/{id}', [PayoutsController::class, 'boostStore'])->name('admin.boost.store');

    Route::get('order/payouts', [AdminController::class, 'index'])->name('admin.order.payouts');
    Route::get('order/lists', [AdminController::class, 'lists'])->name('admin.order.lists');
    Route::get('order/prepayments', [AdminController::class, 'prepayments'])->name('admin.order.prepayments');
    Route::get('order/advance-payemnt/check/{order}', [AdminController::class, 'payemntCheck'])->name('admin.order.payment.check');
    Route::post('order/advance-payemnt/check/{order}', [AdminController::class, 'payemntCheckUpdate'])->name('admin.order.payment.check.update');
    Route::get('boost/list', [AdminController::class, 'boosts'])->name('admin.boosts.list');
    Route::get('boosts/invoice/{boost}', [AdminController::class, 'boostInvoice'])->name('admin.boost.invoice');

});

Route::get('legacy/voyager/orders/{order}', function (Order $order) {
    return redirect()->route('invoice', $order);
})->middleware(['auth'])->name('voyager.orders.show');

Route::get('legacy/voyager/users/{user}/edit', function (User $user) {
    return redirect()->route('profile');
})->middleware(['auth'])->name('voyager.users.edit');

// Route::get('test',[ProfileController::class, 'test']);

Route::get('email', function () {
    $email = request('email');
    if (!$email) {
        $email = 'k@fraukruner.de';
    }
    $order = Order::latest()->first();
    return (new OrderPlaced($order));
});




if (env('APP_ENV') == 'local') {
    Route::get('/test/login-as-user/{user}', function (User $user) {
        Auth::logout();
        Auth::login($user);
        return redirect('/');
    });
}


// Route::get('test', function () {
//     $boosts = Boost::get();
//     foreach ($boosts as $boost) {
//         if (!isset($boost->user_info->street) && $boost->user?->verification !== null) {
//             $data = [
//                 'f_name' => $boost->user?->name,
//                 'l_name' => $boost->user?->last_name,
//                 'street' => $boost->user?->verification?->street,
//                 'house_no' => $boost->user?->verification?->house_no,
//                 'zip' => $boost->user?->verification?->zip,
//                 'federal_state' => $boost->user?->verification?->city,
//                 'email' => $boost->user?->email,
//                 'vat_number' => $boost->user?->vat,
//                 'is_pay_vat' => $boost->user?->is_pay_vat,
//                 'vat_perchatage' => setting('finance.vat'),
//             ];

//             $boost->update([
//                 'user_info' => $data,
//             ]);
//         }
//     }


//     return 'success';
// });

Route::get('/test-error', function () {
    // throw new Exception("This is a test error!");
    $mail_data = [
        'subject' => 'Neue Nachricht',
        'title' =>'hello',
        'body' => 'hello',
        'button_link' => 'https://www.fraukruner.de/login',
        'button_text' => 'Zum Dashboard',
    ];

    Mail::to('sohojwareltd@gmail.com')->send(new UserNotifyEmail($mail_data));
});