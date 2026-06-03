<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Package extends Model
{
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value * 100;
    }
    public function getPriceWithTaxAttribute()
    {
        $value = $this->price;
        $price =  $value + ($value * (setting('finance.vat') / 100));
        return $price ?? 0;
    }

    public function getPriceAttribute($value)
    {


        return $value / 100 ?? 0;
    }
}
