<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Product;

class Category extends Model
{
    protected $guarded = [];

    public function scopeFeatured($query)
    {
      return $query->where('featured', 1);
    }
    public function products()
    {
      return $this->hasMany(Product::class)->inRandomOrder();
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
