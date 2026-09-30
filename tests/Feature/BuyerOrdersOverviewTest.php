<?php

use App\Order;
use Illuminate\Support\Facades\View;
use Tests\Concerns\UsesUploadSchema;
use Tests\Support\UploadTestHelpers;

/**
 * „Meine Käufe“ zeigt Bestellungen, nicht Artikel.
 *
 * Vorher stand eine Bestellung mit zwei Artikeln zweimal in der Liste – mit zwei
 * Beträgen und zwei Rechnungs-Knöpfen. Die Kundin hat aber eine Bestellung
 * aufgegeben, einmal bezahlt und bekommt eine Rechnung; die Artikel hängen als
 * Positionen darin.
 */
uses(UsesUploadSchema::class);

beforeEach(function () {
    // Das echte Dashboard-Layout liest Menues und Einstellungen aus Tabellen, die
    // das schlanke Testschema nicht traegt.
    View::getFinder()->prependLocation(__DIR__.'/../Support/views');
});

/**
 * @return array{0: Order, 1: \Illuminate\Support\Collection<int, Order>, 2: \App\Models\User}
 */
function buyerOrder(int $positions = 2, array $headAttributes = []): array
{
    $buyer = UploadTestHelpers::buyer();
    $category = UploadTestHelpers::category();

    $head = Order::create(array_merge([
        'user_id' => $buyer->id,
        'first_name' => 'Käuferin',
        'last_name' => 'Test',
        'email' => $buyer->email,
        'subtotal' => 40.00 * $positions,
        'total' => 40.00 * $positions,
        'payment_status' => 1,
        'status' => 1,
        'payment_gateway' => 'micropayment',
    ], $headAttributes));

    $children = collect(range(1, $positions))->map(function (int $i) use ($head, $buyer, $category) {
        $vendor = UploadTestHelpers::seller();

        $product = \App\Product::create([
            'name' => "Artikel {$i} der Bestellung {$head->id}",
            'slug' => 'artikel-'.$i.'-'.$head->id,
            'user_id' => $vendor->id,
            'category_id' => $category->id,
            'price' => 40.00,
            'quantity' => 1,
            'status' => 1,
        ]);

        return Order::create([
            'parent_id' => $head->id,
            'user_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'email' => $buyer->email,
            'subtotal' => 40.00,
            'total' => 40.00,
            'payment_status' => 1,
            'status' => 1,
            'payment_gateway' => 'micropayment',
        ]);
    });

    return [$head->fresh(), $children, $buyer];
}

it('zeigt eine Bestellung mit zwei Artikeln nur einmal', function () {
    [$head, $children, $buyer] = buyerOrder(2);

    $html = $this->actingAs($buyer)->get('/buyer/dashboard/orders')->assertOk()->getContent();

    // Der eigentliche Fehler vorher: zwei Einträge für eine Bestellung.
    expect(substr_count($html, 'Bestellung '.$head->orderNumber()))->toBe(1);

    // Die Artikel stehen darin.
    foreach ($children as $child) {
        expect($html)->toContain($child->product->name);
    }
});

it('nennt den Gesamtbetrag der Bestellung und verlinkt eine Rechnung', function () {
    [$head, $children, $buyer] = buyerOrder(2);

    $html = $this->actingAs($buyer)->get('/buyer/dashboard/orders')->getContent();

    // Genau ein Rechnungslink, und der zeigt auf die Bestellung.
    expect(substr_count($html, '/invoice/'.$head->id))->toBe(1);

    foreach ($children as $child) {
        expect($html)->not->toContain('/invoice/'.$child->id);
    }
});

it('zieht den Gutschein im Gesamtbetrag nur einmal ab', function () {
    // Im Kopf der Bestellung ist der Rabatt bereits abgezogen.
    [$head, , $buyer] = buyerOrder(2, [
        'subtotal' => 80.00,
        'discount' => 15.00,
        'discount_code' => 'SOMMER',
        'total' => 65.00,
    ]);

    $html = $this->actingAs($buyer)->get('/buyer/dashboard/orders')->getContent();

    expect($html)
        ->toContain('SOMMER')
        ->toContain('65.00')
        // Nicht 80,00 (Positionssumme) und nicht 50,00 (doppelt abgezogen).
        ->not->toContain('50.00');
});

it('führt eine Bestellung mit einem Artikel genauso auf', function () {
    [$head, $children, $buyer] = buyerOrder(1);

    $html = $this->actingAs($buyer)->get('/buyer/dashboard/orders')->assertOk()->getContent();

    expect(substr_count($html, 'Bestellung '.$head->orderNumber()))->toBe(1)
        ->and($html)->toContain($children->first()->product->name);

    // Bei einem Artikel keine Artikelzahl im Kopf.
    expect($html)->not->toContain('1 Artikel');
});

it('zeigt eine Bestellung ohne Positionen wie bisher', function () {
    $buyer = UploadTestHelpers::buyer();
    $vendor = UploadTestHelpers::seller();
    $category = UploadTestHelpers::category();

    $product = \App\Product::create([
        'name' => 'Einzelner Altartikel',
        'slug' => 'einzelner-altartikel',
        'user_id' => $vendor->id,
        'category_id' => $category->id,
        'price' => 20.00,
        'status' => 1,
    ]);

    $order = Order::create([
        'user_id' => $buyer->id,
        'vendor_id' => $vendor->id,
        'product_id' => $product->id,
        'email' => $buyer->email,
        'total' => 20.00,
        'subtotal' => 20.00,
        'payment_status' => 1,
        'status' => 1,
    ]);

    $html = $this->actingAs($buyer)->get('/buyer/dashboard/orders')->assertOk()->getContent();

    expect($html)
        ->toContain('Bestellung '.$order->orderNumber())
        ->toContain('Einzelner Altartikel');
});
