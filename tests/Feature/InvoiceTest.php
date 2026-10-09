<?php

use App\Order;
use Illuminate\Support\Facades\View;
use Tests\Concerns\UsesUploadSchema;
use Tests\Support\UploadTestHelpers;

/**
 * Ein Beleg je Position – und seine Nummer steht fest.
 *
 * Jeder Artikel kommt von einer eigenen Herstellerin, die einzeln abrechnet.
 * Deshalb gilt ein Beleg je Position: Bei einem Artikel gibt es eine
 * Belegnummer, bei drei Artikeln drei. Rechnung der Kundin und Gutschrift der
 * Herstellerin stehen auf derselben Nummer; die Gutschrift hängt nur die
 * Nutzer-ID an, damit sich die Nummern nicht doppeln.
 *
 * Die Nummer, die die Kundin bezahlt hat, ist eine andere: Sie gilt für die
 * ganze Bestellung, steht auf dem Kontoauszug und heißt orderNumber(). Auf
 * jedem Beleg steht sie als „zur Bestellung“ dabei.
 *
 * Zwischen dem 30.09.2026 und dem Rückbau trug die Rechnung die Bestellnummer.
 * Damit änderten sich rückwirkend alle Belegnummern – aus `FK2024-3552` wurde
 * `FK2024-3550`, und archivierte Belege stimmten nicht mehr. Die Tests hier
 * halten beides fest: die Nummer je Position und dass sie fest in der
 * Datenbank steht, statt bei jedem Aufruf neu gerechnet zu werden.
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

it('zeigt dem Käufer die Rechnung zu einem Artikel seiner Bestellung', function () {
    [$head, $children, $buyer] = invoiceOrder(2);

    $meine = $children->first();

    $response = $this->actingAs($buyer)->get('/invoice/'.$meine->id);

    $response->assertOk()
        ->assertSee('Rechnung')
        ->assertSee($meine->invoiceNumber())
        ->assertSee($meine->product->name)
        // Die Nummer, die sie bezahlt hat, steht als Hinweis dabei.
        ->assertSee($head->orderNumber())
        // Der andere Artikel hat seinen eigenen Beleg.
        ->assertDontSee($children->last()->product->name);
});

it('gibt jedem Artikel eine eigene Rechnung mit eigener Nummer', function () {
    [, $children, $buyer] = invoiceOrder(2);

    foreach ($children as $child) {
        $html = $this->actingAs($buyer)->get('/invoice/'.$child->id)->assertOk()->getContent();

        expect($html)->toMatch('/Rechnungs-Nr\.?:?\s*'.preg_quote($child->invoiceNumber(), '/').'\b/');

        // Und nicht die Nummer des Nachbarartikels.
        $andere = $children->firstWhere('id', '!=', $child->id);
        expect($html)->not->toMatch('/Rechnungs-Nr\.?:?\s*'.preg_quote($andere->invoiceNumber(), '/').'\b/');
    }
});

it('führt vom Kopf der Bestellung zum Beleg der ersten Position', function () {
    [$head, $children, $buyer] = invoiceOrder(2);

    // Der Kopf ist kein Beleg: Seine Nummer ist die Bestellnummer und stand nie
    // auf einer Rechnung. Aeltere Links und Listen tragen sie trotzdem.
    $this->actingAs($buyer)
        ->get('/invoice/'.$head->id)
        ->assertRedirect(route('invoice', $children->first()));
});

it('rechnet den Gutschein auf der Rechnung der Position ab', function () {
    // Im Kopf steht der volle Rabatt, in einer Position nur ihr Anteil.
    [, $children, $buyer] = invoiceOrder(2, [
        'subtotal' => 80.00,
        'discount' => 15.00,
        'discount_code' => 'SOMMER',
        'total' => 65.00,
    ], [
        'discount' => 7.50,
        'discount_code' => 'SOMMER',
    ]);

    $html = $this->actingAs($buyer)->get('/invoice/'.$children->first()->id)->getContent();

    expect($html)
        ->toContain('SOMMER')
        ->toContain('Zwischensumme')
        // 40 − 7,50 = 32,50 für diese Position.
        ->toContain('32.50');
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

it('weist eine fremde Kundin auch an der Position ab', function () {
    [, $children] = invoiceOrder(2);

    $fremde = UploadTestHelpers::buyer();

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
    [, $children] = invoiceOrder(2);

    $admin = UploadTestHelpers::user(['role_id' => 1]);

    $this->actingAs($admin)
        ->get('/invoice/'.$children->last()->id)
        ->assertOk()
        ->assertSee($children->last()->invoiceNumber());
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

    // Ein Altdatensatz ohne Positionen ist seine eigene einzige Position: Hier
    // darf nicht weitergeleitet werden, sonst gibt es keinen Beleg.
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
it('markiert die Rechnung einer stornierten Position als storniert', function () {
    [, $children, $buyer] = invoiceOrder(2);

    $storniert = $children->first();
    $storniert->update(['status' => 3]);

    $html = $this->actingAs($buyer)->get('/invoice/'.$storniert->id)->assertOk()->getContent();

    expect($html)
        ->toContain('RECHNUNG WURDE STORNIERT')
        // Der rote Balken der Kundenansicht.
        ->toContain('card-body storniert');
});

it('lässt die Rechnung der anderen Position unberührt', function () {
    [, $children, $buyer] = invoiceOrder(2);

    $children->first()->update(['status' => 3]);

    $offen = $children->last();

    $html = $this->actingAs($buyer)->get('/invoice/'.$offen->id)->assertOk()->getContent();

    expect($html)->not->toContain('RECHNUNG WURDE STORNIERT');
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
