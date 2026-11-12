<?php

use App\Order;
use App\Product;
use App\Services\ProductStock;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Tests\Concerns\UsesUploadSchema;
use Tests\Support\UploadTestHelpers;

/**
 * Ein verkauftes Produkt bleibt sichtbar, auch wenn es gelöscht wird.
 *
 * Verkäuferinnen nehmen Produkte aus dem Shop, die schon verkauft sind. Vorher
 * verschwanden danach Titel und Bild aus „Meine Verkäufe“, aus der Gutschrift
 * und aus „Meine Käufe“: Die Bestellung fand das gelöschte Produkt nicht mehr,
 * und beim Löschen wurden auch die Bilddateien entfernt.
 */
uses(UsesUploadSchema::class);

beforeEach(function () {
    View::getFinder()->prependLocation(__DIR__.'/../Support/views');
    config(['app.invoice_bundle_cutoff_date' => '2026-09-30']);
    config(['app.seller_credit_bundle_cutoff_at' => '2026-10-27T16:23:47+01:00']);
    config(['app.seller_credit_position_cutoff_at' => '2026-11-03T11:00:00+01:00']);
    UploadTestHelpers::fakeDisks();
});

/**
 * Verkauftes Produkt mit Bestellkopf und Position. Ältere Bestellungen tragen
 * keinen gespeicherten Produktnamen, deshalb ist `product_name` hier leer.
 *
 * @return array{0: Product, 1: Order, 2: Order, 3: \App\Models\User, 4: \App\Models\User}
 */
function soldProduct(array $positionAttributes = []): array
{
    $seller = UploadTestHelpers::seller();
    $buyer = UploadTestHelpers::buyer();
    $category = UploadTestHelpers::category(['name' => 'Slips']);

    Storage::put('products/verkauft.jpg', 'bild');

    $product = Product::create([
        'name' => 'Rosa Spitzenslip',
        'slug' => 'rosa-spitzenslip',
        'user_id' => $seller->id,
        'category_id' => $category->id,
        'price' => 40.00,
        'quantity' => 1,
        'status' => 1,
        'image' => 'products/verkauft.jpg',
    ]);

    $head = UploadTestHelpers::order($seller, $buyer, [
        'vendor_id' => null,
        'subtotal' => 40.00,
        'created_at' => '2026-11-05 10:00:00',
    ]);

    $position = UploadTestHelpers::order($seller, $buyer, array_merge([
        'parent_id' => $head->id,
        'product_id' => $product->id,
        'subtotal' => 40.00,
        'vendor_total' => 32.00,
        'created_at' => '2026-11-05 10:00:00',
    ], $positionAttributes));

    return [$product, $head, $position, $seller, $buyer];
}

it('zeigt der Verkäuferin Titel, Kategorie und Bild eines gelöschten Produkts', function () {
    [$product, , , $seller] = soldProduct();
    $product->delete();

    $this->actingAs($seller)
        ->get('/seller/dashboard/sales')
        ->assertOk()
        ->assertSee('Rosa Spitzenslip')
        ->assertSee('Slips')
        ->assertSee('products/verkauft.jpg');
});

it('nimmt den beim Kauf gespeicherten Titel, auch wenn das Produkt umbenannt wurde', function () {
    [$product, , , $seller] = soldProduct(['product_name' => 'Rosa Spitzenslip']);
    $product->update(['name' => 'Neuer Titel']);
    $product->delete();

    $this->actingAs($seller)
        ->get('/seller/dashboard/sales')
        ->assertSee('Rosa Spitzenslip')
        ->assertDontSee('Neuer Titel');
});

it('führt das gelöschte Produkt auf der Gutschrift auf', function () {
    [$product, , $position, $seller] = soldProduct();
    $product->delete();

    $this->actingAs($seller)
        ->get('/invoice/'.$position->id)
        ->assertOk()
        ->assertSee('Gutschrift')
        ->assertSee('Rosa Spitzenslip');
});

it('zeigt der Käuferin das gelöschte Produkt in ihren Käufen', function () {
    [$product, , , , $buyer] = soldProduct();
    $product->delete();

    $this->actingAs($buyer)
        ->get('/buyer/dashboard/orders')
        ->assertOk()
        ->assertSee('Rosa Spitzenslip')
        ->assertSee('products/verkauft.jpg');
});

it('behält beim Löschen die Bilder eines verkauften Produkts', function () {
    [$product, , , $seller] = soldProduct();

    $this->actingAs($seller)
        ->post(route('seller.products.delete', $product))
        ->assertRedirect(route('seller.products'));

    expect(Product::find($product->id))->toBeNull()
        ->and(Storage::exists('products/verkauft.jpg'))->toBeTrue();
});

it('räumt die Bilder eines nie verkauften Produkts weiterhin ab', function () {
    $seller = UploadTestHelpers::seller();
    Storage::put('products/unverkauft.jpg', 'bild');

    $product = Product::create([
        'name' => 'Nie verkauft',
        'slug' => 'nie-verkauft',
        'user_id' => $seller->id,
        'category_id' => UploadTestHelpers::category()->id,
        'price' => 10.00,
        'image' => 'products/unverkauft.jpg',
    ]);

    $this->actingAs($seller)->post(route('seller.products.delete', $product));

    expect(Storage::exists('products/unverkauft.jpg'))->toBeFalse();
});

it('lässt eine Verkäuferin keine fremden Produkte löschen', function () {
    [$product] = soldProduct();

    $this->actingAs(UploadTestHelpers::seller())
        ->post(route('seller.products.delete', $product))
        ->assertForbidden();

    expect(Product::find($product->id))->not->toBeNull();
});

it('setzt ein gelöschtes Produkt bei einem Storno nicht wieder aktiv', function () {
    [$product, , $position] = soldProduct();
    $product->update(['status' => 0, 'quantity' => 0]);
    $product->delete();

    ProductStock::releaseSale($position);

    $product = Product::withTrashed()->find($product->id);
    expect($product->getRawOriginal('status'))->toBe(0)
        ->and((int) $product->quantity)->toBe(0);
});
