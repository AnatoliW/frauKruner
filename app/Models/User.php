<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Order;
use App\Product;
use App\Rating;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && (int) $this->role_id === 1;
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id')->withDefault();
    }
    public function method()
    {
        return $this->hasOne(Method::class);
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function profileImage()
    {

        return $this->profile ? media_url($this->profile->profile_img) : asset('images/avatar/04.png');
    }


    public function getTitleAttribute($value)
    {
       return $this->attributes['name'].' '.$this->attributes['last_name'].' (Seller)';
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }
    public function info()
    {
        return $this->hasOne(Address::class, 'user_id');
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'user_id');
    }


    public function ratings()
    {
        return $this->hasMany(Rating::class, 'vendor_id')->latest();
    }

    public function getUsernameAttribute($value)
    {
        if ($value) {
            return $value;
        } else {
            return $this->name . ' ' . $this->last_name;
        }
    }

    public function verification()
    {
        return $this->hasOne(Verification::class);
    }

    public function scopeSeller($query)
    {
        return $query->where('role_id', 3)->has('profile');
    }
    public function scopeVendor($query)
    {
        return $query->where('role_id', 3);
    }
    public function scopeCustomer($query)
    {
        return $query->where('role_id', 2);
    }
    public function reviews()
    {
        return $this->hasManyThrough(Rating::class, Product::class,);
    }
    public function scopeActive($query)
    {

        return $query->where('status', 1);
    }
    public function scopeVerified($query)
    {

        return $query->where('verified', 1);
    }
    public function getZipAttribute()
    {
        return $this->address->zip ?? '';
    }
    public function getFirstNameAttribute()
    {
        return $this->address?->first_name;
    }
    // public function getLastNameAttribute()
    // {
    //     return $this->address->last_name ;

    // }
    public function getStreetAttribute()
    {
        return $this->address?->street;
    }
    public function unshipped()
    {
        return $this->hasMany(Order::class, 'vendor_id')->whereNull('shipping_date');
    }
    // public function getVendorAttribute()
    // {
    //     return $this->seller->get() ;

    // }
    public function boosts()
    {
        return $this->morphMany(Boost::class, 'boostable');
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    public function scopeCustomOrder($query)
    {
        return $query->select('users.*')
            ->join('points', function ($join) {
                $join->on('users.id', '=', 'points.pointable_id')
                    ->where('points.pointable_type', 'App\Models\User');
            })
            // ->orderByDesc('users.status')
            ->orderByRaw("COALESCE(users.status, 0) DESC")
            ->orderByDesc('users.boosted') 
            ->orderByDesc('points.points');
    }
    public function scopeFilter($query)
    {
        return $query->when(
            request()->has('search'),

            function ($q) {
                return $q->where('name', 'LIKE', '%' . request()->search . '%')->orWhere('username', 'LIKE', '%' . request()->search . '%');
            }
        )
            ->when(
                request()->has('rating'),
                function ($q) {
                    return  $q
                        ->whereIn('user_id', function ($query) {
                            $query->select('vendor_id')
                                ->from('ratings')
                                ->where('rating', request()->rating);
                        });
                }
            )
            ->when(
                request()->short=='neuste',
                function ($q) {
                    return  $q->orderBy('id','desc');
                }
            )
            ->when(
                request()->short =='verkaeufe',
                function ($q) {
                    return  $q
                        ->wherehas('products', function ($query) {
                            $query->orderBy('sale_count','desc');
                        });
                    });
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->addPoint(0);
        });
    }
}

