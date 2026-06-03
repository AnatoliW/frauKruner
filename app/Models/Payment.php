<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function payable()
    {
        return $this->morphTo();
    }
    public function getAmountAttribute($value)
    {
        return $value / 100;
    }
    public function getTaxAttribute($value)
    {
        return $value / 100;
    }
    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = $value * 100;
    }
    public function setTaxAttribute($value)
    {
        $this->attributes['tax'] = $value * 100;
    }
    public function getStatusTranslatedAttribute()
    {

        if ($this->attributes['status'] == 'PAID') {
            return 'BEZAHLT';
        }
        if ($this->attributes['status'] == 'PENDING') {
            return  'OFFEN';
        }
    }
}
