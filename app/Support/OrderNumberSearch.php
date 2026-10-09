<?php

namespace App\Support;

/**
 * Nummer aus einem Sucheingabefeld herauslesen.
 *
 * Gesucht wird im Adminbereich an drei Stellen nach derselben Zahl – in der
 * Bestellliste, in der Vorkasse-Liste und in den Auszahlungen. Eingetippt oder
 * eingefügt wird dabei ganz Unterschiedliches:
 *
 *   `5131`               die nackte Nummer
 *   `FK2026-5131`        so steht sie auf dem Beleg und auf dem Kontoauszug
 *   `FK2026-5131-12034`  die Gutschrift-Nr. der Herstellerin
 *   ` 5131 `             mit Leerzeichen aus der Zwischenablage
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
     * Erkannt wird zuerst eine vollständige Beleg- oder Gutschrift-Nummer, denn
     * dort steht die gesuchte Zahl in der Mitte: Bei `FK2026-5131` ist die 2026
     * das Jahr, bei `FK2026-5131-12034` die 12034 die Herstellerin. Gesucht ist
     * in beiden Fällen die 5131 – die Nummer des Belegs. Würde stattdessen die
     * Zahl am Ende genommen, träfe eine eingefügte Gutschrift-Nr. die
     * unbeteiligte Bestellung mit der ID 12034.
     *
     * Erst wenn das nicht passt, gelten die Ziffern am Ende der Eingabe. Das
     * deckt die nackte Nummer ab und einen Satz, der auf sie endet.
     */
    public static function number(?string $search): ?string
    {
        $search = trim((string) $search);

        if ($search === '') {
            return null;
        }

        if (preg_match('/FK\d{4}-(\d+)(?:-\d+)?\s*$/i', $search, $matches)) {
            $search = $matches[1];
        } elseif (preg_match('/(\d+)\s*$/', $search, $matches)) {
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
