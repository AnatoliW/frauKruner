<?php

use App\Order;
use Illuminate\Support\Facades\View;
use Tests\Concerns\UsesUploadSchema;
use Tests\Support\UploadTestHelpers;

/**
 * Eine Rechnung je Bestellung – Belegnummern je Position.
 *
 * Der Käufer hat einmal bezahlt und bekommt einen Beleg: eine Rechnung für die
 * ganze Bestellung, mit allen Artikeln darauf. Sie trägt keine eigene Nummer.
 * Sie nennt die Bestellnummer als Bezug – die Nummer vom Kontoauszug – und
 * führt je Artikel dessen Belegnummer auf.
 *
 * Die Belegnummer gilt je Position (`FK2024-3552`), denn jede Herstellerin
 * rechnet einzeln ab. Ab dem eigenen Gutschrift-Stichtag wird je Herstellerin
 * gebündelt, mit Hauptbestellnummer plus Nutzer-ID. Ältere Gutschriften bleiben erhalten.
 *
 * Die Sammelrechnung gilt erst ab dem Stichtag aus
 * `app.invoice_bundle_cutoff_date`. Davor bekam jeder Artikel ein eigenes
 * Rechnungsblatt mit seiner Belegnummer in der Kopfzeile
 * (`Rechnungs-Nr. FK2024-3552`). Diese Blätter sind ausgestellt, verschickt und
 * archiviert – sie müssen sich unverändert wieder erzeugen lassen.
 *
 * Zwischen dem 30.09.2026 und dem Rückbau wurde die Rechnung mit der
 * Bestellnummer als Rechnungsnummer ausgestellt. Damit änderten sich
 * rückwirkend alle Belegnummern – aus `FK2024-3552` wurde `FK2024-3550`, und
 * archivierte Belege stimmten nicht mehr. Die Tests hier halten fest: die
 * Nummer gilt je Position, sie steht fest in der Datenbank statt bei jedem
 * Aufruf neu gerechnet zu werden, die Sammelrechnung erfindet keine neue, und
 * vor dem Stichtag kommt das alte Blatt unverändert zurück.
 */
uses(UsesUploadSchema::class);

beforeEach(function () {
    // Das echte layouts.app zieht Menues, Einstellungen und Zahlungssymbole aus
    // Tabellen, die es im schlanken Test-Schema nicht gibt. Geprueft wird hier
    // der Beleg, nicht das Seitengeruest - wie in MicropaymentNotificationTest.
    View::getFinder()->prependLocation(__DIR__.'/../Support/views');

    // Den Stichtag festnageln: Sonst entscheidet die .env des Rechners, welche
    // Belegform die Tests sehen.
    config(['app.invoice_bundle_cutoff_date' => '2026-09-30']);
    config(['app.seller_credit_bundle_cutoff_at' => '2026-10-27T16:23:47+01:00']);
    config(['app.seller_credit_position_cutoff_at' => '2026-10-28T12:00:00+01:00']);
});

/**
 * @return array{0: Order, 1: \Illuminate\Support\Collection<int, Order>, 2: \App\Models\User}
 */
function invoiceOrder(int $positions = 2, array $headAttributes = [], array $positionAttributes = []): array
{
    $buyer = UploadTestHelpers::buyer();
    $category = UploadTestHelpers::category();

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
        // Nach dem Stichtag, also Sammelrechnung. Tests für die Zeit davor
        // setzen 'created_at' über $headAttributes.
        'created_at' => '2026-10-05 10:00:00',
        'updated_at' => '2026-10-05 10:00:00',
    ], $headAttributes));

    $children = collect(range(1, $positions))->map(function (int $i) use ($head, $buyer, $category, $positionAttributes) {
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

        return Order::create(array_merge([
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
            'created_at' => $head->created_at,
            'updated_at' => $head->created_at,
        ], $positionAttributes));
    });

    return [$head->fresh(), $children, $buyer];
}

/*
|--------------------------------------------------------------------------
| Die Belegnummer
|--------------------------------------------------------------------------
*/

it('schreibt die Belegnummer beim Anlegen fest in die Datenbank', function () {
    [$head, $children] = invoiceOrder(2);

    // Eine Belegnummer ist ein feststehender Wert, keine Formel: Nur so kann
    // eine spaetere Formataenderung archivierte Belege nicht mehr umschreiben.
    foreach ($children as $child) {
        expect($child->fresh()->invoice_no)->toBe('FK'.$child->created_at->format('Y').'-'.$child->id);
    }

    expect($head->fresh()->invoice_no)->toBe('FK'.$head->created_at->format('Y').'-'.$head->id);
});

it('liest die Belegnummer aus der Spalte und rechnet sie nicht neu', function () {
    [, $children] = invoiceOrder(1);

    $position = $children->first();

    // Steht eine Nummer in der Spalte, gilt sie – auch wenn die Formel heute
    // etwas anderes ergeben wuerde. Genau das schuetzt den Altbestand bei einem
    // spaeteren Formatwechsel.
    $position->forceFill(['invoice_no' => 'FK2024-99999'])->save();

    expect($position->fresh()->invoiceNumber())->toBe('FK2024-99999');
});

