<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Product;

class Category extends Model
{
    public function scopeFeatured($query)
    {
      return $query->where('featured', 1);
    }
    public function products()
    {
      return $this->hasMany(Product::class)->inRandomOrder();
    }
}
