<?php

/**
 * Warum ist die Suche im Adminbereich live langsam?
 *
 * Nur lesend: Das Skript zaehlt, misst und schaut nach: Es schreibt nichts,
 * legt nichts an und aendert keine Einstellung.
 *
 * Gemessen wird das, was sich auf dem Entwicklungsrechner nicht nachstellen
 * laesst. Dort liegt die Datenbank im selben Rechner, eine Abfrage kostet den
 * Bruchteil einer Millisekunde, und 190 Abfragen je Seite fallen nicht auf.
 * Liegt die Datenbank live auf einem anderen Host, kostet jede Abfrage ihren
 * Netzweg - und aus denselben 190 Abfragen werden Sekunden. Die Laufzeit einer
 * einzelnen leeren Abfrage (Punkt 2) entscheidet deshalb, ob die Wartezeit von
 * der Menge der Daten kommt oder von der Zahl der Abfragen.
 *
 * Aufruf auf dem Server, im Projektverzeichnis:
 *
 *     php fk-suche-diagnose.php
 *     php fk-suche-diagnose.php Schwarz     # mit einem echten Suchbegriff
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$suche = $argv[1] ?? 'Schwarz';

$titel = function (string $text): void {
    echo "\n".$text."\n".str_repeat('-', strlen($text))."\n";
};

// ---------------------------------------------------------------- 1. Umfang
$titel('1. Wie viele Zeilen liegen live?');

$bestellungen = DB::table('orders')->count();
$koepfe = DB::table('orders')->whereNull('parent_id')->count();
$positionen = DB::table('orders')->whereNotNull('parent_id')->count();
$nutzer = DB::table('users')->count();

printf("orders gesamt          %8d\n", $bestellungen);
printf("  davon Bestellungen   %8d\n", $koepfe);
printf("  davon Positionen     %8d\n", $positionen);
printf("users                  %8d\n", $nutzer);
echo "\nZum Vergleich: auf dem Entwicklungsrechner 4.041 orders und 6.008 users.\n";
echo "Gemessen skaliert die Suchabfrage linear - 117.000 Zeilen kosteten 95 ms.\n";
echo "Die Menge allein erklaert eine lange Wartezeit also erst ab Millionen Zeilen.\n";

// ------------------------------------------------- 2. Weg zur Datenbank
$titel('2. Was kostet eine einzelne Abfrage? (der entscheidende Wert)');

DB::select('select 1');                                   // Verbindung aufwaermen
$start = microtime(true);
$runden = 50;
for ($i = 0; $i < $runden; $i++) {
    DB::select('select 1');
}
$leerlauf = (microtime(true) - $start) * 1000 / $runden;

printf("leere Abfrage `select 1`   %6.2f ms\n", $leerlauf);
printf("Datenbank-Host             %s\n", config('database.connections.'.config('database.default').'.host'));

echo "\nDeutung:\n";
if ($leerlauf < 1) {
    echo "  Unter 1 ms - die Datenbank liegt praktisch im selben Rechner.\n";
    echo "  Dann kommt die Wartezeit NICHT von der Zahl der Abfragen.\n";
} else {
    printf("  %.2f ms je Abfrage. Eine Seite mit 190 Abfragen kostet damit allein\n", $leerlauf);
    printf("  %.1f Sekunden Netzweg - unabhaengig von der Datenmenge.\n", $leerlauf * 190 / 1000);
    echo "  Das ist dann die Ursache, und Vorladen der Beziehungen behebt sie.\n";
}

// --------------------------------------------------- 3. Die Suchabfragen
$titel('3. Wie lange braucht die Suchabfrage selbst?');

$messen = function (string $name, callable $bauen): void {
    try {
        $bauen();                                          // aufwaermen
        $start = microtime(true);
        $treffer = $bauen();
        printf("%-34s %7.0f ms   %5d Treffer\n", $name, (microtime(true) - $start) * 1000, $treffer);
    } catch (\Throwable $e) {
        printf("%-34s FEHLER: %s\n", $name, $e->getMessage());
    }
};

$wieName = function ($q) use ($suche) {
    $q->where('first_name', 'like', "%{$suche}%")
        ->orWhere('last_name', 'like', "%{$suche}%")
        ->orWhere('email', 'like', "%{$suche}%");
};

$messen('/admin/orders', fn () => DB::table('orders as o')
    ->whereNull('o.parent_id')->where('o.payment_status', 1)
    ->where(function ($q) use ($wieName, $suche) {
        $q->where($wieName)->orWhereExists(fn ($s) => $s->from('orders as k')
            ->whereColumn('k.parent_id', 'o.id')
            ->whereExists(fn ($u) => $u->from('users')->whereColumn('users.id', 'k.vendor_id')
                ->where(fn ($n) => $n->where('name', 'like', "%{$suche}%")->orWhere('last_name', 'like', "%{$suche}%"))));
    })->count());

$messen('/admin/prepayments', fn () => DB::table('orders as o')
    ->whereNull('o.parent_id')->where('o.payment_status', 0)
    ->where('o.payment_gateway', 'pre_payment')
    ->where($wieName)->count());

$messen('/admin/payouts', fn () => DB::table('orders as o')
    ->whereNotNull('o.parent_id')->where('o.payment_status', 1)->where('o.status', 1)
    ->where($wieName)->count());

// ------------------------------------------------------------ 4. Indizes
$titel('4. Welche Indizes hat orders?');

$indizes = [];
foreach (DB::select('SHOW INDEX FROM orders') as $i) {
    $indizes[$i->Key_name][] = $i->Column_name;
}
foreach ($indizes as $name => $spalten) {
    printf("  %-24s (%s)\n", $name, implode(', ', $spalten));
}
echo "\nGemessen bringen zusaetzliche Indizes hier fast nichts (95 -> 79 ms bei\n";
echo "117.000 Zeilen). Sie sind also nicht der Hebel - nur der Vollstaendigkeit halber.\n";

// ------------------------------------------------ 5. Stand des Codes
$titel('5. Laeuft live der Stand mit dem Vorladen?');

$quelle = file_get_contents(__DIR__.'/app/Filament/Resources/Payouts/PayoutResource.php');
$vorgeladen = str_contains($quelle, "'vendor.method'");

echo $vorgeladen
    ? "  ja - PayoutResource laedt vendor.method vor (19 statt 127 Abfragen)\n"
    : "  NEIN - PayoutResource laedt die Beziehungen noch nicht vor.\n"
      ."  Dann feuert /admin/payouts rund 190 Abfragen je Seite. Diesen Stand ausrollen.\n";

printf("  Dateistand  %s\n", date('Y-m-d H:i', filemtime(__DIR__.'/app/Filament/Resources/Payouts/PayoutResource.php')));
printf("  FILESYSTEM_DISK  %s  (bei s3 kostet jede Zeile in /admin/payouts einen HTTP-Aufruf)\n", config('filesystems.default'));
printf("  APP_ENV  %s   APP_DEBUG  %s\n", config('app.env'), var_export(config('app.debug'), true));

echo "\nFertig. Es wurde nichts geaendert.\n";
