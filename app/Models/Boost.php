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

    /**
     * Rechnungsnummer der Hervorhebung ("Push").
     *
     * Einzige Quelle fuer Nutzerkonto und Adminbereich, damit beide Seiten
     * immer dieselbe Nummer anzeigen. Alte Belege (vor dem Stichtag aus
     * config('app.invoice_format_cutoff_date')) behalten FKB + Zahlungs-ID,
     * alles danach bekommt FKP-<Jahr>-<ID>, z. B. FKP-2026-1787.
     */
    public function getInvoiceNumberAttribute(): string
    {
        $cutoff = Carbon::parse(config('app.invoice_format_cutoff_date', '2026-08-20'));
        $createdAt = $this->created_at ?? Carbon::now();
        $payment = $this->payment ?? $this->payments->first();

        if ($createdAt->lt($cutoff) && $payment && $payment->payment_trnx_id) {
            return 'FKB' . $payment->payment_trnx_id;
        }

        return 'FKP-' . $createdAt->format('Y') . '-' . $this->id;
    }

    public function getProductNameAttribute()
    {
        return $this->boostable ? $this->boostable->title :'';
    }

    public function getPaymentStatusAttribute()
    {
        // Pushs aus dem Adminbereich sind kostenlos und haben keine Zahlung.
        return $this->payment?->statusTranslated ?? 'KOSTENLOS';
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


    /**
     * Kostenloser Push aus dem Adminbereich.
     *
     * Der Adminbereich pusht immer gratis: Es wird keine Zahlung angelegt, der
     * Preis ist 0 und der Push startet sofort. Eine Rechnung entsteht dadurch
     * bewusst nicht - siehe scopePaidOrFree() und getInvoiceNumberAttribute().
     */
    public static function freeAdminPush(Model $boostable, Package $package, ?int $adminId = null): self
    {
        $boost = $boostable->boosts()->create([
            'package_id' => $package->id,
            'user_id' => $adminId ?? auth()->id(),
            'price' => 0,
            'base_price' => 0,
            'tax' => 0,
            'start_day' => Carbon::now(),
            'end_day' => Carbon::now()->addDays($package->days),
        ]);

        $boost->process();

        return $boost;
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
        // In der Datenbank steht 'PAID'; die Schreibweise nicht voraussetzen.
        return  $q->whereIn('status', ['PAID', 'paid']);
        });
    }

    /**
     * Alles, was im Adminbereich sichtbar sein soll: bezahlte Pushs und die
     * kostenlosen Pushs aus dem Adminbereich, zu denen es keine Zahlung gibt.
     */
    public function scopePaidOrFree($query)
    {
        $query->where(function ($q) {
            $q->whereHas('payment', function ($payment) {
                $payment->whereIn('status', ['PAID', 'paid']);
            })->orWhereDoesntHave('payment');
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
