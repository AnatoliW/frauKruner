<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Payable;
use App\Package;
use Carbon\Carbon;

class Boost extends Model
{
    use HasFactory, Payable;
    protected $guarded = [];
    protected $casts = [
        'start_day' => 'datetime',
        'end_day' => 'datetime',
    ];

    public $additional_attributes = ['product_name', 'payment_status'];

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value * 100;
    }

    public function setBasePriceAttribute($value)
    {
        $this->attributes['base_price'] = $value * 100;
    }

    public function getBasePriceAttribute($value)
    {
        if ($value === null) {
            return $this->package?->price ?? 0;
        }
        return $value / 100 ?? 0;
    }
    public function setTaxAttribute($value)
    {
        $this->attributes['tax'] = $value * 100;
    }
    public function getTaxAttribute($value)
    {
        return $value / 100 ?? 0;
    }

    public function getProductNameAttribute()
    {
        return $this->boostable ? $this->boostable->title :'';
    }

    public function getPaymentStatusAttribute()
    {
        return $this->payment->statusTranslated;
    }

    public function getPriceAttribute($value)
    {

        return $value / 100 ?? 0;
    }

    public function boostable()
    {
        return $this->morphTo();
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function process()
    {
        $this->status = 1;
        $this->save();
        return $this->boostable->update([
            'boosted' => 1,
            'boost_start_date' => Carbon::now(),
            'boost_end_date' => Carbon::now()->addDays($this->package->days),
        ]);
    }

    public function end()
    {
        $this->status = 0;
        $this->save();
        return $this->boostable->update([
            'boosted' => 0,
            'boost_start_date' => null,
            'boost_end_date' => null,
        ]);
    }
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
    public function scopePaid($query)
    {
       $query->whereHas('payment', function ($q) {
        return  $q->where('status', 'paid');
        });
    }
    public function scopeFilter($query)
    {
        return $query->when(
            request()->has('search'),
            function ($q) {
                return $q->where('user_id',  request()->search )
                    ->orWhereHas('user',function ($query){
                        $query->where('name', 'LIKE', '%' . request()->search . '%')->orWhere('last_name', 'LIKE', '%' . request()->search . '%');
                    })
                    ->orWhereHas('boostable', function ($query) {
                        $query->where('name', 'LIKE', '%' . request()->search . '%');
                    });
            }
        );
           
    }
    public function getUserInfoAttribute($value)
    {
        return json_decode($value);
    }
    
    public function setUserInfoAttribute($value)
    {
        $this->attributes['user_info'] = json_encode($value);
    }
}
