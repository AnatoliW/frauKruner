<?php

namespace App\Support;

/**
 * Nummer aus einem Sucheingabefeld herauslesen.
 *
 * Gesucht wird im Adminbereich an drei Stellen nach derselben Zahl – in der
 * Bestellliste, in der Vorkasse-Liste und in den Auszahlungen. Eingetippt oder
 * eingefügt wird dabei ganz Unterschiedliches:
 *
 *   `5131`          die nackte Nummer
 *   `FK2026-5131`   so steht sie in der Mail und auf dem Kontoauszug
 *   ` 5131 `        mit Leerzeichen aus der Zwischenablage
 *
 * Welche Spalten dann verglichen werden, entscheidet jede Liste selbst – die
 * Bestellliste sucht Bestellungen, die Auszahlungsliste Positionen. Gemeinsam
 * ist nur das Herauslesen der Zahl, und genau das steht deshalb hier: sonst
 * liegt dieselbe Zeichenkettenarbeit dreimal im Code und läuft auseinander.
 */
class OrderNumberSearch
{
    /**
     * Die gesuchte Nummer, oder null, wenn die Eingabe keine ist.
     *
     * Es werden bewusst nur die Ziffern am Ende genommen: Bei `FK2026-5131` ist
     * die 2026 das Jahr und nicht die gesuchte Nummer.
     */
    public static function number(?string $search): ?string
    {
        $search = trim((string) $search);

        if ($search === '') {
            return null;
        }

        if (preg_match('/(\d+)\s*$/', $search, $matches)) {
            $search = $matches[1];
        }

        // Keine Zahl (ein Name zum Beispiel): Diese Bedingung trägt dann nichts
        // bei, statt die ganze Suche auf eine leere Menge zu ziehen.
        if (! ctype_digit($search)) {
            return null;
        }

        // Führende Nullen abschneiden, damit `0005131` dieselbe Zeile findet.
        $normalisiert = ltrim($search, '0');

        return $normalisiert === '' ? '0' : $normalisiert;
    }
}
