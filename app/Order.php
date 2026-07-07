<?php

namespace App;

use App\Models\Orderimage;
use App\Models\Traits\HasMeta;
use Illuminate\Database\Eloquent\Model;
use App\Product;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\User;
use Carbon\Carbon;

class Order extends Model
{
    use HasMeta;
    protected $guarded = [];

    protected $meta_attributes = [
        "payment_prove",
   
    ];

    protected function casts(): array
    {
        return [
            'shipping_date' => 'datetime',
        ];
    }


    public function user()
    {
        return $this->belongsTo('App\Models\User')->withDefault();
    }
    public function vendor()
    {
        return $this->belongsTo('App\Models\User', 'vendor_id')->withDefault();
    }
    public function address()
    {
        return $this->belongsTo('App\Models\Address')->withDefault();
    }

    public function products()
    {
        return $this->belongsToMany('App\Product')->withPivot('quantity', 'price', 'variation');
    }
    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault();
    }


    public function orderProduct()
    {
        return $this->hasMany(OrderProduct::class);
    }
    public function orderimages()
    {
        return $this->hasMany(Orderimage::class);
    }

    public function scopeOwn($query)
    {
        if (auth()->user()->role_id = 3) {
            return $query->where('vendor_id', auth()->id());
        } else {
            return $query->where('user_id', auth()->id());
        }
    }
    public function scopeFilter($query)
    {
      
        $items = explode(' ', request()->search);

        return $query->when(request()->has('search'), function ($q) use ($items) {
            $q->whereHas('vendor', function ($q) use ($items) {

                foreach ($items as $item) {

                    return $q->where('name', 'LIKE', '%' . $item . '%')->orWhere('last_name', 'LIKE', '%' . $item . '%')->orWhere('id',request()->search);
                }
            })
                ->orWhere(function ($q) use ($items) {
                    foreach ($items as $data) {
                        $q->orWhere('first_name', 'LIKE', '%' . $data . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $data . '%');
                    }
                })
                ->orWhere('email', request()->search)
                ->orWhere('id', request()->search)
                ->orWhere('parent_id', request()->search)
                ->orWhere('tracking_Id','LIKE', '%'. request()->search. '%')
                ->orWhereHas('user', function ($q) {
                    $q->where('username', request()->search)->orWhere('id',request()->search);
                });
        });
    }



    // public function getShippingDateAttribute($value)
    // {
    //     return Carbon::parse($value)->format('d.M.Y');
    // }
    public function getAdditionAttribute($value)
    {
        $datas = json_decode($value);

        if ($datas) {
            return $datas;
        } else {
            return $datas = [];
        }
    }
    public function getFinishingsAttribute($value)
    {
        $datas = json_decode($value);

        if ($datas) {
            return $datas;
        } else {
            return $datas = [];
        }
    }
    public function getWearingTimeAttribute($value)
    {
        $datas = json_decode($value);

        if ($datas) {
            return $datas;
        } else {
            return $datas = [];
        }
    }

    public function childrens()
    {
        return $this->hasMany(Order::class, 'parent_id');
    }
    public function parent()
    {
        return $this->belongsTo(Order::class, 'parent_id');
    }



    public function scopePaid($query)
    {
        return $query->where('payment_status', 1);
    }


    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }
    public function scopeActive($query)
    {
       
        return $query->where('status', 1);
    }
    public function getSellerInfoAttribute($value)
    {
        $info = json_decode($value ?? '');

        if (filled($value) && $info) {
            return $info;
        }

        return $this->resolveSellerInfo();
    }

    public function resolveSellerInfo(): object
    {
        $vendor = $this->relationLoaded('vendor')
            ? $this->vendor
            : $this->vendor()->with(['address', 'verification'])->first();

        if (! $vendor) {
            return (object) [
                'f_name' => null,
                'l_name' => null,
                'street' => null,
                'house_no' => null,
                'zip' => null,
                'federal_state' => null,
                'email' => null,
                'vat_number' => null,
                'is_pay_vat' => 0,
                'vat_perchatage' => (float) (setting('finance.vat') ?: 19),
            ];
        }

        return (object) [
            'f_name' => $vendor->first_name ?? $vendor->name ?? $vendor->verification?->name,
            'l_name' => $vendor->last_name ?? $vendor->address?->last_name ?? $vendor->verification?->last_name,
            'street' => $vendor->street ?? $vendor->address?->street ?? $vendor->verification?->street,
            'house_no' => $vendor->house_no ?? $vendor->address?->house_no ?? $vendor->verification?->house_no,
            'zip' => $vendor->zip ?? $vendor->address?->zip ?? $vendor->verification?->zip,
            'federal_state' => $vendor->federal_state ?? $vendor->address?->federal_state ?? $vendor->verification?->city,
            'email' => $vendor->email,
            'vat_number' => $vendor->vat ?? null,
            'is_pay_vat' => (int) ($vendor->is_pay_vat ?? 0),
            'vat_perchatage' => (float) (setting('finance.vat') ?: 19),
        ];
    }
    
    public function setSellerInfoAttribute($value)
    {
        $this->attributes['seller_info'] = json_encode($value);
    }
}