it('gibt die Belegnummer je Position und nicht die der Bestellung', function () {
    [$head, $children] = invoiceOrder(2);

    foreach ($children as $child) {
        expect($child->invoiceNumber())
            ->toBe('FK'.$child->created_at->format('Y').'-'.$child->id)
            ->not->toBe($head->orderNumber());
    }

    // Bei mehreren Artikeln hat jeder seine eigene Nummer.
    expect($children[0]->invoiceNumber())->not->toBe($children[1]->invoiceNumber());
});

it('baut die Gutschrift auf der Hauptbestellnummer und der Verkäuferinnen-ID auf', function () {
    [$head, $children] = invoiceOrder(1, [
        'id' => 5146,
        'created_at' => '2026-10-27 15:23:47',
    ], ['id' => 5147, 'vendor_id' => 5186]);

    $position = $children->first();

    expect($head->orderNumber())->toBe('FK2026-5146');
    expect($position->gutschriftNumber())
        ->toBe('FK2026-5146-5186')
        ->not->toBe('FK2026-5147-5186');

    // Die gespeicherte Belegnummer der Position bleibt erhalten.
    expect($position->fresh()->invoice_no)->toBe('FK2026-5147');
});

it('nimmt für die Gutschrift auch am Jahreswechsel das Jahr der Hauptbestellung', function () {
    [$head, $children] = invoiceOrder(1, [
        'created_at' => '2026-12-31 23:59:59',
    ], [
        'created_at' => '2027-01-01 00:00:01',
    ]);

    $position = $children->first();

    expect($position->gutschriftNumber())
        ->toBe('FK2026-'.$head->id.'-'.$position->vendor_id);
});

it('verwendet für eine Gutschrift ohne Hauptbestellung die eigene Bestellnummer', function () {
    $order = Order::create([
        'vendor_id' => 5186,
        'created_at' => '2026-10-05 10:00:00',
    ]);

    expect($order->gutschriftNumber())->toBe('FK2026-'.$order->id.'-5186');
});

/*
|--------------------------------------------------------------------------
| Die Belege
|--------------------------------------------------------------------------
*/

it('zeigt dem Käufer eine Rechnung mit allen Artikeln der Bestellung', function () {
    [$head, $children, $buyer] = invoiceOrder(2);

    $response = $this->actingAs($buyer)->get('/invoice/'.$head->id);

    $response->assertOk()
        ->assertSee('Rechnung')
        // Die Bestellnummer als Bezug: die Nummer vom Kontoauszug.
        ->assertSee($head->orderNumber());

    foreach ($children as $child) {
        $response->assertSee($child->product->name)
            // Je Artikel seine Belegnummer.
            ->assertSee($child->invoiceNumber());
    }
});

it('erfindet für die Rechnung keine eigene Nummer', function () {
    [$head, $children, $buyer] = invoiceOrder(2);

    $html = $this->actingAs($buyer)->get('/invoice/'.$head->id)->assertOk()->getContent();

    // Keine „Rechnungs-Nr." auf dem Blatt: Das Dokument verweist auf die
    // Bestellung und führt die Belegnummern der Artikel auf. Jede Zahl darauf
    // hatte vorher schon dieselbe Bedeutung – deshalb kann sich rückwirkend
    // nichts verschieben.
    expect($html)
        ->not->toContain('Rechnungs-Nr')
        ->toContain('zur Bestellung')
        ->toContain('Beleg-Nr.');

    foreach ($children as $child) {
        expect($html)->toContain($child->invoiceNumber());
    }
});

it('führt den Käufer von einer Artikel-Nummer zur Rechnung der Bestellung', function () {
    [$head, $children, $buyer] = invoiceOrder(2);

    // Ältere Links und Listen tragen die ID einer Position. Die Rechnung gilt
    // aber für die Bestellung, also wird darauf aufgelöst.
    $this->actingAs($buyer)
        ->get('/invoice/'.$children->first()->id)
        ->assertOk()
        ->assertSee($head->orderNumber())
        ->assertSee($children->last()->product->name);
});

it('rechnet den Gutschein auf der Rechnung genau einmal ab', function () {
    // Im Kopf der Bestellung ist der Rabatt bereits abgezogen. Die Positionen
    // tragen nur ihren Anteil – auf der Sammelrechnung darf er trotzdem nur
    // einmal erscheinen.
    [$head, , $buyer] = invoiceOrder(2, [
        'subtotal' => 80.00,
        'discount' => 15.00,
        'discount_code' => 'SOMMER',
        'total' => 65.00,
    ], [
        'discount' => 7.50,
        'discount_code' => 'SOMMER',
    ]);

    $html = $this->actingAs($buyer)->get('/invoice/'.$head->id)->getContent();

    expect($html)
        ->toContain('SOMMER')
        ->toContain('Zwischensumme')
        // 80 − 15 = 65, nicht 50 und nicht 72,50.
        ->toContain('65.00')
        ->not->toContain('50.00');
});

