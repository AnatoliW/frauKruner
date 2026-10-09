<?php

/**
 * Stimmen die Belegnummern wieder mit den archivierten Belegen zusammen?
 *
 * Nur lesend: Das Skript zaehlt und vergleicht. Es schreibt nichts, legt nichts
 * an und aendert keine Einstellung.
 *
 * Hintergrund: Bis zum 29.09.2026 trug ein Beleg die Nummer seiner Position
 * (`FK<Jahr>-<Positions-ID>`), die Gutschrift der Herstellerin dieselbe Nummer
 * mit ihrer Nutzer-ID am Ende. Am 30.09.2026 wurde die Rechnung auf die
 * Bestellnummer umgestellt (`FK<Jahr>-<Haupt-ID>`) - damit aenderten sich
 * rueckwirkend auch alle schon gedruckten Belege. Dieser Rueckbau stellt die
 * alten Nummern wieder her und schreibt sie in die Spalte `invoice_no` fest.
 *
 * Das Skript zeigt je Zeile der Auszahlungsliste drei Nummern:
 *
 *   gespeichert   was jetzt in der Spalte steht und auf dem Beleg erscheint
 *   alte Formel   was vor dem 30.09.2026 auf dem Beleg stand
 *   Zwischenstand was zwischen dem 30.09.2026 und dem Rueckbau dort stand
 *
 * Stimmen Spalte 1 und 2 ueberall ueberein, passen die archivierten Belege
 * wieder. Abweichungen werden am Ende gezaehlt und einzeln aufgefuehrt.
 *
 * Aufruf auf dem Server, im Projektverzeichnis:
 *
 *     php fk-belegnummern-pruefen.php            # Stichprobe + Gesamtzaehlung
 *     php fk-belegnummern-pruefen.php 13         # Seite 13 der Auszahlungsliste
 *     php fk-belegnummern-pruefen.php 13 50      # Seite 13 bei 50 Zeilen/Seite
 */

use App\Order;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$seite = isset($argv[1]) ? max(1, (int) $argv[1]) : null;
$proSeite = isset($argv[2]) ? max(1, (int) $argv[2]) : 50;

echo PHP_EOL;

if (! Schema::hasColumn('orders', 'invoice_no')) {
    echo "Die Spalte `invoice_no` fehlt noch. Erst `php artisan migrate` ausfuehren,".PHP_EOL;
    echo "dann dieses Skript erneut starten.".PHP_EOL.PHP_EOL;

    exit(1);
}

/** Die alte Formel von vor dem 30.09.2026. */
$alteFormel = static fn (Order $p): string => 'FK'.$p->created_at->format('Y').'-'.$p->getKey();

/** Was zwischen dem 30.09.2026 und dem Rueckbau auf dem Beleg stand. */
$zwischenstand = static fn (Order $p): string => $p->orderNumber();

// Dieselbe Abfrage und Sortierung wie die Auszahlungsliste im Adminbereich.
$positionen = Order::query()
    ->paid()
    ->active()
    ->children()
    ->with('parent')
    ->orderBy('created_at', 'desc')
    ->get();

echo 'Zeilen in der Auszahlungsliste: '.$positionen->count().PHP_EOL;

// 1. Gesamtzaehlung: Weicht irgendwo die gespeicherte Nummer von der alten ab?
$abweichend = $positionen->filter(
    fn (Order $p) => $p->invoiceNumber() !== $alteFormel($p)
);

echo 'Belege mit der alten Nummer:    '.($positionen->count() - $abweichend->count()).PHP_EOL;
echo 'Abweichungen:                   '.$abweichend->count().PHP_EOL;

if ($abweichend->isNotEmpty()) {
    echo PHP_EOL.'--- Abweichungen im Einzelnen ---'.PHP_EOL;
    printf("%-11s %-11s %-18s %-18s %s".PHP_EOL, 'Position', 'Datum', 'gespeichert', 'alte Formel', 'Haupt-ID');

    foreach ($abweichend as $p) {
        printf("%-11s %-11s %-18s %-18s %s".PHP_EOL,
            $p->getKey(),
            $p->created_at->format('d.m.Y'),
            $p->invoiceNumber(),
            $alteFormel($p),
            (string) $p->parent_id
        );
    }

    echo PHP_EOL.'Belege ab dem 30.09.2026 duerfen abweichen: Sie sind mit der'.PHP_EOL;
    echo 'Zwischenstand-Nummer herausgegangen und behalten sie.'.PHP_EOL;
}

// 2. Eine Seite zum Nachsehen - genau das, was im Adminbereich zu sehen ist.
$start = $seite !== null ? ($seite - 1) * $proSeite : 0;
$ausschnitt = $positionen->slice($start, $seite !== null ? $proSeite : 15);

echo PHP_EOL.'--- '.($seite !== null
    ? 'Seite '.$seite.' bei '.$proSeite.' Zeilen/Seite'
    : 'Die 15 neuesten Zeilen').' ---'.PHP_EOL;

printf("%-11s %-11s %-18s %-18s %-18s %s".PHP_EOL,
    'Position', 'Datum', 'gespeichert', 'alte Formel', 'Zwischenstand', 'Gutschrift');

foreach ($ausschnitt as $p) {
    printf("%-11s %-11s %-18s %-18s %-18s %s".PHP_EOL,
        $p->getKey(),
        $p->created_at->format('d.m.Y'),
        $p->invoiceNumber(),
        $alteFormel($p),
        $zwischenstand($p),
        $p->gutschriftNumber()
    );
}

echo PHP_EOL;
