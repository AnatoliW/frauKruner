<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indizes für die Suche im Adminbereich – Bestellliste, Vorkasse, Auszahlungen.
 *
 * `orders` hatte Indizes auf user_id, vendor_id und product_id, aber keinen auf
 * parent_id. Ausgerechnet darüber hängen Bestellung und Position zusammen, und
 * genau diesen Zusammenhang durchsucht der Adminbereich: Wird nach einer
 * Verkäuferin oder einer Beleg-Nummer gesucht, fragt die Suche zu jeder
 * Bestellung nach ihren Positionen (`whereHas('childrens')`).
 *
 * Auf der Entwicklungs-Datenbank – MariaDB 10.4 – fiel das nie auf: Sie formt
 * diese Unterabfrage in einen Semi-Join um und wertet sie einmal aus. Gemessen
 * blieb die Suche dort linear, 117.000 Zeilen kosteten 95 ms, und ein Index
 * brachte nur 95 → 79 ms.
 *
 * Live läuft MySQL auf RDS, und das wertet dieselbe Unterabfrage je Zeile aus.
 * Ohne Index auf parent_id ist jede dieser Auswertungen ein vollständiger
 * Durchlauf durch `orders`. Bei 5.114 Bestellungen und 11.672 Zeilen sind das
 * rund 60 Millionen Zeilenzugriffe für eine einzige Suche – die Abfrage kam
 * nicht mehr zurück.
 *
 * Deshalb hier zwei Indizes:
 *
 *   parent_id                                für die Unterabfrage auf die
 *                                            Positionen, der wichtigste
 *   parent_id, payment_status, created_at    für die Seitenabfrage der Listen:
 *                                            Kopf oder Position, bezahlt oder
 *                                            offen, neueste zuerst
 *
 * Reine Lesebeschleunigung: Es werden keine Daten verändert und keine Spalte
 * angefasst. Bei dieser Tabellengröße ist das in Sekundenbruchteilen erledigt.
 */
return new class extends Migration
{
    /**
     * Ein Index wird nur angelegt, wenn er fehlt.
     *
     * Die Tabelle stammt aus einer gewachsenen Installation; auf manchen
     * Ständen kann ein gleichnamiger Index schon vorhanden sein. Ein zweites
     * `CREATE INDEX` desselben Namens bricht die Migration ab, und dann bleibt
     * auch der zweite, noch fehlende Index liegen.
     */
    private function hatIndex(string $name): bool
    {
        return collect(Schema::getIndexes('orders'))
            ->contains(fn (array $index): bool => $index['name'] === $name);
    }

    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! $this->hatIndex('orders_parent_id_index')) {
                $table->index('parent_id');
            }

            if (! $this->hatIndex('orders_liste_index')) {
                $table->index(['parent_id', 'payment_status', 'created_at'], 'orders_liste_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if ($this->hatIndex('orders_liste_index')) {
                $table->dropIndex('orders_liste_index');
            }

            if ($this->hatIndex('orders_parent_id_index')) {
                $table->dropIndex('orders_parent_id_index');
            }
        });
    }
};