it('zeigt der Herstellerin ihre Gutschrift und nicht die der anderen', function () {
    [$head, $children] = invoiceOrder(2);

    $meine = $children->first();
    $andere = $children->last();
    $vendor = \App\Models\User::find($meine->vendor_id);

    $response = $this->actingAs($vendor)->get('/invoice/'.$meine->id);

    $response->assertOk()
        ->assertSee('Gutschrift')
        ->assertSee($meine->invoiceNumber().'-'.$meine->vendor_id)
        ->assertDontSee($head->orderNumber().'-'.$meine->vendor_id)
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

it('weist eine fremde Kundin auch über eine Positions-ID ab', function () {
    [, $children] = invoiceOrder(2);

    $fremde = UploadTestHelpers::buyer();

    // Aufgelöst wird auf die Bestellung – geprüft wird deren Eigentümerin.
    $this->withoutExceptionHandling()->actingAs($fremde);

    expect(fn () => $this->get('/invoice/'.$children->first()->id))
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
    [$head, $children] = invoiceOrder(2);

    $admin = UploadTestHelpers::user(['role_id' => 1]);

    $this->actingAs($admin)
        ->get('/invoice/'.$head->id)
        ->assertOk()
        ->assertSee($head->orderNumber())
        ->assertSee($children->first()->invoiceNumber());
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

    // Ein Altdatensatz ohne Positionen ist seine eigene einzige Position.
    $this->actingAs($buyer)
        ->get('/invoice/'.$order->id)
        ->assertOk()
        ->assertSee($order->orderNumber())
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

    $children->first()->update(['status' => 3]);

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

/*
|--------------------------------------------------------------------------
| Der Stichtag: Belege von vor der Sammelrechnung
|--------------------------------------------------------------------------
|
| Vor dem Stichtag hat jeder Artikel ein eigenes Rechnungsblatt bekommen, mit
| seiner Belegnummer in der Kopfzeile. Diese Blätter liegen im Archiv der
| Betreiberin und müssen sich unverändert wieder erzeugen lassen.
*/

it('gibt einer Bestellung vor dem Stichtag je Artikel ein eigenes Rechnungsblatt', function () {
    [, $children, $buyer] = invoiceOrder(2, [
        'created_at' => '2024-06-12 09:00:00',
        'updated_at' => '2024-06-12 09:00:00',
    ]);

    foreach ($children as $child) {
        $html = $this->actingAs($buyer)->get('/invoice/'.$child->id)->assertOk()->getContent();

        // Die Belegnummer steht in der Kopfzeile, so wie damals ausgestellt.
        expect($html)->toMatch('/Rechnungs-Nr\.?:?\s*'.preg_quote($child->invoiceNumber(), '/').'\b/');

        // Nur dieser Artikel, nicht der Nachbarartikel.
        $andere = $children->firstWhere('id', '!=', $child->id);
        expect($html)
            ->toContain($child->product->name)
            ->not->toContain($andere->product->name);
    }
});

it('nimmt das Jahr des Belegs und nicht das des Stichtags', function () {
    [, $children, $buyer] = invoiceOrder(1, [
        'created_at' => '2024-06-12 09:00:00',
        'updated_at' => '2024-06-12 09:00:00',
    ]);

    $position = $children->first();

    expect($position->invoiceNumber())->toBe('FK2024-'.$position->id);

    $this->actingAs($buyer)
        ->get('/invoice/'.$position->id)
        ->assertOk()
        ->assertSee('FK2024-'.$position->id);
});

it('führt bei einer Bestellung vor dem Stichtag vom Kopf zum ersten Blatt', function () {
    [$head, $children, $buyer] = invoiceOrder(2, [
        'created_at' => '2024-06-12 09:00:00',
        'updated_at' => '2024-06-12 09:00:00',
    ]);

    // Der Kopf war damals kein Beleg: Seine Nummer ist die Bestellnummer und
    // stand nie auf einer Rechnung.
    $this->actingAs($buyer)
        ->get('/invoice/'.$head->id)
        ->assertRedirect(route('invoice', $children->first()));
});

it('lässt die Gutschrift der Herstellerin vom Stichtag unberührt', function () {
    [, $alt] = invoiceOrder(1, [
        'created_at' => '2024-06-12 09:00:00',
        'updated_at' => '2024-06-12 09:00:00',
    ]);
    [, $neu] = invoiceOrder(1);

    // Der Käufer-Stichtag verändert ältere Gutschriften nicht.
    foreach ([$alt->first(), $neu->first()] as $position) {
        expect($position->gutschriftNumber())
            ->toBe($position->invoiceNumber().'-'.$position->vendor_id);

        $vendor = \App\Models\User::find($position->vendor_id);

        $this->actingAs($vendor)
            ->get('/invoice/'.$position->id)
            ->assertOk()
            ->assertSee('Gutschrift')
            ->assertSee($position->gutschriftNumber());
    }
});

it('entscheidet die Belegform am Datum der Bestellung, nicht der Position', function () {
    // Haupt- und Unterbestellung entstehen in derselben Transaktion. Fallen die
    // Zeitstempel über den Stichtag, darf eine Bestellung nicht in zwei
    // Belegformen zerfallen.
    $head = Order::create([
        'payment_status' => 1,
        'created_at' => '2026-09-29 23:59:59',
        'updated_at' => '2026-09-29 23:59:59',
    ]);

    $child = Order::create([
        'parent_id' => $head->id,
        'payment_status' => 1,
        'created_at' => '2026-09-30 00:00:01',
        'updated_at' => '2026-09-30 00:00:01',
    ]);

    expect($head->usesBundledInvoice())->toBeFalse()
        ->and($child->usesBundledInvoice())->toBeFalse();
});

it('gibt einer Bestellung ab dem Stichtag die Sammelrechnung', function () {
    [$head, , $buyer] = invoiceOrder(2, [
        'created_at' => '2026-09-30 00:00:00',
        'updated_at' => '2026-09-30 00:00:00',
    ]);

    expect($head->usesBundledInvoice())->toBeTrue();

    $this->actingAs($buyer)
        ->get('/invoice/'.$head->id)
        ->assertOk()
        ->assertSee('zur Bestellung')
        ->assertDontSee('Rechnungs-Nr');
});

/** Drei Positionen: die ersten beiden gehören derselben Verkäuferin. */
function bundledSellerCreditOrder(string $createdAt = '2026-10-27 15:23:47'): array
{
    [$head, $positions, $buyer] = invoiceOrder(3, ['created_at' => $createdAt]);
    $positions[1]->update(['vendor_id' => $positions[0]->vendor_id]);

    return [$head, $positions, $buyer];
}

it('wendet neue Gutschriften exakt ab dem festen Zeitpunkt auf die Hauptbestellung an', function ($date, $bundled) {
    [$head, $positions] = bundledSellerCreditOrder($date);

    foreach ($positions as $position) {
        expect($position->usesBundledSellerCredit())->toBe($bundled)
            ->and($position->gutschriftNumber())->toBe(
                ($bundled ? $head->orderNumber() : $position->invoiceNumber()).'-'.$position->vendor_id
            );
    }

    expect($head->sellerCreditGroups())->toHaveCount($bundled ? 2 : 3);
})->with([
    'direkt davor' => ['2026-10-27 15:23:46', false],
    'genau ab Beginn' => ['2026-10-27 15:23:47', true],
]);

it('bewahrt die bisherigen Gutschriften der Beispielbestellung 5146', function () {
    [$head, $positions] = invoiceOrder(2, [
        'id' => 5146,
        'created_at' => '2026-10-09 09:04:00',
    ], ['vendor_id' => 5186]);

    expect($positions[0]->gutschriftNumber())->toBe('FK2026-5147-5186')
        ->and($positions[1]->gutschriftNumber())->toBe('FK2026-5148-5186')
        ->and($head->sellerCreditGroups())->toHaveCount(2);

    $positions[0]->update(['invoice_no' => 'FK2025-ARCHIV']);
    expect($positions[0]->fresh()->gutschriftNumber())->toBe('FK2025-ARCHIV-5186');
});

it('hält alle Positionen einer älteren Bestellung auch nach dem Zeitpunkt im alten Format', function () {
    [$head, $positions] = invoiceOrder(1, [
        'created_at' => '2026-10-27 15:23:46',
    ], ['created_at' => '2026-10-27 15:23:48']);

    expect($head->usesBundledSellerCredit())->toBeFalse()
        ->and($positions[0]->usesBundledSellerCredit())->toBeFalse()
        ->and($positions[0]->gutschriftNumber())->toBe($positions[0]->invoiceNumber().'-'.$positions[0]->vendor_id);
});

it('zeigt über beide Positionslinks dieselbe gemeinsame Gutschrift nur für die eigene Verkäuferin', function () {
    [$head, $positions] = bundledSellerCreditOrder();
    $vendor = \App\Models\User::find($positions[0]->vendor_id);
    $pages = [];

    foreach ([$positions[0], $positions[1]] as $position) {
        $response = $this->actingAs($vendor)->get('/invoice/'.$position->id)->assertOk()
            ->assertViewHas('positions', fn ($items) => $items->modelKeys() === [$positions[0]->id, $positions[1]->id])
            ->assertSee($head->orderNumber().'-'.$vendor->id)
            ->assertSee($positions[0]->product->name)
            ->assertSee($positions[1]->product->name)
            ->assertDontSee($positions[2]->product->name)
            ->assertSee('Gesamtbetrag');

        $html = $response->getContent();
        expect($html)->toContain('<strong>68.00 €</strong>');
        $pages[] = $html;
    }

    expect($pages[0])->toBe($pages[1]);
});

it('stellt für eine andere Verkäuferin einen getrennten Beleg aus und verweigert fremde Positionslinks', function () {
    [$head, $positions] = bundledSellerCreditOrder();
    $vendor = \App\Models\User::find($positions[2]->vendor_id);

    $this->actingAs($vendor)->get('/invoice/'.$positions[2]->id)->assertOk()
        ->assertSee($head->orderNumber().'-'.$vendor->id)
        ->assertSee($positions[2]->product->name)
        ->assertDontSee($positions[0]->product->name)
        ->assertDontSee($positions[1]->product->name);

    $this->withoutExceptionHandling();
    expect(fn () => $this->get('/invoice/'.$positions[0]->id))
        ->toThrow(Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
});

it('hält Gutschriften derselben Verkäuferin aus unterschiedlichen Bestellungen getrennt', function () {
    [$first, $positions] = bundledSellerCreditOrder();
    [$second, $otherPositions] = invoiceOrder(1, [
        'created_at' => '2026-10-27 15:23:48',
    ], ['vendor_id' => $positions[0]->vendor_id]);

    expect($positions[0]->sellerCreditPositions()->modelKeys())->toBe([$positions[0]->id, $positions[1]->id])
        ->and($otherPositions[0]->sellerCreditPositions()->modelKeys())->toBe([$otherPositions[0]->id])
        ->and($positions[0]->gutschriftNumber())->not->toBe($otherPositions[0]->gutschriftNumber());
});

it('zeigt eine neue Gutschrift auch ohne übergeordnete Bestellung', function () {
    [, $positions] = invoiceOrder(1, ['created_at' => '2026-10-27 15:23:47']);
    $position = $positions[0];
    $position->update(['parent_id' => null]);
    $vendor = \App\Models\User::find($position->vendor_id);

    $this->actingAs($vendor)->get('/invoice/'.$position->id)->assertOk()
        ->assertViewHas('positions', fn ($items) => $items->modelKeys() === [$position->id])
        ->assertSee('FK2026-'.$position->id.'-'.$vendor->id)
        ->assertSee('Gesamtbetrag');
});

it('vergibt ohne zugeordnete Verkäuferin keine doppelten Gutschriftnummern', function () {
    [$head, $positions] = invoiceOrder(2, ['created_at' => '2026-10-27 15:23:47'], ['vendor_id' => null]);

    expect($head->sellerCreditGroups())->toHaveCount(2)
        ->and($positions[0]->gutschriftNumber())->not->toBe($positions[1]->gutschriftNumber());
});

it('summiert Netto und Umsatzsteuer der gemeinsamen Gutschrift aus den gespeicherten Beträgen', function () {
    [, $positions] = bundledSellerCreditOrder();

    foreach ([$positions[0], $positions[1]] as $position) {
        $position->update([
            'vendor_total' => 11.90,
            'seller_info' => ['is_pay_vat' => 1],
        ]);
    }

    $vendor = \App\Models\User::find($positions[0]->vendor_id);
    $html = $this->actingAs($vendor)->get('/invoice/'.$positions[0]->id)->assertOk()->getContent();

    expect($html)->toContain('MwSt. (19 %)', '<strong>20.00 €</strong>', '<strong>3.80 €</strong>', '<strong>23.80 €</strong>');
});

it('zieht stornierte Positionen von der gemeinsamen Gutschrift ab, ohne die anderen zu stornieren', function () {
    [, $positions] = bundledSellerCreditOrder();
    $positions[0]->update(['status' => 3]);
    $vendor = \App\Models\User::find($positions[0]->vendor_id);

    $html = $this->actingAs($vendor)->get('/invoice/'.$positions[1]->id)->assertOk()->getContent();

    expect($html)->toContain('Artikeln dieser Bestellung wurden storniert', '<strong>34.00 €</strong>')
        ->not->toContain('GUTSCHRIFT WURDE STORNIERT');

    $positions[1]->update(['status' => 3]);
    $html = $this->get('/invoice/'.$positions[1]->id)->assertOk()->getContent();
    expect($html)->toContain('GUTSCHRIFT WURDE STORNIERT', '<strong>0.00 €</strong>');
});

it('bündelt Gutschriften im Adminbereich und erhält Positionsanker für Auszahlungslinks', function () {
    [$head, $positions] = bundledSellerCreditOrder();
    \App\Models\Role::create(['id' => 1, 'name' => 'admin', 'display_name' => 'Admin']);
    \Illuminate\Support\Facades\Schema::create('permissions', function ($table) {
        $table->id();
        $table->string('key');
    });
    \Illuminate\Support\Facades\Schema::create('permission_role', function ($table) {
        $table->unsignedBigInteger('permission_id');
        $table->unsignedBigInteger('role_id');
    });

    try {
        $this->actingAs(UploadTestHelpers::user(['role_id' => 1]));
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));
        $page = \Livewire\Livewire::test(\App\Filament\Resources\Orders\Pages\ViewOrder::class, [
            'record' => $head->getRouteKey(),
        ])->assertSuccessful();

        $html = $page->html();
        expect(substr_count($html, '<section class="invoice-document__section" id="seller-print-block-'))->toBe(2)
            ->and($html)->toContain('id="seller-print-block-'.$positions[1]->id.'"', '<strong>68.00 €</strong>')
            ->and(substr_count($html, 'Gutschrift-Nr. '.$positions[0]->gutschriftNumber()))->toBe(1);

        [$legacy, $legacyPositions] = bundledSellerCreditOrder('2026-10-09 09:04:00');
        $legacyHtml = \Livewire\Livewire::test(\App\Filament\Resources\Orders\Pages\ViewOrder::class, [
            'record' => $legacy->getRouteKey(),
        ])->assertSuccessful()->html();
        expect(substr_count($legacyHtml, '<section class="invoice-document__section" id="seller-print-block-'))->toBe(3);

        foreach ($legacyPositions as $position) {
            expect($legacyHtml)->toContain($position->invoiceNumber().'-'.$position->vendor_id);
        }

        $list = \Livewire\Livewire::test(\App\Filament\Resources\Orders\Pages\ListOrders::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$head, $legacy]);
        $articles = $list->instance()->getTable()->getColumn('articles');
        $newState = $articles->record($head)->getState();
        expect($newState)->toContain('Gutschrift-Nr. '.$positions[0]->gutschriftNumber())
            ->not->toContain('Beleg-Nr. '.$positions[0]->invoiceNumber());
        expect(substr_count($newState, 'Gutschrift-Nr. '.$positions[0]->gutschriftNumber()))->toBe(1);
        $articles->clearCachedState();
        expect($articles->record($legacy)->getState())->toContain('Beleg-Nr. '.$legacyPositions[0]->invoiceNumber())
            ->not->toContain('Gutschrift-Nr.');

        // Die bestehende Namenssuche nutzt MySQLs CONCAT_WS; für SQLite nachbilden.
        \Illuminate\Support\Facades\DB::connection()->getPdo()->sqliteCreateFunction('CONCAT_WS',
            fn ($separator, ...$values) => implode($separator, array_filter($values, fn ($value) => $value !== null))
        );

        $payouts = \Livewire\Livewire::test(\App\Filament\Resources\Payouts\Pages\ListPayouts::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($positions->merge($legacyPositions));
        $numberColumn = $payouts->instance()->getTable()->getColumn('id');
        expect($numberColumn->getLabel())->toBe('Gutschrift-Nr.');

        foreach ($positions->merge($legacyPositions) as $position) {
            $numberColumn->clearCachedState();
            expect($numberColumn->record($position)->getState())
                ->toContain($position->gutschriftNumber(), $position->orderNumber());
        }

        $unpaid = Order::create(['parent_id' => $head->id, 'vendor_id' => $positions[0]->vendor_id, 'payment_status' => 0, 'status' => 1]);
        $cancelled = Order::create(['parent_id' => $head->id, 'vendor_id' => $positions[0]->vendor_id, 'payment_status' => 1, 'status' => 3]);

        $payouts->searchTable($positions[0]->gutschriftNumber())
            ->assertCanSeeTableRecords([$positions[0], $positions[1]])
            ->assertCanNotSeeTableRecords([$positions[2], $unpaid, $cancelled, ...$legacyPositions]);
        $payouts->searchTable($legacyPositions[1]->gutschriftNumber())
            ->assertCanSeeTableRecords([$legacyPositions[1]])
            ->assertCanNotSeeTableRecords([$legacyPositions[0], $legacyPositions[2], ...$positions]);
        $payouts->searchTable($head->orderNumber())->assertCanSeeTableRecords($positions);

        // Ein kopierter Suchlink darf nicht zusätzlich vom alten scopeFilter gefiltert werden.
        \Livewire\Livewire::withQueryParams(['search' => $positions[0]->gutschriftNumber()])
            ->test(\App\Filament\Resources\Payouts\Pages\ListPayouts::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$positions[0], $positions[1]])
            ->assertCanNotSeeTableRecords([$positions[2], $unpaid, $cancelled, ...$legacyPositions]);
    } finally {
        \Illuminate\Support\Facades\Schema::dropIfExists('permission_role');
        \Illuminate\Support\Facades\Schema::dropIfExists('permissions');
    }
});

it('zeigt in Verkäufen und Auszahlungen dieselbe Gutschrift- und Hauptbestellnummer wie auf dem Beleg', function ($date) {
    [$head, $positions] = bundledSellerCreditOrder($date);
    $vendor = \App\Models\User::find($positions[0]->vendor_id);
    $this->actingAs($vendor);

    foreach (['seller.sales', 'seller.payments'] as $route) {
        $response = $this->get(route($route))->assertOk();
        $text = preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($response->getContent())));

        foreach ([$positions[0], $positions[1]] as $position) {
            expect($text)->toContain('Bestellnummer: '.$head->orderNumber(), 'Gutschrift-Nr.: '.$position->gutschriftNumber());
            $this->get(route('invoice', $position))->assertOk()->assertSee($position->gutschriftNumber());
        }

        expect($text)->not->toContain($positions[2]->product->name);
    }
})->with(['2026-10-09 09:04:00', '2026-10-27 15:23:47']);

