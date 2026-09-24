<?php

/**
 * Nur-Lesen: durchleuchtet eine einzelne Bestellung und ihr Umfeld.
 *
 * Aufruf:  php fk-einzelfall.php 12631
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$id = (int) ($argv[1] ?? 0);

if (! $id) {
    exit("Aufruf: php fk-einzelfall.php <order-id>\n");
}

function t(string $s): void { echo "\n".str_repeat('=', 78)."\n$s\n".str_repeat('=', 78)."\n"; }

$order = DB::table('orders')->find($id);

if (! $order) {
    exit("Bestellung $id gibt es nicht.\n");
}

t("1. BESTELLUNG $id");
foreach ((array) $order as $k => $v) {
    if ($v !== null && $v !== '') {
        printf("  %-22s %s\n", $k, is_scalar($v) ? $v : json_encode($v));
    }
}

t("2. UNTERBESTELLUNGEN (parent_id = $id)");
$kinder = DB::table('orders')->where('parent_id', $id)->get();
if ($kinder->isEmpty()) {
    echo "  KEINE. Der Warenkorb war beim Anlegen leer.\n";
} else {
    foreach ($kinder as $k) {
        printf("  #%-7s product_id=%-7s total=%-10s vendor_total=%-10s status=%s\n",
            $k->id, $k->product_id ?? '-', $k->total ?? '-', $k->vendor_total ?? '-', $k->status ?? '-');
    }
}

t('3. ANDERE BESTELLUNGEN DERSELBEN E-MAIL (Doppel-Absenden?)');
$geschwister = DB::table('orders')
    ->whereNull('parent_id')
    ->where('email', $order->email)
    ->orderBy('id')
    ->get(['id', 'created_at', 'total', 'subtotal', 'discount', 'discount_code',
        'payment_gateway', 'payment_status', 'status']);

foreach ($geschwister as $g) {
    printf("  %s#%-7s %s  total=%-10s subtotal=%-10s rabatt=%-8s %-16s bezahlt=%s\n",
        $g->id == $id ? '>> ' : '   ',
        $g->id, $g->created_at, $g->total ?? 'NULL', $g->subtotal ?? 'NULL',
        $g->discount ?? '-', $g->payment_gateway ?? '-', $g->payment_status ?? '0');
}

t('4. ZEITLICHE NACHBARN (5 davor / 5 danach, alle Kunden)');
$fenster = DB::table('orders')->whereNull('parent_id')
    ->whereBetween('id', [$id - 5, $id + 5])->orderBy('id')
    ->get(['id', 'created_at', 'email', 'total', 'subtotal', 'payment_gateway', 'payment_status']);
foreach ($fenster as $f) {
    printf("  %s#%-7s %s  total=%-10s subtotal=%-10s %-16s bezahlt=%s\n",
        $f->id == $id ? '>> ' : '   ',
        $f->id, $f->created_at, $f->total ?? 'NULL', $f->subtotal ?? 'NULL',
        $f->payment_gateway ?? '-', $f->payment_status ?? '0');
}

t("5. PROTOKOLL zu FK….-$id");
$logs = DB::table('logs')
    ->where('details', 'like', '%-'.$id.'%')
    ->orderBy('id')->get(['id', 'created_at', 'details']);
if ($logs->isEmpty()) {
    echo "  Keine Protokolleintraege. Es kam also keine verarbeitbare Rueckmeldung an.\n";
} else {
    foreach ($logs as $l) {
        echo "  #{$l->id} {$l->created_at}\n    {$l->details}\n";
    }
}

t('6. WAS JETZT AN MICROPAYMENT GEHEN WUERDE');
$subject = new \App\Payment\MicropaymentOrderSubject(\App\Order::find($id));
printf("  Referenz : %s\n", $subject->reference());
printf("  Betrag   : %d Cent (%s)\n", $subject->amountInCents(), $subject->amountForHumans());
printf("  bezahlt  : %s\n", $subject->isPaid() ? 'ja' : 'nein');

t('FERTIG - nichts geaendert.');
