<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * discount/minimum_cart waren int – im Admin eingegebene Cent-Beträge
     * wurden dadurch stillschweigend abgeschnitten. used war tinyint und
     * damit bei 127 Einlösungen am Anschlag.
     *
     * Die Spalten werden zusätzlich NOT NULL. Bestehende NULL-Werte müssen
     * deshalb vorher gefüllt werden: MySQL würde sie sonst zu 0 machen, und
     * ein Gutschein mit limit=0 wäre ab sofort "vollständig eingelöst".
     */
    public function up(): void
    {
        // Erst alle Abbruchgründe prüfen, dann erst schreiben. Andersherum
        // hinterlässt ein Abbruch bereits geänderte Daten – und die Migration
        // hätte genau das getan, wovor die Kommentare unten warnen.

        // code wird unten NOT NULL. Ein bestehender NULL-Code ließe das ALTER
        // TABLE auflaufen – und zwar erst mitten in der Umstellung. Es gibt
        // keinen sinnvollen Ersatzwert für einen Gutscheincode, also abbrechen
        // und die Entscheidung dem Menschen überlassen.
        $withoutCode = DB::table('coupons')->whereNull('code')->count();

        if ($withoutCode > 0) {
            throw new RuntimeException(
                "Es gibt {$withoutCode} Gutschein(e) ohne Code. "
                .'Bitte zuerst einen Code eintragen oder die Datensätze löschen, dann die Migration erneut starten.'
            );
        }

        // Zu lange Codes würden beim Verkürzen auf 50 Zeichen einen Fehler
        // werfen. Lieber vorher hart abbrechen als halb migrieren – DDL ist in
        // MySQL nicht transaktional, ein Abbruch mittendrin wäre nicht
        // wiederholbar.
        //
        // LENGTH() statt CHAR_LENGTH(): zählt in SQLite Zeichen und in MySQL
        // Bytes, liegt also nie zu niedrig. Ein Code, der nur wegen Umlauten
        // über 50 Bytes kommt, wird damit vorsichtshalber gemeldet.
        $tooLong = DB::table('coupons')->whereRaw('LENGTH(code) > 50')->count();

        if ($tooLong > 0) {
            throw new RuntimeException(
                "Es gibt {$tooLong} Gutschein(e) mit einem Code über 50 Zeichen. "
                . 'Bitte zuerst manuell kürzen, dann die Migration erneut starten.'
            );
        }

        $duplicates = DB::table('coupons')
            ->select('code')
            ->groupBy('code')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('code');

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException(
                'Doppelte Gutscheincodes verhindern den Unique-Index: '
                . $duplicates->implode(', ')
                . '. Bitte zuerst bereinigen, dann die Migration erneut starten.'
            );
        }

        // Ab hier wird geschrieben. Die Spalten werden gleich NOT NULL; MySQL
        // würde bestehende NULL-Werte sonst stillschweigend zu 0 machen, und
        // ein Gutschein mit limit=0 wäre ab sofort "vollständig eingelöst".
        DB::table('coupons')->whereNull('discount')->update(['discount' => 0]);
        DB::table('coupons')->whereNull('minimum_cart')->update(['minimum_cart' => 0]);
        DB::table('coupons')->whereNull('used')->update(['used' => 0]);
        DB::table('coupons')->whereNull('limit')->update(['limit' => 1]);

        Schema::table('coupons', function (Blueprint $table) {
            $table->string('code', 50)->change();
            $table->decimal('discount', 10, 2)->default(0)->change();
            $table->decimal('minimum_cart', 10, 2)->default(0)->change();
            $table->unsignedInteger('limit')->default(1)->change();
            $table->unsignedInteger('used')->default(0)->change();
        });

        // Der Index kann aus einem früheren, mittendrin abgebrochenen Lauf
        // schon dastehen – ein zweites CREATE UNIQUE INDEX wäre dann ein
        // Fehler, obwohl der Zustand stimmt.
        if (! Schema::hasIndex('coupons', ['code'], 'unique')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->unique('code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('coupons', ['code'], 'unique')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropUnique(['code']);
            });
        }

        // discount/minimum_cart bleiben bewusst decimal: ein Rückbau auf int
        // würde genau die Cent-Beträge abschneiden, wegen derer diese
        // Migration überhaupt existiert.
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->change();
            $table->decimal('discount', 10, 2)->nullable()->default(null)->change();
            $table->decimal('minimum_cart', 10, 2)->nullable()->default(null)->change();
            $table->unsignedInteger('limit')->nullable()->default(null)->change();
            $table->unsignedInteger('used')->nullable()->default(0)->change();
        });
    }
};
