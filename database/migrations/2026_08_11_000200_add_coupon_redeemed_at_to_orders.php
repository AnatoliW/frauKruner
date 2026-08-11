<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * coupon_redeemed_at markiert, dass die Einlösung eines Gutscheins für
     * diese Bestellung gebucht wurde. Ohne die Markierung würde ein zweiter
     * Durchlauf von processPayment() den Zähler erneut hochsetzen.
     *
     * discount_code war varchar(30), der Gutscheincode selbst aber varchar(50).
     * Ein längerer Code hätte beim Anlegen der Bestellung einen Fehler
     * geworfen (MySQL Strict Mode) statt sauber durchzulaufen.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('coupon_redeemed_at')->nullable()->after('discount_code');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('discount_code', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('coupon_redeemed_at');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('discount_code', 30)->nullable()->change();
        });
    }
};