it('verwendet für die Versandbestätigung aus dem Dashboard die Hauptbestellnummer', function () {
    \Illuminate\Support\Facades\Mail::fake();
    [, $positions] = bundledSellerCreditOrder();
    $position = $positions[0];
    $vendor = \App\Models\User::find($position->vendor_id);

    $this->actingAs($vendor)->post(route('seller.order.update', $position), [
        'shipping_method' => 'DHL',
        'shipping_date' => '2026-10-28',
        'tracking_Id' => 'TEST-123',
    ])->assertRedirect()->assertSessionHasNoErrors();

    \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\UserNotifyEmail::class, function ($mail) use ($position) {
        expect($mail->data->subject)->toBe('Versandbestätigung '.$position->orderNumberWithPosition())
            ->and($mail->data->body)->toContain('Bestellnummer '.$position->orderNumberWithPosition());

        return true;
    });
});

/*
|--------------------------------------------------------------------------
| Ab app.seller_credit_position_cutoff_at: Gutschrift je Artikel mit Pos.
|--------------------------------------------------------------------------
*/

const POSITION_CREDIT_DATE = '2026-10-28 11:00:00';

it('stellt Gutschriften exakt ab dem zweiten Zeitpunkt wieder je Artikel aus', function ($date, $neu) {
    [$head, $positions] = bundledSellerCreditOrder($date);
    $vendor = $positions[0]->vendor_id;

    expect($head->usesPositionSellerCredit())->toBe($neu)
        ->and($head->sellerCreditGroups())->toHaveCount($neu ? 3 : 2)
        ->and(array_map(fn ($p) => $p->gutschriftNumber(), $positions->all()))->toBe([
            $head->orderNumber().'-'.$vendor.($neu ? '-1' : ''),
            $head->orderNumber().'-'.$vendor.($neu ? '-2' : ''),
            $head->orderNumber().'-'.$positions[2]->vendor_id,
        ]);
})->with([
    'direkt davor' => ['2026-10-28 10:59:59', false],
    'genau ab Beginn' => [POSITION_CREDIT_DATE, true],
]);

