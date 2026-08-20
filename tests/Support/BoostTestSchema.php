<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal-Schema fuer die Push-Tests.
 *
 * Wie UploadTestSchema: Die Alt-Tabellen dieser Anwendung werden nicht per
 * Migration verwaltet, deshalb baut der Test nur die Spalten nach, die der
 * Push-Pfad tatsaechlich anfasst - in der SQLite-In-Memory-DB aus phpunit.xml.
 */
class BoostTestSchema
{
    /** @var list<string> */
    private const TABLES = ['payments', 'boosts', 'packages', 'points', 'users'];

    public static function create(): void
    {
        self::drop();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->tinyInteger('boosted')->default(0);
            $table->timestamp('boost_start_date')->nullable();
            $table->timestamp('boost_end_date')->nullable();
            $table->timestamps();
        });

        // Das User-Model legt beim Anlegen automatisch Punkte an.
        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pointable_id');
            $table->string('pointable_type');
            $table->bigInteger('points')->default(0);
            $table->timestamps();
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('price')->nullable();
            $table->integer('days')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('boosts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('boostable_id')->nullable();
            $table->string('boostable_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->integer('price')->default(0);
            $table->integer('base_price')->nullable();
            $table->integer('tax')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamp('start_day')->nullable();
            $table->timestamp('end_day')->nullable();
            $table->text('user_info')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payable_id');
            $table->string('payable_type');
            $table->string('payment_trnx_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status')->default('PENDING');
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('tax')->default(0);
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
