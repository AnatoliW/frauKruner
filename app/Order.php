<?php

namespace App;

use Closure;
use App\Mail\UserOrderEmail;
use App\Mail\VendorOrderEmail;
use App\Models\Orderimage;
use App\Models\Traits\HasMeta;
use App\Coupon;
use App\Services\ProductStock;
use Illuminate\Database\Eloquent\Model;
use App\Product;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
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
            'coupon_redeemed_at' => 'datetime',
            'confirmed_at' => 'datetime',
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

    /**
     * Markiert die Bestellung als bezahlt und bucht erst dann das Produkt ab.
     *
     * Gibt false zurück, wenn die Bestellung bereits als bezahlt markiert war,
     * damit Bestand sowie Käufer- und Verkäufer-Mail nicht doppelt ausgelöst werden.
     *
     * Das Setzen ist bewusst ein bedingtes UPDATE und keine Prüfung mit
     * anschließendem Schreiben: Zwei gleichzeitige Aufrufe – zwei Zustellungen
     * derselben Micropayment-Benachrichtigung, zwei Klicks auf „als bezahlt
     * markieren“ im Admin – könnten sonst beide die Prüfung passieren, bevor
     * einer schreibt. Folge wären doppelte Mails und eine doppelt abgebuchte
     * Menge. So gewinnt genau einer, und nur der arbeitet weiter.
     *
     * Ein leerer payment_status zählt als unbezahlt; die Spalte ist in der
     * Datenbank nullable.
     */
    public function markAsPaid(): bool
    {
        $claimed = static::query()
            ->whereKey($this->getKey())
            ->where(function ($query) {
                $query->where('payment_status', '!=', 1)
                    ->orWhereNull('payment_status');
            })
            ->update([
                'payment_status' => 1,
                'status' => 1,
            ]);

        if (! $claimed) {
            return false;
        }

        // Der Datensatz wurde an der Instanz vorbei geschrieben, deshalb hier
        // nachziehen – nachfolgender Code und Aufrufer lesen die Bestellung.
        $this->refresh();

        $this->parent?->update([
            'payment_status' => 1,
            'status' => 1,
        ]);

        // Der Gutschein wird hier bewusst nicht gebucht: Das passiert bereits
        // beim Abschluss der Bestellung, siehe CheckoutController::processPayment().
        // Ein Aufruf an dieser Stelle würde eine Vorkasse-Bestellung, die noch
        // unter der alten Regel angelegt wurde, ein zweites Mal zählen.
        ProductStock::bookSale($this);

        // Der Käufer bekommt jetzt dieselbe Bestellbestätigung wie bei PayPal/Stripe –
        // bei Vorkasse aber erst nach dem Zahlungseingang.
        $this->sendPaymentMail($this->email, fn () => new UserOrderEmail($this));
        $this->sendPaymentMail($this->vendor->email, fn () => new VendorOrderEmail($this));

        return true;
    }

    /**
     * Verschickt eine Mail zum Zahlungseingang, ohne die Zahlung zu gefährden.
     *
     * Der Versand läuft synchron und mitten in einer fremden Anfrage: Bei der
     * Online-Überweisung ruft der Micropayment-Server dafür
     * MicropaymentController::notify() auf und wartet auf die Antwort. Eine
     * durchgereichte Ausnahme hätte dort zwei Folgen, und beide treffen eine
     * Zahlung, die längst gebucht ist:
     *
     *  - Micropayment bekäme `status=error` statt `status=ok` und zeigte der
     *    Kundin „Die Weiterleitung wurde nicht erlaubt“, obwohl sie bezahlt hat.
     *  - Die erneute Zustellung der Benachrichtigung verliert das bedingte
     *    UPDATE am Anfang von markAsPaid(), bricht mit false ab und kommt hier
     *    gar nicht mehr an. Die Bestätigung wäre endgültig verloren – kein
     *    Wiederholungsversuch erreicht sie noch.
     *
     * Ein gescheiterter Versand darf deshalb weder den Ablauf abbrechen noch
     * die jeweils andere Mail verhindern. Er wird mit Ausnahme protokolliert,
     * damit die Bestätigung von Hand nachgeholt werden kann; Bestellnummer und
     * Empfänger stehen dabei.
     *
     * Dasselbe gilt für den Knopf „als bezahlt markieren“ im Adminbereich: Der
     * Zahlungseingang ist dort ebenso gebucht, bevor die erste Mail rausgeht.
     *
     * @param  Closure(): \Illuminate\Mail\Mailable  $mailable  Wird erst
     *         innerhalb der Absicherung gebaut, damit auch ein Fehler beim
     *         Erzeugen der Nachricht die Zahlung nicht mitreißt.
     */
    private function sendPaymentMail(?string $recipient, Closure $mailable): void
    {
        if (blank($recipient)) {
            return;
        }

        try {
            Mail::to($recipient)->send($mailable());
        } catch (\Throwable $e) {
            try {
                // Zuerst die Zeile, nach der im Betrieb gesucht wird: Sie benennt
                // die Bestellung, deren Bestaetigung fehlt, und den Empfaenger, an
                // den sie von Hand nachzuholen ist.
                logger()->error('Bestellbestätigung konnte nicht verschickt werden', [
                    'order_id' => $this->getKey(),
                    'recipient' => $recipient,
                ]);

                // report() statt logger()->error($e): Die Ausnahme laeuft damit
                // durch den Fehler-Handler der Anwendung und erreicht mitsamt
                // Ablaufverfolgung auch eine spaeter angebundene Ueberwachung,
                // nicht nur storage/logs/laravel.log.
                report($e);
            } catch (\Throwable) {
                // Auch das Protokollieren darf die Zahlung nicht mitreissen.
                // report() geht durch den Fehler-Handler und wirft seinerseits,
                // wenn sich kein Protokoll schreiben laesst - bei nicht
                // beschreibbarem storage/logs also genau dann, wenn frisch
                // ausgerollt wurde. Die Ausnahme kaeme bis in notify() zurueck,
                // und die Zahlung ginge als `status=error` zurueck. Eine
                // verlorene Protokollzeile ist das kleinere Uebel als eine
                // Bestaetigung, die keine erneute Zustellung je wieder erreicht.
                //
                // Vorbild: MicropaymentController::log() sichert den Eintrag in
                // die Tabelle `logs` aus demselben Grund ab.
            }
        }
    }


    /**
     * Die Bestellung, an der der Gutschein hängt.
     *
     * Der volle Rabatt steht auf der Hauptbestellung; Unterbestellungen tragen
     * nur ihren Anteil. Gebucht werden darf deshalb ausschließlich oben, sonst
     * zählt jede Position einzeln als Einlösung.
     */
    public function couponOrder(): Order
    {
        return $this->parent ?: $this;
    }

    /**
     * Bucht die Einlösung des Gutscheins dieser Bestellung – genau einmal.
     *
     * Aufgerufen wird das beim Abschluss der Bestellung, also sobald die Kundin
     * die Zahlungsart bestätigt hat. Nicht erst beim Zahlungseingang: Ein
     * Gutschein ist mit der verbindlichen Bestellung genutzt, auch wenn eine
     * Vorkasse-Rechnung am Ende offen bleibt.
     *
     * coupon_redeemed_at wird atomar gesetzt: Nur der Aufruf, der die Spalte
     * von NULL auf jetzt dreht, bucht auch den Zähler hoch. Damit bleibt es bei
     * einer Einlösung, egal wie oft die Methode aufgerufen wird (mehrere
     * Unterbestellungen, doppeltes Absenden, Wiederholung nach Abbruch).
     */
    public function redeemCoupon(): void
    {
        if (! $this->discount_code || $this->discount <= 0) {
            return;
        }

        $claimed = static::query()
            ->whereKey($this->getKey())
            ->whereNull('coupon_redeemed_at')
            ->update(['coupon_redeemed_at' => now()]);

        if (! $claimed) {
            return;
        }

        $coupon = Coupon::query()->code($this->discount_code)->first();

        if (! $coupon || ! $coupon->redeem()) {
            // Das Limit wurde zwischen Bestellung und Zahlung ausgeschöpft. Die
            // Bestellung bleibt zum angezeigten Preis bestehen – nachträglich
            // teurer machen ist keine Option. Nur protokollieren.
            logger()->warning('Gutschein konnte nach der Zahlung nicht gebucht werden', [
                'order_id' => $this->getKey(),
                'discount_code' => $this->discount_code,
            ]);
        }
    }

    /**
     * Beansprucht die einmalige Bestätigung dieser Bestellung.
     *
     * Gibt nur beim ersten Aufruf true zurück. Alles, was genau einmal pro
     * Bestellung passieren darf – Unterbestellungen fortschreiben, Mails
     * verschicken, Gutschein buchen – gehört hinter diese Prüfung.
     */
    public function claimConfirmation(): bool
    {
        $claimed = static::query()
            ->whereKey($this->getKey())
            ->whereNull('confirmed_at')
            ->update(['confirmed_at' => now()]);

        return (bool) $claimed;
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
            'f_name' => static::firstFilled($vendor->first_name, $vendor->name, $vendor->verification?->name),
            'l_name' => static::firstFilled($vendor->last_name, $vendor->address?->last_name, $vendor->verification?->last_name),
            'street' => static::firstFilled($vendor->street, $vendor->address?->street, $vendor->verification?->street),
            'house_no' => static::firstFilled($vendor->house_no, $vendor->address?->house_no, $vendor->verification?->house_no),
            'zip' => static::firstFilled($vendor->zip, $vendor->address?->zip, $vendor->verification?->zip),
            'federal_state' => static::firstFilled($vendor->federal_state, $vendor->address?->federal_state, $vendor->verification?->city),
            'email' => $vendor->email,
            'vat_number' => $vendor->vat ?? null,
            'is_pay_vat' => (int) ($vendor->is_pay_vat ?? 0),
            'vat_perchatage' => (float) (setting('finance.vat') ?: 19),
        ];
    }

    /**
     * Liefert den ersten Wert, der weder null noch ein leerer String ist.
     *
     * Ersetzt die bisherigen ??-Ketten: ?? greift nur bei null, sodass ein
     * leerer String aus der Datenbank die Fallback-Kette abbrechen liess.
     */
    public static function firstFilled(...$values): ?string
    {
        foreach ($values as $value) {
            if (filled($value)) {
                return (string) $value;
            }
        }

        return null;
    }
    
    public function setSellerInfoAttribute($value)
    {
        $this->attributes['seller_info'] = json_encode($value);
    }
}
