<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reparatur, keine neue Struktur: `password_reset_tokens` wird eigentlich
     * schon von 0001_01_01_000000_create_users_table angelegt.
     *
     * Auf der Produktivdatenbank fehlt die Tabelle trotzdem, während jene
     * Migration als ausgeführt vermerkt ist – `migrate` holt sie dort also nie
     * nach. Folge: `POST /password/email` lief in einen 500er
     * (SQLSTATE[42S02], Table 'fraukruner.password_reset_tokens' doesn't exist),
     * das Zurücksetzen des Passworts war für alle Nutzerinnen unbenutzbar.
     *
     * Das Schema ist Zeichen für Zeichen das der users-Migration, damit beide
     * Wege – frische Installation und Reparatur – dieselbe Tabelle ergeben.
     * Der hasTable()-Guard macht die Migration auf gesunden Datenbanken zum
     * No-op, sie darf also überall mitlaufen.
     */
    public function up(): void
    {
        if (Schema::hasTable('password_reset_tokens')) {
            return;
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Kein dropIfExists: Das Zurückrollen dieser Reparatur würde die Tabelle
     * auch dort entfernen, wo die users-Migration sie rechtmäßig angelegt hat.
     */
    public function down(): void
    {
        //
    }
};