it('nummeriert Gutschriften je Artikel wie im Beispiel FK2026-5149', function () {
    [$head, $positions] = invoiceOrder(3, ['id' => 5149, 'created_at' => POSITION_CREDIT_DATE]);
    $andere = $positions[1]->vendor_id;

    // Nur ein Artikel der Verkäuferin: ohne Pos.
    expect($positions[1]->gutschriftNumber())->toBe('FK2026-5149-'.$andere);

    // Zwei Artikel derselben Verkäuferin auf Pos. 1 und 3: die Pos. der Rechnung,
    // nicht ihr eigener Zähler.
    $positions[0]->update(['vendor_id' => 5680]);
    $positions[2]->update(['vendor_id' => 5680]);

    expect($positions[0]->gutschriftNumber())->toBe('FK2026-5149-5680-1')
        ->and($positions[1]->gutschriftNumber())->toBe('FK2026-5149-'.$andere)
        ->and($positions[2]->gutschriftNumber())->toBe('FK2026-5149-5680-3');
});

it('zeigt der Kundin die Pos. erst ab dem zweiten Zeitpunkt', function ($date, $mitPos) {
    [$head, $children, $buyer] = invoiceOrder(2, ['created_at' => $date]);

    $response = $this->actingAs($buyer)->get('/invoice/'.$head->id)->assertOk();

    if (! $mitPos) {
        $response->assertDontSee('<th>Pos.</th>', false);

        return;
    }

    $response->assertSeeInOrder([
        '<th>Pos.</th>',
        '<td>1</td>', $children[0]->product->name,
        '<td>2</td>', $children[1]->product->name,
        'colspan="3"', 'Gesamtbetrag',
    ], false);
})->with([
    'davor' => ['2026-10-27 15:23:47', false],
    'danach' => [POSITION_CREDIT_DATE, true],
]);

