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

// --------------------------------------------------- 0. Welche Datenbank?
$titel('0. Welche Datenbank laeuft hier?');

$version = DB::selectOne('select version() as v')->v;
$istMaria = stripos($version, 'mariadb') !== false;

printf("Version   %s\n", $version);
printf("Host      %s\n", config('database.connections.'.config('database.default').'.host'));

echo "\nWarum das die wichtigste Zeile ist: Die Suche vergleicht eine Bestellung\n";
echo "mit ihren Positionen ueber eine korrelierte EXISTS-Unterabfrage. MariaDB\n";
echo "formt die in einen Semi-Join um und bleibt linear schnell. MySQL fuehrt\n";
echo "sie je nach Version als abhaengige Unterabfrage aus - einmal pro Zeile.\n";
echo "Ohne Index auf orders.parent_id ist das jedes Mal ein voller Tabellen-\n";
echo "durchlauf, und aus Millisekunden werden Minuten. Punkt 4 zeigt, ob der\n";
echo "Index fehlt, Punkt 3 was der Plan daraus macht.\n";

// Damit dieses Skript nicht selbst haengt, wenn die Abfrage entgleist.
try {
    if ($istMaria) {
        DB::statement('SET SESSION max_statement_time = 20');
    } else {
        DB::statement('SET SESSION max_execution_time = 20000');
    }
    echo "\nZeitgrenze fuer Punkt 3: 20 Sekunden je Abfrage.\n";
} catch (\Throwable $e) {
    echo "\nHinweis: Zeitgrenze liess sich nicht setzen (".$e->getMessage().").\n";
}

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
    echo "\n".$name."\n";

    // Zuerst der Plan: Der kostet nichts und sagt schon alles. `type=ALL` in
    // einer Unterabfrage heisst voller Durchlauf je Zeile - das ist der Haenger.
    try {
        $q = $bauen();
        foreach (DB::select('EXPLAIN '.$q->toSql(), $q->getBindings()) as $r) {
            printf("  Plan  %-20s type=%-8s key=%-16s rows=%-9s %s\n",
                $r->select_type ?? '', $r->type ?? '', $r->key ?? 'KEINER',
                $r->rows ?? '', $r->Extra ?? '');
        }
    } catch (\Throwable $e) {
        printf("  Plan  nicht lesbar: %s\n", $e->getMessage());
    }

    try {
        $start = microtime(true);
        $treffer = $bauen()->count();
        printf("  Zeit  %7.0f ms   %5d Treffer\n", (microtime(true) - $start) * 1000, $treffer);
    } catch (\Throwable $e) {
        $kurz = explode("\n", $e->getMessage())[0];
        printf("  Zeit  abgebrochen: %s\n", substr($kurz, 0, 160));
        echo "        (Zeitgrenze erreicht - genau das ist die lange Wartezeit live)\n";
    }
};

$wieName = function ($q) use ($suche) {
    $q->where('first_name', 'like', "%{$suche}%")
        ->orWhere('last_name', 'like', "%{$suche}%")
        ->orWhere('email', 'like', "%{$suche}%");
};

// Die teure Bedingung isoliert: nur die Unterabfrage auf die Positionen.
$messen('/admin/orders - nur der Teil mit den Positionen', fn () => DB::table('orders as o')
    ->whereNull('o.parent_id')->where('o.payment_status', 1)
    ->whereExists(fn ($s) => $s->from('orders as k')
        ->whereColumn('k.parent_id', 'o.id')
        ->whereExists(fn ($u) => $u->from('users')->whereColumn('users.id', 'k.vendor_id')
            ->where(fn ($n) => $n->where('name', 'like', "%{$suche}%")->orWhere('last_name', 'like', "%{$suche}%")))));

$messen('/admin/orders - vollstaendig', fn () => DB::table('orders as o')
    ->whereNull('o.parent_id')->where('o.payment_status', 1)
    ->where(function ($q) use ($wieName, $suche) {
        $q->where($wieName)->orWhereExists(fn ($s) => $s->from('orders as k')
            ->whereColumn('k.parent_id', 'o.id')
            ->whereExists(fn ($u) => $u->from('users')->whereColumn('users.id', 'k.vendor_id')
                ->where(fn ($n) => $n->where('name', 'like', "%{$suche}%")->orWhere('last_name', 'like', "%{$suche}%"))));
    }));

$messen('/admin/prepayments', fn () => DB::table('orders as o')
    ->whereNull('o.parent_id')->where('o.payment_status', 0)
    ->where('o.payment_gateway', 'pre_payment')
    ->where($wieName));

$messen('/admin/payouts', fn () => DB::table('orders as o')
    ->whereNotNull('o.parent_id')->where('o.payment_status', 1)->where('o.status', 1)
    ->where($wieName));

// ------------------------------------------------------------ 4. Indizes
$titel('4. Welche Indizes hat orders?');

$indizes = [];
foreach (DB::select('SHOW INDEX FROM orders') as $i) {
    $indizes[$i->Key_name][] = $i->Column_name;
}
foreach ($indizes as $name => $spalten) {
    printf("  %-28s (%s)\n", $name, implode(', ', $spalten));
}

$hatParent = false;
foreach ($indizes as $spalten) {
    if (($spalten[0] ?? null) === 'parent_id') {
        $hatParent = true;
    }
}

echo "\n";
if ($hatParent) {
    echo "  parent_id ist indiziert - gut, die Unterabfrage kann gezielt suchen.\n";
} else {
    echo "  parent_id ist NICHT indiziert.\n";
    echo "  Auf MariaDB fiel das nicht auf (gemessen 95 ms bei 117.000 Zeilen, weil\n";
    echo "  der Semi-Join die Unterabfrage einmal auswertet). Auf MySQL wird sie je\n";
    echo "  Zeile ausgewertet und durchlaeuft dabei jedes Mal die ganze Tabelle.\n";
    echo "  Die Migration add_search_indexes_to_orders_table legt den Index an.\n";
}

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
