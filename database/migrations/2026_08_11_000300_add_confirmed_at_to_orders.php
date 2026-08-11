<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * confirmed_at markiert, dass processPayment() die Bestellung bereits
     * bestätigt hat – also Unterbestellungen fortgeschrieben und Mails
     * verschickt wurden.
     *
     * Für Stripe und PayPal verhindert bisher `payment_id|unique` einen
     * zweiten Durchlauf. Bei Vorkasse wird nichts validiert: Ein erneutes
     * Absenden des Formulars schickt Kundin und Verkäufer die gleichen Mails
     * noch einmal. Die Spalte macht den Schritt für alle Zahlarten einmalig.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('confirmed_at');
        });
    }
};
