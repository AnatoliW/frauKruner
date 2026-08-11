<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;


class Coupon extends Model
{
    public const REJECT_EXPIRED = 'expired';

    public const REJECT_USED_UP = 'used_up';

    public const REJECT_EMPTY_CART = 'empty_cart';

    public const REJECT_MINIMUM_CART = 'minimum_cart';

    public const REJECT_COVERS_CART = 'covers_cart';

    protected $fillable = [
        'code',
        'discount',
        'expire_at',
        'limit',
        'minimum_cart',
        'used',
    ];

    protected function casts(): array
    {
        return [
            'expire_at' => 'date',
            'discount' => 'float',
            'minimum_cart' => 'float',
            'limit' => 'integer',
            'used' => 'integer',
        ];
    }

    /**
     * Codes werden beim Schreiben getrimmt, damit ein versehentliches
     * Leerzeichen im Backend nicht zu einem Code führt, den niemand eintippen
     * kann. Im Model statt im Formular, damit es für jeden Schreibpfad gilt.
     */
    protected function code(): Attribute
    {
        return Attribute::set(fn ($value) => trim((string) $value));
    }

    /**
     * Die Collation der Spalte (utf8mb4_unicode_ci) vergleicht bereits ohne
     * Rücksicht auf Groß-/Kleinschreibung. Ein LOWER() um die Spalte würde den
     * Unique-Index coupons_code_unique unbenutzbar machen.
     *
     * ACHTUNG: Diese Unempfindlichkeit ist eine Eigenschaft der Collation, nicht
     * des Codes hier – die Tests laufen auf SQLite und vergleichen dort
     * case-sensitiv, können sie also nicht absichern. Wird die Spalte auf eine
     * _bin-Collation umgestellt oder die Datenbank gewechselt, fällt "sommer"
     * für "SOMMER" still durch, ohne dass ein Test rot wird.
     */
    public function scopeCode(Builder $query, $code)
    {
        return $query->where('code', trim((string) $code));
    }

    public function isExpired()
    {
        return $this->expire_at && $this->expire_at->copy()->endOfDay()->isPast();
    }

    public function isUsedUp()
    {
        return $this->used >= $this->limit;
    }

    /**
     * Grund, warum der Gutschein auf diesen Warenkorb nicht passt – oder null,
     * wenn er passt. Gibt bewusst einen Reason-Code und keinen fertigen Satz
     * zurück: Die Formulierung gehört in die Präsentationsschicht, siehe
     * App\Support\CouponMessages.
     *
     * @return string|null einer der REJECT_*-Codes
     */
    public function rejectionFor($subtotal)
    {
        if ($this->isExpired()) {
            return static::REJECT_EXPIRED;
        }

        if ($this->isUsedUp()) {
            return static::REJECT_USED_UP;
        }

        if ($subtotal <= 0) {
            return static::REJECT_EMPTY_CART;
        }

        if ($subtotal < $this->minimum_cart) {
            return static::REJECT_MINIMUM_CART;
        }

        // Deckt der Rabatt den gesamten Warenkorb, bliebe eine Bestellung über
        // 0 € übrig. Stripe und PayPal lehnen einen Betrag von 0 ab, der Kunde
        // liefe erst im Bezahlschritt in einen Gateway-Fehler. Lieber hier mit
        // einer verständlichen Meldung abweisen.
        if ($subtotal <= $this->discount) {
            return static::REJECT_COVERS_CART;
        }

        return null;
    }

    /**
     * Rabatt für diesen Warenkorb – nie mehr als der Warenkorbwert selbst.
     *
     * rejectionFor() weist einen Gutschein, der den Warenkorb komplett deckt,
     * bereits ab; die Deckelung bleibt trotzdem als zweite Absicherung, damit
     * kein Aufrufer versehentlich einen negativen Gesamtbetrag erzeugt.
     */
    public function discountForCart($subtotal)
    {
        return round(min($this->discount, max(0, $subtotal)), 2);
    }

    /**
     * Bucht eine Einlösung atomar. Gibt false zurück, wenn das Limit
     * zwischenzeitlich erreicht wurde.
     */
    public function redeem()
    {
        return (bool) static::query()
            ->whereKey($this->getKey())
            ->whereColumn('used', '<', 'limit')
            ->increment('used');
    }
}
