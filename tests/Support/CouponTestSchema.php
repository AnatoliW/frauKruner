<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal-Schema für die Gutschein-Tests.
 *
 * Die Alt-Tabellen dieser Anwendung werden nicht per Migration verwaltet, daher
 * wird das benötigte Schema – analog zu Tests\Support\UploadTestSchema – in der
 * SQLite-In-Memory-DB aus phpunit.xml nachgebaut.
 *
 * coupons entspricht dem Stand nach 2026_08_11_000100_fix_coupons_column_types.
 * Von orders nur die Spalten, die an Gutscheinen und an der einmaligen
 * Bestätigung hängen.
 */
class CouponTestSchema
{
    /**
     * @var list<string>
     */
    private const TABLES = ['orders', 'coupons'];

    public static function create(): void
    {
        self::drop();

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

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('email', 100)->nullable();
            $table->decimal('total', 8, 2)->nullable();
            $table->decimal('discount', 8, 2)->nullable();
            $table->string('discount_code', 50)->nullable();
            $table->timestamp('coupon_redeemed_at')->nullable();
            $table->string('payment_gateway', 50)->nullable();
            // Wie in der echten Datenbank nullable: dort ist die Spalte
            // `int(11) NULL DEFAULT '0'`. Ein strengeres Testschema wuerde
            // verdecken, dass markAsPaid() den leeren Wert behandeln muss.
            $table->integer('payment_status')->nullable()->default(0);
            $table->timestamp('confirmed_at')->nullable();
            $table->tinyInteger('status')->nullable();
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
