<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Product;

class Rating extends Model
{
    protected $fillable = ['name', 'email', 'review', 'rating', 'product_id', 'user_id', 'vendor_id'];

    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault();
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id')->withDefault();
    }
    public function vendor()
    {
        return $this->belongsTo('App\Models\User', 'vendor_id')->withDefault();
    }

    protected static function booted(): void
    {
        static::created(function (Rating $rating) {
            // $rating->product->addPoint($rating->rating);
            $rating->vendor->addPoint($rating->rating);
        });
    }

    
}
