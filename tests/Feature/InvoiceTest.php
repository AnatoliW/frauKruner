<?php

use App\Order;
use Illuminate\Support\Facades\View;
use Tests\Concerns\UsesUploadSchema;
use Tests\Support\UploadTestHelpers;

/**
 * Ein Beleg je Empfänger:
 *
 *  - Der Käufer bekommt EINE Rechnung für seine Bestellung, mit allen Artikeln
 *    darauf. Rechnungsstellerin ist Frau Kruner, bezahlt wurde die Bestellung
 *    als Ganzes – die Rechnungsnummer ist deshalb die Bestellnummer.
 *  - Jede Herstellerin bekommt eine Gutschrift für ihre Position, mit eigener
 *    Nummer.
 *
 * Vorher bekam der Käufer eine Rechnung je Artikel, jede mit einer Nummer, die
 * er nie bezahlt hatte.
 */
uses(UsesUploadSchema::class);

beforeEach(function () {
    // Das echte layouts.app zieht Menues, Einstellungen und Zahlungssymbole aus
    // Tabellen, die es im schlanken Test-Schema nicht gibt. Geprueft wird hier
    // der Beleg, nicht das Seitengeruest - wie in MicropaymentNotificationTest.
    View::getFinder()->prependLocation(__DIR__.'/../Support/views');
});

/**
 * @return array{0: Order, 1: \Illuminate\Support\Collection<int, Order>, 2: \App\Models\User}
 */
function invoiceOrder(int $positions = 2, array $headAttributes = []): array
{
    $buyer = UploadTestHelpers::buyer();

    $head = Order::create(array_merge([
        'user_id' => $buyer->id,
        'first_name' => 'Käuferin',
        'last_name' => 'Test',
        'email' => $buyer->email,
        'street' => 'Teststraße',
        'house_no' => '1',
        'zip' => '10115',
        'federal_state' => 'Berlin',
        'subtotal' => 40.00 * $positions,
        'total' => 40.00 * $positions,
        'payment_status' => 1,
        'status' => 1,
    ], $headAttributes));

    $children = collect(range(1, $positions))->map(function (int $i) use ($head, $buyer) {
        $vendor = UploadTestHelpers::seller();

        $product = \App\Product::create([
            'name' => "Artikel {$i} der Bestellung {$head->id}",
            'slug' => 'artikel-'.$i.'-'.$head->id,
            'user_id' => $vendor->id,
            'price' => 40.00,
            'quantity' => 1,
            'status' => 1,
        ]);

        return Order::create([
            'parent_id' => $head->id,
            'user_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'first_name' => 'Käuferin',
            'last_name' => 'Test',
            'email' => $buyer->email,
            'subtotal' => 40.00,
            'total' => 40.00,
            'vendor_total' => 34.00,
            'commission' => 6.00,
            'payment_status' => 1,
            'status' => 1,
        ]);
    });

    return [$head->fresh(), $children, $buyer];
}

it('zeigt dem Käufer eine Rechnung mit allen Artikeln der Bestellung', function () {
    [$head, $children, $buyer] = invoiceOrder(2);

    $response = $this->actingAs($buyer)->get('/invoice/'.$head->id);

    $response->assertOk()
        ->assertSee('Rechnung')
        // Die Rechnungsnummer ist die Bestellnummer – die vom Kontoauszug.
        ->assertSee($head->invoiceNumber());

    foreach ($children as $child) {
        $response->assertSee($child->product->name);
    }
});

it('gibt der Rechnung genau eine Nummer und nicht eine je Artikel', function () {
    [$head, $children, $buyer] = invoiceOrder(2);

    $html = $this->actingAs($buyer)->get('/invoice/'.$head->id)->getContent();

    // Der eigentliche Fehler vorher: je Artikel eine eigene Rechnungsnummer.
    foreach ($children as $child) {
        expect($html)->not->toMatch('/Rechnungs-Nr\.?:?\s*FK\d{4}-'.$child->id.'\b/');
    }

    expect($html)->toContain($head->invoiceNumber());
});

it('führt den Käufer von einer Artikel-Nummer zur Rechnung der Bestellung', function () {
    [$head, $children, $buyer] = invoiceOrder(2);

    // Ältere Links und Listen tragen die ID einer Position.
    $this->actingAs($buyer)
        ->get('/invoice/'.$children->first()->id)
        ->assertOk()
        ->assertSee($head->invoiceNumber());
});

it('rechnet den Gutschein auf der Rechnung genau einmal ab', function () {
    // Im Kopf der Bestellung ist der Rabatt bereits abgezogen.
    [$head, , $buyer] = invoiceOrder(2, [
        'subtotal' => 80.00,
        'discount' => 15.00,
        'discount_code' => 'SOMMER',
        'total' => 65.00,
    ]);

    $html = $this->actingAs($buyer)->get('/invoice/'.$head->id)->getContent();

    expect($html)
        ->toContain('SOMMER')
        ->toContain('Zwischensumme')
        // 80 − 15 = 65, nicht 50.
        ->toContain('65.00')
        ->not->toContain('50.00');
});

it('zeigt der Herstellerin ihre Gutschrift und nicht die der anderen', function () {
    [, $children] = invoiceOrder(2);

    $meine = $children->first();
    $andere = $children->last();
    $vendor = \App\Models\User::find($meine->vendor_id);

    $response = $this->actingAs($vendor)->get('/invoice/'.$meine->id);

    $response->assertOk()
        ->assertSee('Gutschrift')
        ->assertSee($meine->gutschriftNumber())
        ->assertSee($meine->product->name)
        // Die Nachbarposition derselben Bestellung gehört ihr nicht.
        ->assertDontSee($andere->product->name);
});

