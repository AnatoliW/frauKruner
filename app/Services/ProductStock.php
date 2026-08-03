<?php

namespace App\Services;

use App\Order;
use App\Product;

/**
 * Bucht das Produkt einer Bestellung ab bzw. gibt es wieder frei.
 *
 * Wichtig: Eine Vorkasse-Bestellung reserviert bewusst nichts. Das Produkt
 * bleibt bis zum Zahlungseingang im Shop sichtbar und kaufbar, damit der Shop
 * nicht durch unbezahlte Bestellungen blockiert wird. Abgebucht wird erst,
 * wenn die Bestellung als bezahlt markiert wird.
 */
class ProductStock
{
    /**
     * Verkauf abbuchen: Verkaufszähler, Bestand und - bei Einzelstücken -
     * Deaktivierung im Shop.
     */
    public static function bookSale(Order $order): void
    {
        $product = static::product($order);

        if (! $product) {
            return;
        }

        $product->increment('sale_count');
        $product->update(['quantity' => max(0, (int) $product->quantity - 1)]);

        // selloption = 0 bedeutet Einzelstück: nach dem Verkauf aus dem Shop nehmen.
        if (! $product->selloption) {
            $product->update(['status' => 0]);
        }
    }

    /**
     * Abbuchung rückgängig machen, z. B. bei einem Storno.
     */
    public static function releaseSale(Order $order): void
    {
        $product = static::product($order);

        if (! $product) {
            return;
        }

        if ((int) $product->sale_count > 0) {
            $product->decrement('sale_count');
        }

        $product->update(['quantity' => (int) $product->quantity + 1]);

        if (! $product->selloption) {
            $product->update(['status' => 1]);
        }
    }

    protected static function product(Order $order): ?Product
    {
        $product = $order->product;

        return $product && $product->exists ? $product : null;
    }
}