it('stellt derselben Verkäuferin je Artikel eine eigene Gutschrift aus', function () {
    [$head, $positions] = bundledSellerCreditOrder(POSITION_CREDIT_DATE);
    $vendor = \App\Models\User::find($positions[0]->vendor_id);

    foreach ([[$positions[0], $positions[1], 1], [$positions[1], $positions[0], 2]] as [$position, $andere, $pos]) {
        $html = $this->actingAs($vendor)->get('/invoice/'.$position->id)->assertOk()
            ->assertViewHas('positions', fn ($items) => $items->modelKeys() === [$position->id])
            ->assertSee('Gutschrift-Nr. '.$head->orderNumber().'-'.$vendor->id.'-'.$pos)
            ->assertSee($position->product->name)
            ->assertDontSee($andere->product->name)
            ->assertDontSee($positions[2]->product->name)
            ->getContent();

        expect($html)->toContain('<strong>34.00 €</strong>')->not->toContain('68.00 €');
    }
});

it('storniert ab dem zweiten Zeitpunkt nur die Gutschrift des stornierten Artikels', function () {
    [, $positions] = bundledSellerCreditOrder(POSITION_CREDIT_DATE);
    $positions[0]->update(['status' => 3]);
    $vendor = \App\Models\User::find($positions[0]->vendor_id);

    $html = $this->actingAs($vendor)->get('/invoice/'.$positions[1]->id)->assertOk()->getContent();
    expect($html)->toContain('<strong>34.00 €</strong>')->not->toContain('storniert', 'STORNIERT');

    $html = $this->get('/invoice/'.$positions[0]->id)->assertOk()->getContent();
    expect($html)->toContain('GUTSCHRIFT WURDE STORNIERT', '<strong>0.00 €</strong>');
});

