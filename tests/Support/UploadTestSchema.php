<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal-Schema für die Upload-Tests.
 *
 * Die Alt-Tabellen dieser Anwendung werden nicht per Migration verwaltet
 * (database/migrations enthält nur users/cache/jobs/menus). Damit die Tests
 * trotzdem überall – lokal wie auf dem Server – laufen, ohne die echte
 * Datenbank anzufassen, wird hier das benötigte Schema in der
 * SQLite-In-Memory-Datenbank aus phpunit.xml nachgebaut.
 *
 * Nur die Spalten, die von den Upload-Pfaden tatsächlich berührt werden.
 * Abweichungen zwischen diesem Schema und der echten Datenbank findet
 * `php artisan uploads:diagnose`.
 */
class UploadTestSchema
{
    /**
     * Spalten, die der Upload-Code in der echten DB erwartet.
     *
     * @var array<string, list<string>>
     */
    public const REQUIRED_COLUMNS = [
        'products' => ['image', 'meta_remove_status'],
        'images' => ['product_id', 'image', 'nsfw', 'meta_remove_status'],
        'orders' => ['video', 'video_uploaded_at'],
        'orderimages' => ['order_id', 'image', 'meta_remove_status'],
        'profiles' => ['profile_img', 'meta_remove_status'],
        'verifications' => ['person_id_shot_img', 'id_card_front_img', 'id_card_back_img', 'status'],
    ];

    /**
     * @var list<string>
     */
    private const TABLES = [
        'points',
        'metas',
        'logs',
        'settings',
        'categories',
        'images',
        'orderimages',
        'orders',
        'products',
        'methods',
        'addresses',
        'verifications',
        'profiles',
        'users',
        'roles',
    ];

    public static function create(): void
    {
        self::drop();

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('display_name');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('name');
            $table->string('last_name', 50)->nullable();
            $table->string('email');
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->string('username', 100)->nullable();
            $table->tinyInteger('status')->nullable()->default(1);
            $table->tinyInteger('visibiliti_status')->default(1);
            $table->integer('commission')->nullable();
            $table->tinyInteger('verified')->default(0);
            $table->timestamp('verification_deleted_at')->nullable();
            $table->tinyInteger('resend')->default(0);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('email_send_at')->nullable();
            $table->tinyInteger('boosted')->default(0);
            $table->boolean('is_commercial')->default(false);
            $table->timestamps();
        });

        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('profile_img')->nullable();
            $table->string('username')->nullable();
            $table->text('description')->nullable();
            // Wird von ProfileController::info() geschrieben.
            $table->boolean('meta_remove_status')->default(false);
            $table->timestamps();
        });

        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('street')->nullable();
            $table->string('house_no')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('person_id_shot_img')->nullable();
            $table->string('id_card_front_img')->nullable();
            $table->string('id_card_back_img')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('paypal_email')->nullable();
            $table->string('additional')->nullable();
            $table->string('street')->nullable();
            $table->string('house_no')->nullable();
            $table->string('zip')->nullable();
            $table->string('federal_state')->nullable();
            $table->string('po_box')->nullable();
            $table->string('vat_id')->nullable();
            $table->timestamps();
        });

        Schema::create('methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('paypal')->nullable();
            $table->string('iban')->nullable();
            $table->string('bic')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->integer('order')->default(1);
            $table->string('name');
            $table->string('slug');
            $table->tinyInteger('featured')->nullable();
            $table->string('image')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->integer('parent_id')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->integer('price')->nullable();
            $table->integer('saleprice')->nullable();
            $table->text('details')->nullable();
            $table->integer('quantity')->nullable();
            $table->longText('description')->nullable();
            $table->string('image', 200)->nullable();
            $table->integer('view')->default(0);
            $table->integer('sale_count')->default(1);
            $table->tinyInteger('is_variable')->nullable();
            $table->string('tags')->nullable();
            $table->longText('finishings')->nullable();
            $table->longText('addition')->nullable();
            $table->longText('wearing_time')->nullable();
            $table->decimal('shipping_cost', 10, 0)->nullable();
            $table->tinyInteger('featured')->nullable();
            $table->integer('discount')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('selloption')->default(0);
            $table->tinyInteger('boosted')->default(0);
            // Wird von ProductsController::store()/update() geschrieben.
            $table->boolean('meta_remove_status')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('product_id');
            $table->text('image');
            $table->tinyInteger('nsfw')->default(0);
            $table->boolean('meta_remove_status')->default(false);
            $table->text('meta_remove_log')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email', 100)->nullable();
            $table->decimal('subtotal', 8, 2)->nullable();
            $table->decimal('tax', 8, 2)->nullable();
            $table->decimal('shipping_cost', 8, 2)->nullable();
            $table->string('shipping_method', 50)->nullable();
            $table->string('tracking_Id')->nullable();
            $table->decimal('total', 8, 2)->nullable();
            $table->string('payment_gateway', 50)->nullable();
            $table->integer('shipped')->nullable();
            $table->timestamp('video_uploaded_at')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->text('message')->nullable();
            $table->tinyInteger('payouts_status')->default(0);
            $table->tinyInteger('payouts_rerquest')->default(0);
            $table->decimal('vendor_total', 8, 2)->nullable();
            $table->decimal('commission', 8, 2)->nullable();
            $table->string('photo')->nullable();
            $table->string('video')->nullable();
            $table->timestamp('shipping_date')->nullable();
            $table->tinyInteger('send_shipping_email')->nullable();
            $table->string('payment_id')->nullable();
            $table->integer('payment_status')->default(0);
            $table->tinyInteger('is_rated')->default(0);
            $table->timestamps();
        });

        Schema::create('orderimages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('order_id');
            $table->string('image');
            // Wird von PagesController::photoUpload() geschrieben.
            $table->boolean('meta_remove_status')->default(false);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key');
            $table->string('display_name')->default('');
            $table->text('value')->nullable();
            $table->text('details')->nullable();
            $table->string('type')->default('text');
            $table->integer('order')->default(1);
            $table->string('group')->nullable();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });

        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('metable_id');
            $table->string('metable_type');
            $table->string('column_name');
            $table->longText('column_value')->nullable();
            $table->timestamps();
        });

        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pointable_id');
            $table->string('pointable_type');
            $table->bigInteger('points')->default(0);
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