it('weist eine fremde Kundin ab', function () {
    [$head] = invoiceOrder(2);

    $fremde = UploadTestHelpers::buyer();

    // Die Adresse war vorher nur durch `auth` geschuetzt: Wer angemeldet war,
    // konnte ueber eine geratene Nummer fremde Namen und Adressen lesen.
    $this->withoutExceptionHandling()->actingAs($fremde);

    expect(fn () => $this->get('/invoice/'.$head->id))
        ->toThrow(Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
});

it('weist eine fremde Herstellerin ab', function () {
    [, $children] = invoiceOrder(2);

    $fremde = UploadTestHelpers::seller();

    $this->withoutExceptionHandling()->actingAs($fremde);

    expect(fn () => $this->get('/invoice/'.$children->first()->id))
        ->toThrow(Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
});

it('weist die Nachbarposition derselben Bestellung ab', function () {
    [, $children] = invoiceOrder(2);

    // Zwei Herstellerinnen in einer Bestellung: Keine darf die Gutschrift der
    // anderen oeffnen, obwohl beide zur selben Bestellung gehoeren.
    $vendor = \App\Models\User::find($children->first()->vendor_id);

    $this->withoutExceptionHandling()->actingAs($vendor);

    expect(fn () => $this->get('/invoice/'.$children->last()->id))
        ->toThrow(Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
});

it('lässt den Adminbereich jeden Beleg sehen', function () {
    [$head] = invoiceOrder(2);

    $admin = UploadTestHelpers::user(['role_id' => 1]);

    $this->actingAs($admin)
        ->get('/invoice/'.$head->id)
        ->assertOk()
        ->assertSee($head->invoiceNumber());
});

it('zeigt eine Bestellung ohne Positionen wie bisher', function () {
    $buyer = UploadTestHelpers::buyer();

    $order = Order::create([
        'user_id' => $buyer->id,
        'first_name' => 'Käuferin',
        'last_name' => 'Test',
        'email' => $buyer->email,
        'total' => 20.00,
        'subtotal' => 20.00,
        'payment_status' => 1,
        'status' => 1,
    ]);

    $this->actingAs($buyer)
        ->get('/invoice/'.$order->id)
        ->assertOk()
        ->assertSee($order->invoiceNumber());
});

/**
 * Storno: Storniert wird je Position, denn es betrifft eine Herstellerin, die
 * nicht geliefert hat. Der Bestellkopf blieb dabei unberuehrt – in der Datenbank
 * standen 138 stornierte Positionen und kein einziger stornierter Kopf. Die
 * Belege lesen den Stornostand deshalb aus den Positionen.
 */
it('markiert die Rechnung als storniert, wenn alle Positionen storniert sind', function () {
    [$head, $children, $buyer] = invoiceOrder(2);

    foreach ($children as $child) {
        $child->update(['status' => 3]);
    }

    $html = $this->actingAs($buyer)->get('/invoice/'.$head->id)->assertOk()->getContent();

    expect($html)
        ->toContain('RECHNUNG WURDE STORNIERT')
        // Der rote Balken der Kundenansicht.
        ->toContain('card-body storniert');
});

it('weist einen Teilstorno auf der Rechnung aus, ohne sie zu entwerten', function () {
    [$head, $children, $buyer] = invoiceOrder(2);

    $storniert = $children->first();
    $storniert->update(['status' => 3]);

    $html = $this->actingAs($buyer)->get('/invoice/'.$head->id)->assertOk()->getContent();
    $text = preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html)));

    // Die Rechnung gilt weiter – nur nicht für jeden Artikel.
    expect($text)
        ->toContain('1 von 2')
        ->toContain('Artikeln dieser Bestellung wurden storniert')
        ->not->toContain('RECHNUNG WURDE STORNIERT');

    // Und man sieht, welcher Artikel betroffen ist.
    expect($html)->toContain('text-decoration-line-through');
});

it('markiert die Gutschrift der betroffenen Herstellerin als storniert', function () {
    [, $children] = invoiceOrder(2);

    $storniert = $children->first();
    $storniert->update(['status' => 3]);

    $vendor = \App\Models\User::find($storniert->vendor_id);

    $this->actingAs($vendor)
        ->get('/invoice/'.$storniert->id)
        ->assertOk()
        ->assertSee('GUTSCHRIFT WURDE STORNIERT', false);
});

it('lässt die Gutschrift der anderen Herstellerin unberührt', function () {
    [, $children] = invoiceOrder(2);

    $children->first()->update(['status' => 3]);

    $offen = $children->last();
    $vendor = \App\Models\User::find($offen->vendor_id);

    $this->actingAs($vendor)
        ->get('/invoice/'.$offen->id)
        ->assertOk()
        ->assertDontSee('GUTSCHRIFT WURDE STORNIERT', false);
});

it('setzt den Bestellkopf auf storniert, sobald die letzte Position storniert ist', function () {
    [$head, $children] = invoiceOrder(2);

    // Erste Position storniert: Die Bestellung lebt weiter.
    $children->first()->update(['status' => 3]);
    $children->first()->syncCancellation();

    expect((int) $head->fresh()->status)->not->toBe(3);

    // Zweite Position storniert: Jetzt ist nichts mehr offen.
    $children->last()->update(['status' => 3]);
    $children->last()->syncCancellation();

    expect((int) $head->fresh()->status)->toBe(3);
});

it('zieht bei einer Bestellung ohne Positionen nichts nach', function () {
    $order = Order::create([
        'email' => 'kundin@example.test',
        'total' => 20.00,
        'status' => 3,
    ]);

    // Darf nicht scheitern und nichts anderes anfassen.
    $order->syncCancellation();

    expect((int) $order->fresh()->status)->toBe(3);
});
