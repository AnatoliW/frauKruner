<?php

namespace App\Support;

use App\Coupon;

/**
 * Übersetzt die Reason-Codes aus Coupon::rejectionFor() in Sätze für Kundinnen
 * und Kunden. Bewusst getrennt vom Model: Das Model soll nicht wissen, wie
 * Beträge formatiert werden oder in welcher Sprache der Shop antwortet.
 */
class CouponMessages
{
    public static function forRejection(Coupon $coupon, string $reason): string
    {
        return match ($reason) {
            Coupon::REJECT_EXPIRED => 'Dieser Gutschein ist abgelaufen.',
            Coupon::REJECT_USED_UP => 'Dieser Gutschein wurde bereits vollständig eingelöst.',
            Coupon::REJECT_EMPTY_CART => 'Ihr Warenkorb ist leer.',
            Coupon::REJECT_MINIMUM_CART => 'Mindesteinkaufswert erforderlich, um diesen Gutschein zu verwenden: '
                .\Shop::price($coupon->minimum_cart).'.',
            Coupon::REJECT_COVERS_CART => 'Dieser Gutschein deckt Ihren gesamten Warenkorb ab. '
                .'Bitte legen Sie weitere Artikel dazu, damit ein Restbetrag zu zahlen bleibt.',
            default => 'Dieser Gutschein kann nicht verwendet werden.',
        };
    }

    /**
     * Zusatz für den Fall, dass ein bereits angewendeter Gutschein nachträglich
     * entfernt werden musste – der angezeigte Gesamtbetrag ändert sich dadurch.
     */
    public static function removed(string $message): string
    {
        return $message.' Der Gutschein wurde entfernt – bitte prüfen Sie den Gesamtbetrag.';
    }

    /**
     * Der Gutschein gilt weiter, ist aber inzwischen einen anderen Betrag wert –
     * etwa weil er im Backend geändert wurde, während der Warenkorb offen war.
     * Auch das verändert den Gesamtbetrag und darf nicht stillschweigend
     * passieren.
     */
    public static function amountChanged(float $previous, float $current): string
    {
        return 'Der Rabatt dieses Gutscheins hat sich von '.\Shop::price($previous)
            .' auf '.\Shop::price($current).' geändert – bitte prüfen Sie den Gesamtbetrag.';
    }

    public static function unknownCode(): string
    {
        return 'Falscher Gutscheincode';
    }

    public static function noLongerValid(): string
    {
        return 'Der hinterlegte Gutschein ist nicht mehr gültig.';
    }
}
