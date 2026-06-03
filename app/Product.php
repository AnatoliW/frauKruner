<?php

namespace App;

use App\Models\Boost;
use App\Models\Favorite;
use App\Models\Image;
use App\Models\Traits\HasPoint;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Product extends Model
{
    use HasPoint;
    use SoftDeletes;

    protected $guarded = [];
    protected $dates = ['deleted_at'];

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value * 100;
    }

    public function getPriceAttribute($value)
    {
        return $value / 100 ?? 0;
    }

    public function price()
    {
        return ($this->price ?? 0) + ($this->shipping_cost ?? 0);
    }

    public function getImageAttribute($value)
    {
        if ($value) {
            return str_replace('\\', '/', $value);
        }
    }

    public function getStatusAttribute($value)
    {
        return $this->user->status && $this->attributes['status'];
    }

    public function getCommissionAttribute()
    {
        if (isset($this->user->commission)) {
            $inputVal = $this->user->commission;
        } else {
            $inputVal = setting('finance.commission');
        }

        if ($inputVal > 0) {
            switch (setting('finance.commission_type')) {
                case 'percentage':
                    $commission = $this->price() * ($inputVal / 100);
                    break;

                default:
                    $commission = $inputVal;
                    break;
            }
        } else {
            $commission = 0;
        }

        return $commission;
    }

    public function getVatAttribute()
    {
        $total = $this->commission;
        if (setting('finance.vat')) {
            $vat = $total * (setting('finance.vat') / 100);
        } else {
            $vat = 0;
        }

        return $vat;
    }

    public function getTotalPriceAttribute()
    {
        $total = $this->price() + $this->commission + $this->vat;

        return $total;
    }

    public function getFinishingsAttribute($value)
    {
        return (array) json_decode($value);
    }

    public function getAdditionAttribute($value)
    {
        return (array) json_decode($value);
    }

    public function getWearingTimeAttribute($value)
    {
        return (array) json_decode($value);
    }

    public function getThumbnailAttribute()
    {
        return media_url($this->image);
        // return 'http://placekitten.com/200/300';
    }

    public function setSalePriceAttribute($value)
    {
        $this->attributes['saleprice'] = $value * 100;
    }

    public function getSalePriceAttribute($value)
    {
        if ($value) {
            $price = $value / 100;

            return sprintf('%.2f', $price);
        }
    }

    public function getTitleAttribute($value)
    {
        return $this->attributes['name'].' (Product)';
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->withDefault();
    }

    public function brands()
    {
        return $this->belongsToMany('App\Brand')->withTimestamps()->withDefault();
    }

    public function ratings()
    {
        return $this->hasMany('App\Rating')->latest();
    }

    public function getVariationAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        }
    }

    public function scopePublished($query)
    {
        return $query->where('status', 1);
    }

    public function path()
    {
        return $this->slug ? route('product', $this->slug) : '#';
    }

    public function scopeOwn($query)
    {
        return $query->where('user_id', Auth::id());
    }

    public function scopeProductActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeQuantity($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function scopeActive($query)
    {
        return $query->whereHas('user', function ($query) {
            $query->where('verified', true)
                  ->where('visibiliti_status', true)
                  ->where('status', true);
        });
    }

    public function scopeVerified($query)
    {
        return $query->whereHas('user', function ($query) {
            $query->where('verified', true)
                  ->where('status', true);
        });
    }

    public function scopeVisibility($query)
    {
        return $query->whereHas('user', function ($query) {
            $query->where('visibiliti_status', true)
                  ->where('status', true);
        });
    }

    public function scopeFilter($query)
    {
        return $query->when(
            request()->has('search'),
            function ($q) {
                return $q->where('products.name', 'LIKE', '%'.request()->search.'%')
                    ->orWhere('products.details', 'LIKE', '%'.request()->search.'%')
                    ->orWhere('products.description', 'LIKE', '%'.request()->search.'%');
            }
        )
            ->when(
                request()->has('seller'),
                function ($q) {
                    return $q->whereHas('user', function ($query) {
                        $query->where('id', request()->seller);
                    });
                }
            )

            // ->when(
            //     request()->has('rating'),
            //     function ($q) {
            //         return  $q->whereHas('ratings', function ($q) {
            //             $q->where('rating',request()->rating);
            //         });
            //     }
            // )
            // ->when(
            //     request()->has('rating'),
            //     function ($q) {
            //         return  $q->whereHas('ratings', function ($q) {
            //             $q->groupBy('vendor_id', 'id')->havingRaw('AVG(ratings.rating)  = ?', [request()->rating]);
            //         });
            //     }
            // )
            ->when(
                request()->has('category'),
                function ($q) {
                    $categories = explode(',', request()->category);
                    foreach ($categories as $category) {
                        $q = $q->whereHas('category', function ($query) use ($category, $categories) {
                            if (count($categories) > 1) {
                                $query->orWhere('name', $category);
                            } else {
                                $query->where('name', $category);
                            }
                        });
                    }

                    return $q;
                }
            )
            ->when(
                request()->has('time'),
                function ($q) {
                    $times = explode(',', request()->time);
                    foreach ($times as $time) {
                        $q = $q->where('wearing_time', 'LIKE', '%'.$time.'%');
                    }

                    return $q;
                }
            )
            ->when(
                request()->has('additions'),
                function ($q) {
                    $additions = explode(',', request()->additions);
                    foreach ($additions as $addition) {
                        $q = $q->where('addition', 'LIKE', '%'.$addition.'%');
                    }

                    return $q;
                }
            )
            ->when(
                request()->has('finishings'),
                function ($q) {
                    $finishings = explode(',', request()->finishings);
                    foreach ($finishings as $finishing) {
                        $q = $q->where('finishings', 'LIKE', '%'.$finishing.'%');
                    }

                    return $q;
                }
            )
            ->when(
                request()->has('tag'),
                function ($q) {
                    return $q->where('tags', 'LIKE', '%'.request()->tag.'%');
                }
            )
            ->when(
                request('orderBy') == 'preis',
                function ($q) {
                    return $q->orderBy('price', 'desc');
                }
            )
            ->when(
                request('orderBy') == 'verkaeufe',
                function ($q) {
                    return $q->orderBy('sale_count', 'desc');
                }
            )
            ->when(
                request('orderBy') == 'neuste',
                function ($q) {
                    return $q->orderBy('id', 'desc');
                }
            )
            ->when(
                request('orderBy') == 'bewertung',
                function ($q) {
                    return $q->whereHas('ratings', function ($q) {
                        $q->groupBy('product_id', 'id');
                    });
                }
            )
            ->when(
                request()->has('rating'),
                function ($q) {
                    return $q
                        ->whereIn('user_id', function ($query) {
                            $query->select('vendor_id')
                                ->from('ratings')
                                ->where('rating', request()->rating);
                        });
                }
            );
    }

    public function scopeOrder($query)
    {
        switch (request()->orderBy) {
            case 'bewertung': // rating
                return $query->withAvg('ratings', 'rating')->orderBy('ratings_avg_rating', 'desc');
            case 'preis': // price
                return $query->orderBy('price', 'desc');
            case 'neuste': // date
                return $query->latest();
            case 'verkaeufe': // sales
                return $query->orderBy('sale_count', 'desc');
            default:
                return $query;
        }
    }

    public function boosts()
    {
        return $this->morphMany(Boost::class, 'boostable')->latest();
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function scopeCustomOrder($query)
    {
        return $query->select('products.*')
            ->join('points', function ($join) {
                $join->on('products.id', '=', 'points.pointable_id')
                    ->where('points.pointable_type', 'App\Product');
            })
            ->join('users', 'products.user_id', '=', 'users.id')
            ->orderByDesc('users.status')
            ->orderByDesc('products.status')
            ->orderByDesc('products.boosted')
            ->orderByDesc('points.points')
            ->orderByDesc('products.created_at');
    }

    protected static function booted(): void
    {
        static::created(function (Product $product) {
            $product->addPoint(0);
        });
    }
}

