<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal-Schema für die Tests der Online-Überweisung.
 *
 * Die Alt-Tabellen dieser Anwendung werden nicht per Migration verwaltet, daher
 * wird das benötigte Schema – analog zu Tests\Support\CouponTestSchema – in der
 * SQLite-In-Memory-DB aus phpunit.xml nachgebaut.
 *
 * Enthalten ist nur, was am Zahlungseingang hängt: die Bestellung selbst, das
 * Produkt und sein Verkäufer (für Bestandsbuchung und Mails), das Protokoll und
 * die Gutscheine.
 */
class MicropaymentTestSchema
{
    /**
     * @var list<string>
     */
    private const TABLES = ['orders', 'products', 'users', 'points', 'logs', 'coupons', 'packages', 'boosts', 'payments', 'addresses', 'verifications', 'metas'];

    public static function create(): void
    {
        self::drop();

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('street', 100)->nullable();
            $table->string('house_no', 20)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('federal_state', 100)->nullable();
            $table->decimal('total', 8, 2)->nullable();
            $table->decimal('subtotal', 8, 2)->nullable();
            $table->decimal('discount', 8, 2)->nullable();
            $table->string('discount_code', 50)->nullable();
            $table->timestamp('coupon_redeemed_at')->nullable();
            $table->string('payment_gateway', 50)->nullable();
            $table->string('payment_id', 190)->nullable();
            // Wie in der echten Datenbank nullable, siehe CouponTestSchema.
            $table->integer('payment_status')->nullable()->default(0);
            $table->timestamp('confirmed_at')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->timestamps();
        });

        // Product nutzt SoftDeletes und rechnet Preise in Cent um; für die
        // Bestandsbuchung zählen quantity, sale_count, selloption und status.
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->integer('price')->default(0);
            $table->decimal('shipping_cost', 8, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->integer('sale_count')->default(0);
            $table->tinyInteger('selloption')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('boosted')->default(0);
            $table->timestamp('boost_start_date')->nullable();
            $table->timestamp('boost_end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('role_id')->nullable();
            $table->tinyInteger('boosted')->default(0);
            $table->timestamp('boost_start_date')->nullable();
            $table->timestamp('boost_end_date')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        // User::booted() legt beim Anlegen einen Punktestand an.
        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pointable_id')->nullable();
            $table->string('pointable_type')->nullable();
            $table->integer('points')->default(0);
            $table->timestamps();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->text('details')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 50)->unique();
            $table->decimal('discount', 10, 2)->default(0);
            $table->date('expire_at')->nullable();
            $table->unsignedInteger('limit')->default(1);
            $table->decimal('minimum_cart', 10, 2)->default(0);
            $table->unsignedInteger('used')->default(0);
            $table->timestamps();
        });

        // Hervorhebungen: Paket, Boost und die zugehörige Zahlung. Preise liegen
        // wie in der Anwendung in Cent, die Modelle rechnen beim Lesen um.
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('price')->default(0);
            $table->integer('days')->default(30);
            $table->timestamps();
        });

        Schema::create('boosts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('boostable_id')->nullable();
            $table->string('boostable_type')->nullable();
            $table->integer('price')->default(0);
            $table->integer('base_price')->nullable();
            $table->integer('tax')->default(0);
            $table->timestamp('start_day')->nullable();
            $table->timestamp('end_day')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->text('user_info')->nullable();
            $table->timestamps();
        });

        // Die Rechnung einer Hervorhebung liest die Anschrift der Verkaeuferin
        // ueber User::address() bzw. User::verification(). Ohne die Tabellen
        // scheitert schon die Relation, nicht erst die Anzeige.
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('street')->nullable();
            $table->string('house_no')->nullable();
            $table->string('zip')->nullable();
            $table->string('federal_state')->nullable();
            $table->timestamps();
        });

        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('street')->nullable();
            $table->string('house_no')->nullable();
            $table->string('zip')->nullable();
            $table->string('city')->nullable();
            $table->string('last_name')->nullable();
            $table->timestamps();
        });

        // User nutzt HasMeta: Attribute wie `vat` liegen nicht in der Spalte,
        // sondern als Zeile in `metas`. Die Rechnung liest sie.
        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('metable_id')->nullable();
            $table->string('metable_type')->nullable();
            $table->string('column_name')->nullable();
            $table->text('column_value')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->string('payable_type')->nullable();
            $table->string('status', 20)->default('PENDING');
            $table->integer('amount')->default(0);
            $table->integer('tax')->default(0);
            $table->string('payment_trnx_id', 190)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->text('response_body')->nullable();
            $table->timestamps();
        });
    }

    public static function drop(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
    }
}
