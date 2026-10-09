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
 * rechnet einzeln ab; ihre Gutschrift baut darauf auf und hängt nur ihre
 * Nutzer-ID an (`FK2024-3552-5186`). Damit steht auf keinem Blatt eine Zahl,
 * die es nicht vorher schon mit derselben Bedeutung gab.
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
});

/**
 * @return array{0: Order, 1: \Illuminate\Support\Collection<int, Order>, 2: \App\Models\User}
 */
function invoiceOrder(int $positions = 2, array $headAttributes = [], array $positionAttributes = []): array
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
        // Nach dem Stichtag, also Sammelrechnung. Tests für die Zeit davor
        // setzen 'created_at' über $headAttributes.
        'created_at' => '2026-10-05 10:00:00',
        'updated_at' => '2026-10-05 10:00:00',
    ], $headAttributes));

    $children = collect(range(1, $positions))->map(function (int $i) use ($head, $buyer, $positionAttributes) {
        $vendor = UploadTestHelpers::seller();

        $product = \App\Product::create([
            'name' => "Artikel {$i} der Bestellung {$head->id}",
            'slug' => 'artikel-'.$i.'-'.$head->id,
            'user_id' => $vendor->id,
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

it('baut die Gutschrift auf der Belegnummer der Rechnung auf', function () {
    [, $children] = invoiceOrder(1);

    $position = $children->first();

    // Der Anhang der Nutzer-ID ist der Grund, warum sich die beiden Nummern
    // nicht doppeln, obwohl beide Belege auf derselben Nummer stehen.
    expect($position->gutschriftNumber())
        ->toBe($position->invoiceNumber().'-'.$position->vendor_id);
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

    // Dieselbe Regel vor und nach dem Stichtag: Belegnummer plus Nutzer-ID.
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
