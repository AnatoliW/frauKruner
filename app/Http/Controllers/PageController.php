<?php

namespace App\Http\Controllers;

use App\Addition;
use App\Category;
use App\Contact;
use App\Faq;
use App\Finishing;
use App\Models\Post;
use App\Models\User;
use App\Order;
use App\Page;
use App\Postcat;
use App\Product;
use App\Rating;
use App\Shipping;
use App\Tag;
use App\WearingTime;
use Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function home()
    {
        // Cache categories for 60 minutes
        // $categories = Cache::remember('featured_categories', 60, function () {
        //     return Category::with('products')->featured()->orderBy('order', 'asc')->get();
        // });
        $categories = Cache::remember('categories_with_products', 60, function () {
            return Category::with(['products' => function ($query) {
                $query->with('user')
                    ->active()
                    ->visibility()
                    ->take(10); // Limit products to 10 per category
            }])->featured()->orderBy('order', 'asc')->get();
        });
        // Cache products for 60 minutes
        $products = Cache::remember('active_verified_products', 60, function () {
            return Product::with('category')
                ->active()
                ->verified()
                ->visibility()
                // ->productActive()
                ->customOrder()
                ->limit(10)
                ->get();
        });

        // Cache reviews for 60 minutes
        $reviews = Cache::remember('latest_reviews', 60, function () {
            return Rating::latest()->limit(3)->get();
        });

        return view('home', compact('categories', 'products', 'reviews'));
    }

    public function product($slug)
    {
        // Allow products from paused profiles to load - they display in paused state
        $product = Product::where('slug', $slug)
            ->whereHas('user', function ($q) {
                $q->where('verified', true);
            })
            ->firstOrFail();
        $related_products = Product::whereHas('category', function ($q) use ($product) {
            $q->where('slug', $product->category->slug);
        })
            ->whereHas('user', function ($q) {
                $q->where('status', true); // Exclude paused profiles
            })
            ->active()
            ->visibility()
            ->productActive()
            ->inRandomOrder()
            ->limit(3)
            ->get();

        // $related_products = Product::active()->limit(3)->get();
        return view('product', compact('product', 'related_products'));
    }

    public function shop()
    {
        $resolveCachedCollection = function (string $key, callable $loader) {
            $cached = Cache::get($key);

            if ($cached instanceof \Illuminate\Support\Collection && $cached->every(fn ($item) => is_object($item))) {
                return $cached;
            }

            Cache::forget($key);
            $fresh = $loader();
            Cache::put($key, $fresh, 1800);

            return $fresh;
        };

        $categories = $resolveCachedCollection('categories', function () {
            return Category::select(['id', 'name', 'slug', 'featured'])->get();
        });

        $products = Product::with('user')
            ->active()
            ->filter()
            ->customOrder()
            ->paginate(48);

        $products->each(function (Product $product) use ($categories) {
            $product->setRelation('category', $categories->where('id', $product->category_id)->first());
        });

        $profiles = User::with([
            'profile',
            'products' => fn ($query) => $query->active()->latest()->limit(12),
        ])
            ->where('role_id', 3)
            ->where('boosted', true)
            ->customOrder()
            ->withAvg('ratings as avg_rating', 'rating')
            ->withCount('ratings as total_rating')
            ->paginate(8);

        $profiles->each(function (User $user) use ($categories) {
            $user->products->each(function (Product $product) use ($categories, $user) {
                $product->setRelations([
                    'category' => $categories->where('id', $product->category_id)->first(),
                    'user' => $user,
                ]);
            });
        });

        $wearingTimes = $resolveCachedCollection('wearingTimes', function () {
            return WearingTime::all();
        });

        $finishings = $resolveCachedCollection('finishings', function () {
            return Finishing::all();
        });

        $additions = $resolveCachedCollection('additions', function () {
            return Addition::all();
        });

        $tags = $resolveCachedCollection('tags', function () {
            return Tag::latest()->limit(20)->get();
        });

        // $users = Cache::remember('users', 1800, function () {
        //     return User::active()->seller()->has('products')->get();
        // });

        return view('shop', compact('categories', 'wearingTimes', 'finishings', 'additions', 'tags', 'products', 'profiles'));
    }

    public function blog()
    {
        $posts = Post::latest()->where('status', 'PUBLISHED')->filter()->get();
        $categories = Postcat::all();

        return view('blog', compact('posts', 'categories'));
    }

    public function post_details($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        return view('post_details', compact('post'));
    }

    public function cart()
    {
        return view('cart');
    }

    public function checkout()
    {
        if (\Cart::isEmpty()) {
            return redirect('/shop');
        }

        $shippings = Shipping::all();

        return view('checkout', compact('shippings'));
    }

    public function thankyou()
    {
        if (!session()->has('thank')) {
            return redirect('/shop');
        }

        return view('thankyou');
    }

    public function search()
    {
        $search = request()->search;
        $products = Product::where('name', 'LIKE', "%{$search}%")
            ->where('status', 1)
            ->whereHas('user', function ($query) {
                $query->where('status', true); // Exclude paused profiles
            })
            ->limit(24)
            ->whereNull('parent_id')
            ->latest()
            ->get();

        return view('search', compact('products'));
    }

    public function contact()
    {
        return view('contact');
    }

    public function contact_store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:40'],
            'email' => ['required', 'max:100', 'email'],
            'subject' => ['required', 'max:100'],
            'message' => ['required', 'max:2000'],
        ]);
        Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return back()->with('success_msg', 'Message sent successfully');
    }

    public function page($slug)
    {
        $page = Page::where('slug', '=', $slug)->where('status', 'ACTIVE')->firstOrFail();

        return view('page', compact('page'));
    }

    public function rating(Request $request, User $user, ?Order $order = null)
    {
        if ($order) {
            $order->update([
                'is_rated' => true,
            ]);
        }
        // $rating = $user->ratings->where('user_id', auth()->id());
        // $baught = Order::where('user_id', auth()->id())
        //     ->where('status', 1)
        //     ->count();
        // if ($baught >= 1 && $rating->count() < 1) {

        Rating::create([
            'rating' => $request->rating,
            'review' => $request->comment,
            'product_id' => null,
            'user_id' => Auth()->id(),
            'vendor_id' => $user->id,
        ]);
        // } else {
        //     return back()->withErrors('Sorry! You have already send your review');
        // }

        return back()->with('success', 'Thanks for your review');
        // return back()->withErrors('Sorry! One of the items in your cart is no longer Available!');
    }

    public function userProfile(User $user, ?Order $order = null)
    {
        //   dd($user);
        // Check if user status is 0
        if ($user->verified == 0) {
            abort(403, 'This Profile is deactivited');
        }

        $avg_rating = $user->ratings->avg('rating');

        return view('user_profile', compact('user', 'avg_rating', 'order'));
    }

    public function about()
    {
        return view('about');
    }

    public function profile()
    {
        return view('profile');
    }

    public function faq()
    {
        $for_buyers = Faq::where('type', 0)->latest()->get();
        $for_sellers = Faq::where('type', 1)->latest()->get();

        return view('faq', compact('for_buyers', 'for_sellers'));
    }

    public function posts()
    {
        return view('posts');
    }

    public function nextpost($slug)
    {
        $post = Post::where('slug', $slug)->firstOrfail();
        $nextPost = Post::where('id', '>', $post->id)
            ->where('status', 'PUBLISHED')
            ->orderBy('id', 'asc')
            ->first();

        if ($nextPost) {
            return redirect()->route('post_details', $nextPost->slug);
        }

        // Handle the case when there is no next post
        return redirect()->route('blog')->withErrors('Sorry! No next post found.');
    }

    public function previouspost($slug)
    {
        $post = Post::where('slug', $slug)->firstOrfail();
        $nextPost = Post::where('id', '<', $post->id)
            ->where('status', 'PUBLISHED')
            ->orderBy('id', 'asc')
            ->first();

        if ($nextPost) {
            return redirect()->route('post_details', $nextPost->slug);
        }

        // Handle the case when there is no next post
        return redirect()->route('blog')->withErrors('Sorry! No next post found.');
    }

    public function vendors()
    {
        $vendors = User::seller()->customOrder()->filter()->paginate(20);

        return view('vendors', compact('vendors'));
    }

    public function reviews()
    {
        $vendor = User::find(request()->vendor_id);

        return view('review_modal', compact('vendor'));
    }

    public function preThankyou(Order $order)
    {
        return view('pre_thank', compact('order'));
    }

    public function newpage()
    {
        $categories = Category::whereIn('id', [12, 9, 11])->select(['id', 'name', 'slug', 'featured'])->get();

        $products = Product::with('user')
            ->whereIn('category_id', [12, 9, 11])
            ->active()
            ->filter()
            ->customOrder()
            ->paginate(48);

        $products->each(function (Product $product) use ($categories) {
            $product->setRelation('category', $categories->where('id', $product->category_id)->first());
        });

        $profiles = User::with([
            'profile',
            'products' => fn ($query) => $query->active()->latest()->limit(12),
        ])
            ->where('role_id', 3)
            ->where('boosted', true)
            ->customOrder()
            ->withAvg('ratings as avg_rating', 'rating')
            ->withCount('ratings as total_rating')
            ->paginate(8);

        $profiles->each(function (User $user) use ($categories) {
            $user->products->each(function (Product $product) use ($categories, $user) {
                $product->setRelations([
                    'category' => $categories->where('id', $product->category_id)->first(),
                    'user' => $user,
                ]);
            });
        });

        $wearingTimes = Cache::remember('wearingTimes', 1800, function () {
            return WearingTime::all();
        });

        $finishings = Cache::remember('finishings', 1800, function () {
            return Finishing::all();
        });

        $additions = Cache::remember('additions', 1800, function () {
            return Addition::all();
        });

        $tags = Cache::remember('tags', 1800, function () {
            return Tag::latest()->limit(20)->get();
        });

        // $users = Cache::remember('users', 1800, function () {
        //     return User::active()->seller()->has('products')->get();
        // });

        return view('newpage', compact('categories', 'wearingTimes', 'finishings', 'additions', 'tags', 'products', 'profiles'));
    }

    public function ageRestricted()
    {
        return view('age-restricted');
    }

    public function sitemap()
    {
        $products = Product::where('status', 1)
            ->whereHas('user', function ($query) {
                $query->where('status', true); // Exclude paused profiles
            })
            ->where('parent_id', null)
            ->get();
        $posts = Post::where('status', 'PUBLISHED')
            ->get();
        $pages = Page::where('status', 'ACTIVE')->get();
        $categories = Category::get();
        $vendors = User::where('status', 1)->seller()->get();
        $postCats = Postcat::get();

        return response()->view('sitemap', [
            'products' => $products,
            'pages' => $pages,
            'posts' => $posts,
            'vendors' => $vendors,
            'categories' => $categories,
            'postCats' => $postCats,
        ])->header('Content-Type', 'text/xml');
    }
}
