<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Schreibt die Belegnummer fest in die Datenbank.
 *
 * Bisher wurde jede Belegnummer bei jedem Seitenaufruf neu aus `id` und
 * `created_at` berechnet. Das heisst: Wer die Formel aendert, aendert damit
 * rueckwirkend auch jeden Beleg, der schon gedruckt, verschickt und archiviert
 * war. Genau das ist am 30.09.2026 passiert - aus `FK2024-3552` wurde
 * `FK2024-3550`, und die gesicherten Belege stimmten nicht mehr.
 *
 * Eine Belegnummer ist ein feststehender Wert, keine Formel. Sie gehoert
 * deshalb in eine Spalte: einmal vergeben, danach nur noch gelesen. Eine
 * spaetere Formataenderung trifft dann nur noch neue Belege.
 *
 * Der Altbestand wird mit der Formel von vor dem 30.09.2026 gefuellt
 * (`FK<Jahr des Datensatzes>-<eigene ID>`). Damit tragen alle Belege wieder
 * genau die Nummer, unter der sie damals herausgegangen sind.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('invoice_no', 40)->nullable()->after('parent_id');
        });

        $this->backfill();

        // Erst nach dem Fuellen: Ein Index auf einer Spalte voller NULL-Werte
        // waere zwar gueltig, die Eindeutigkeit soll aber auch den Altbestand
        // abdecken. Geprueft: keine Doppelung ueber alle Datensaetze.
        Schema::table('orders', function (Blueprint $table) {
            $table->unique('invoice_no');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['invoice_no']);
            $table->dropColumn('invoice_no');
        });
    }

    /**
     * Traegt die alte Nummer in jeden vorhandenen Datensatz nach.
     *
     * Auf MySQL als eine einzige Anweisung: Live liegt die Datenbank auf einem
     * anderen Host, und tausende einzelne UPDATEs kosten dort jeweils ihren
     * Netzweg. Der Weg ueber PHP bleibt als Rueckfalloption, damit die Migration
     * auch auf SQLite laeuft.
     */
    private function backfill(): void
    {
        $treiber = DB::getDriverName();

        if (in_array($treiber, ['mysql', 'mariadb'], true)) {
            DB::statement(
                "update `orders`
                    set `invoice_no` = concat('FK', coalesce(year(`created_at`), year(now())), '-', `id`)
                  where `invoice_no` is null"
            );

            return;
        }

        DB::table('orders')
            ->whereNull('invoice_no')
            ->select('id', 'created_at')
            ->orderBy('id')
            ->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $jahr = $row->created_at
                        ? date('Y', strtotime((string) $row->created_at))
                        : date('Y');

                    DB::table('orders')
                        ->where('id', $row->id)
                        ->update(['invoice_no' => 'FK'.$jahr.'-'.$row->id]);
                }
            });
    }
};
