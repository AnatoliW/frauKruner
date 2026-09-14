<?php

/**
 * Sicherung fuer den Befehl boosts:expire.
 *
 * boosts:expire aendert genau drei Spalten - boosts.status, products.boosted
 * und users.boosted - und setzt sie ausschliesslich von 1 auf 0. Es genuegt
 * daher, festzuhalten, welche Zeilen vorher auf 1 standen.
 *
 * Das ist hier die bessere Sicherung als ein vollstaendiger Dump: Ein Dump
 * zurueckzuspielen wuerde auch alles andere auf den Stand von vorher setzen -
 * Bestellungen, Nachrichten, Registrierungen, die inzwischen dazugekommen
 * sind. Diese Datei stellt nur die Push-Kennzeichen wieder her. Nebenbei
 * braucht sie keinen installierten MySQL-Client.
 *
 * Sichern:       php push-snapshot.php
 * Zuruecksetzen: php push-snapshot.php --restore=storage/app/push-rollback-....sql
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$spalten = [
    'boosts' => 'status',
    'products' => 'boosted',
    'users' => 'boosted',
];

$restore = null;

foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--restore=')) {
        $restore = substr($argument, strlen('--restore='));
    }
}

if ($restore !== null) {
    $pfad = str_starts_with($restore, '/') ? $restore : __DIR__.'/'.$restore;

    if (! is_file($pfad)) {
        fwrite(STDERR, 'Datei nicht gefunden: '.$pfad."\n");
        exit(1);
    }

    // Zeilenweise lesen: Jede UPDATE-Anweisung steht auf genau einer Zeile,
    // alles andere in der Datei sind Kommentare.
    $anweisungen = [];

    foreach (file($pfad, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $zeile) {
        $zeile = trim($zeile);

        if (str_starts_with($zeile, 'UPDATE ')) {
            $anweisungen[] = rtrim($zeile, ';');
        }
    }

    if ($anweisungen === []) {
        fwrite(STDERR, "Keine UPDATE-Anweisungen in der Datei.\n");
        exit(1);
    }

    $betroffen = 0;

    DB::transaction(function () use ($anweisungen, &$betroffen) {
        foreach ($anweisungen as $anweisung) {
            $betroffen += DB::update($anweisung);
        }
    });

    echo 'Zurueckgesetzt: '.$betroffen.' Zeilen aus '.count($anweisungen)." Anweisungen\n";
    exit(0);
}

$datei = __DIR__.'/storage/app/push-rollback-'.date('Ymd-His').'.sql';
$sql = "-- Rueckstellung fuer boosts:expire\n-- erstellt: ".date('Y-m-d H:i:s')."\n\n";
$gesamt = 0;

foreach ($spalten as $tabelle => $spalte) {
    $ids = DB::table($tabelle)->where($spalte, 1)->orderBy('id')->pluck('id')->all();
    $gesamt += count($ids);

    $sql .= '-- '.$tabelle.'.'.$spalte.' = 1 bei '.count($ids)." Zeilen\n";

    if ($ids === []) {
        $sql .= "-- (keine)\n\n";

        continue;
    }

    foreach (array_chunk($ids, 500) as $teil) {
        $sql .= 'UPDATE '.$tabelle.' SET '.$spalte.' = 1 WHERE id IN ('.implode(',', $teil).");\n";
    }

    $sql .= "\n";
}

file_put_contents($datei, $sql);

echo 'Gesichert: '.$gesamt." Zeilen\n";
echo 'Datei: '.$datei."\n";
