<?php

namespace App\Support;

use App\Coupon;

/**
 * Hält den angewendeten Gutschein in der Session und – wichtiger – hält ihn
 * aktuell.
 *
 * Der Rabattbetrag liegt in der Session, weil \Cart::getTotal() und
 * \Shop::discount() ihn dort lesen. Er wurde bisher aber nur beim Anwenden
 * berechnet: Wer danach Artikel aus dem Warenkorb nahm, sah weiter den alten
 * Rabatt, obwohl Mindestbestellwert oder Ablaufdatum längst nicht mehr passten.
 * revalidate() rechnet deshalb bei jedem Seitenaufruf gegen den aktuellen
 * Warenkorb nach, statt sich auf den Stand vom Zeitpunkt der Eingabe zu
 * verlassen.
 */
class CouponSession
{
    public const DISCOUNT_KEY = 'discount';

    public const CODE_KEY = 'discount_code';

    public static function code(): ?string
    {
        return session()->get(static::CODE_KEY);
    }

    public static function discount(): float
    {
        return (float) (session()->get(static::DISCOUNT_KEY) ?? 0);
    }

    public static function apply(Coupon $coupon, float $subtotal): void
    {
        session()->put(static::DISCOUNT_KEY, $coupon->discountForCart($subtotal));
        session()->put(static::CODE_KEY, $coupon->code);
    }

    public static function forget(): void
    {
        session()->forget(static::DISCOUNT_KEY);
        session()->forget(static::CODE_KEY);
    }

    /**
     * Prüft den hinterlegten Gutschein gegen den aktuellen Warenkorb und
     * schreibt den Rabatt neu in die Session.
     *
     * @return string|null Meldung, wenn sich der Gesamtbetrag dadurch ändert –
     *                     weil der Gutschein entfernt wurde oder weil er
     *                     inzwischen einen anderen Betrag wert ist
     */
    public static function revalidate(): ?string
    {
        $code = static::code();

        if (! $code) {
            // Ein Rabatt ohne Code lässt sich nicht nachprüfen – der Betrag
            // stammt dann aus einer Alt-Session und wird verworfen.
            session()->forget(static::DISCOUNT_KEY);

            return null;
        }

        // Vor dem Neuberechnen merken: nur so lässt sich hinterher sagen, ob
        // sich der angezeigte Betrag verändert hat.
        $hadDiscount = session()->has(static::DISCOUNT_KEY);
        $previous = static::discount();

        $coupon = Coupon::query()->code($code)->first();

        if (! $coupon) {
            static::forget();

            return CouponMessages::removed(CouponMessages::noLongerValid());
        }

        $subtotal = (float) \Cart::getSubTotal();

        if ($reason = $coupon->rejectionFor($subtotal)) {
            static::forget();

            return CouponMessages::removed(CouponMessages::forRejection($coupon, $reason));
        }

        static::apply($coupon, $subtotal);

        $current = static::discount();

        // Der Gutschein gilt weiter, ist aber einen anderen Betrag wert – etwa
        // weil jemand ihn im Backend geändert hat, während der Warenkorb offen
        // war. Ohne diesen Hinweis würde die Bestellung stillschweigend zu
        // einem anderen Preis angelegt als dem, den die Kundin gesehen hat.
        // Verglichen wird in ganzen Cent, damit Float-Reste keine Meldung
        // auslösen.
        if ($hadDiscount && static::cents($previous) !== static::cents($current)) {
            return CouponMessages::amountChanged($previous, $current);
        }

        return null;
    }

    private static function cents(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