it('zeigt im Adminbereich ab dem zweiten Zeitpunkt je Artikel eine Gutschrift und findet sie mit Pos.', function () {
    [$head, $positions] = bundledSellerCreditOrder(POSITION_CREDIT_DATE);
    \App\Models\Role::create(['id' => 1, 'name' => 'admin', 'display_name' => 'Admin']);
    \Illuminate\Support\Facades\Schema::create('permissions', function ($table) {
        $table->id();
        $table->string('key');
    });
    \Illuminate\Support\Facades\Schema::create('permission_role', function ($table) {
        $table->unsignedBigInteger('permission_id');
        $table->unsignedBigInteger('role_id');
    });

    try {
        $this->actingAs(UploadTestHelpers::user(['role_id' => 1]));
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));
        $html = \Livewire\Livewire::test(\App\Filament\Resources\Orders\Pages\ViewOrder::class, [
            'record' => $head->getRouteKey(),
        ])->assertSuccessful()->html();

        expect(substr_count($html, '<section class="invoice-document__section" id="seller-print-block-'))->toBe(3)
            ->and($html)->toContain('<th>Pos.</th>', 'colspan="6"')->not->toContain('68.00 €');

        foreach ($positions as $position) {
            expect($html)->toContain('<section class="invoice-document__section" id="seller-print-block-'.$position->id.'"')
                ->and(substr_count($html, 'Gutschrift-Nr. '.$position->gutschriftNumber().' '))->toBe(1);
        }

        $articles = \Livewire\Livewire::test(\App\Filament\Resources\Orders\Pages\ListOrders::class)
            ->instance()->getTable()->getColumn('articles');
        $state = $articles->record($head)->getState();

        foreach ($positions as $position) {
            expect($state)->toContain('Gutschrift-Nr. '.$position->gutschriftNumber().'<');
        }

        \Illuminate\Support\Facades\DB::connection()->getPdo()->sqliteCreateFunction('CONCAT_WS',
            fn ($separator, ...$values) => implode($separator, array_filter($values, fn ($value) => $value !== null))
        );

        $payouts = \Livewire\Livewire::test(\App\Filament\Resources\Payouts\Pages\ListPayouts::class);
        $payouts->searchTable($positions[1]->gutschriftNumber())
            ->assertCanSeeTableRecords([$positions[1]])
            ->assertCanNotSeeTableRecords([$positions[0], $positions[2]]);
        // Ohne Pos.: alle Artikel dieser Verkäuferin in der Bestellung.
        $payouts->searchTable($head->orderNumber().'-'.$positions[0]->vendor_id)
            ->assertCanSeeTableRecords([$positions[0], $positions[1]])
            ->assertCanNotSeeTableRecords([$positions[2]]);
    } finally {
        \Illuminate\Support\Facades\Schema::dropIfExists('permission_role');
        \Illuminate\Support\Facades\Schema::dropIfExists('permissions');
    }
});

it('nennt in der Versandbestätigung ab dem zweiten Zeitpunkt Artikel und Bestellnummer mit Pos.', function () {
    \Illuminate\Support\Facades\Mail::fake();
    [$head, $positions] = bundledSellerCreditOrder(POSITION_CREDIT_DATE);
    $position = $positions[1];
    $vendor = \App\Models\User::find($position->vendor_id);

    $this->actingAs($vendor)->post(route('seller.order.update', $position), [
        'shipping_method' => 'DHL',
        'shipping_date' => '2026-10-28',
        'tracking_Id' => 'TEST-123',
    ])->assertRedirect()->assertSessionHasNoErrors();

    \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\UserNotifyEmail::class, function ($mail) use ($head, $position) {
        $nummer = 'Bestellung '.$head->orderNumber().' (Position 2 von 3)';

        expect($mail->data->subject)->toBe('Versandbestätigung „'.$position->product->name.'“ – '.$nummer)
            ->and($mail->data->body)->toContain('dein Artikel „'.e($position->product->name).'“ aus '.$nummer);

        return true;
    });
});
