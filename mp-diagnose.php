<?php

/**
 * Nur-Lesen-Diagnose fuer die 0,49-EUR-Zahlungen bei Micropayment.
 *
 * Sucht die Vorgaenge, bei denen ein Betrag von 0 an das Zahlungsfenster
 * gegangen ist. Schreibt nichts, aendert nichts.
 *
 * Aufruf:  php mp-diagnose.php
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function titel(string $text): void
{
    echo "\n".str_repeat('=', 78)."\n".$text."\n".str_repeat('=', 78)."\n";
}

function zeilen($rows, array $spalten): void
{
    $rows = collect($rows);

    if ($rows->isEmpty()) {
        echo "  (keine Treffer)\n";

        return;
    }

    echo '  '.implode(' | ', $spalten)."\n";
    echo '  '.str_repeat('-', 70)."\n";

    foreach ($rows as $row) {
        $werte = [];
        foreach ($spalten as $spalte) {
            $wert = data_get($row, $spalte);
            $werte[] = is_null($wert) ? 'NULL' : (string) $wert;
        }
        echo '  '.implode(' | ', $werte)."\n";
    }

    echo '  Anzahl: '.$rows->count()."\n";
}

// ---------------------------------------------------------------------------
titel('1. BESTELLUNGEN mit Gesamtbetrag 0 (Hauptbestellungen)');
// Diese gehen als amount=0 ins Zahlungsfenster. Rohwerte per Query Builder,
// damit kein Accessor dazwischenfunkt.
zeilen(
    DB::table('orders')
        ->whereNull('parent_id')
        ->where(fn ($q) => $q->whereNull('total')->orWhere('total', '<=', 0))
        ->orderByDesc('id')
        ->limit(40)
        ->get(['id', 'created_at', 'total', 'subtotal', 'discount', 'discount_code',
            'payment_gateway', 'payment_status', 'status', 'email']),
    ['id', 'created_at', 'total', 'subtotal', 'discount', 'discount_code',
        'payment_gateway', 'payment_status', 'status', 'email']
);

titel('1b. davon: wie viele haben ueberhaupt Unterbestellungen?');
$nullOrders = DB::table('orders')->whereNull('parent_id')
    ->where(fn ($q) => $q->whereNull('total')->orWhere('total', '<=', 0))
    ->pluck('id');
$mitKindern = DB::table('orders')->whereIn('parent_id', $nullOrders)
    ->distinct()->pluck('parent_id');
echo '  Hauptbestellungen mit total<=0 gesamt : '.$nullOrders->count()."\n";
echo '  davon MIT Unterbestellungen          : '.$mitKindern->count()."\n";
echo '  davon OHNE Unterbestellungen (leerer Warenkorb): '
    .($nullOrders->count() - $mitKindern->count())."\n";

// ---------------------------------------------------------------------------
titel('2. ZAHLUNGEN (Hervorhebungen) mit Betrag 0 oder unter 49 Cent');
// payments.amount liegt in Cent in der Datenbank.
zeilen(
    DB::table('payments')
        ->where('amount', '<', 49)
        ->orderByDesc('id')
        ->limit(40)
        ->get(['id', 'created_at', 'payable_id', 'payable_type', 'status',
            'amount', 'tax', 'payment_method', 'payment_trnx_id']),
    ['id', 'created_at', 'payable_id', 'payable_type', 'status',
        'amount', 'tax', 'payment_method', 'payment_trnx_id']
);

// ---------------------------------------------------------------------------
titel('3. PAKETE - Rohwert der Preisspalte (Cent!) gegen den gelesenen Wert');
// Ein Paket, das versehentlich mit dem Euro-Betrag gefuellt wurde, ergibt
// ueber den Accessor (Wert/100) einen Cent-Betrag unter dem Mindestbetrag.
foreach (DB::table('packages')->orderBy('id')->get() as $p) {
    $roh = (int) ($p->price ?? 0);
    $euro = $roh / 100;
    $mwst = $euro * ((float) (setting('finance.vat') ?? 0) / 100);
    $brutto = $euro + $mwst;
    $cent = (int) ($brutto * 100);

    printf(
        "  #%-4s %-28s roh=%-8s => %8s EUR  brutto=%8s EUR = %5s Cent %s\n",
        $p->id,
        mb_substr((string) ($p->name ?? '-'), 0, 28),
        $roh,
        number_format($euro, 2, ',', '.'),
        number_format($brutto, 2, ',', '.'),
        $cent,
        $cent < 49 ? '  <== UNTER MINDESTBETRAG' : ''
    );
}
echo '  MwSt-Satz aus den Einstellungen: '.(setting('finance.vat') ?? 'NICHT GESETZT')."\n";

// ---------------------------------------------------------------------------
titel('4. PROTOKOLL: gemeldete Betragsabweichungen (mismatch)');
// Schlaegt die Betragspruefung an, wird nichts gebucht - Geld da, Leistung nicht.
zeilen(
    DB::table('logs')
        ->where('details', 'like', '%mismatch%')
        ->orderByDesc('id')
        ->limit(30)
        ->get(['id', 'created_at', 'email', 'details']),
    ['id', 'created_at', 'email', 'details']
);

// ---------------------------------------------------------------------------
titel('5. PROTOKOLL: Micropayment-Ereignisse der letzten 60 Tage');
zeilen(
    DB::table('logs')
        ->where('details', 'like', '%reference%')
        ->where('created_at', '>=', now()->subDays(60))
        ->orderByDesc('id')
        ->limit(40)
        ->get(['id', 'created_at', 'email', 'details']),
    ['id', 'created_at', 'email', 'details']
);

titel('FERTIG - es wurde nichts geaendert.');
