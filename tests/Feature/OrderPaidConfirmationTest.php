<?php

use App\Mail\UserOrderEmail;
use App\Mail\VendorOrderEmail;
use App\Order;
use App\Product;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\UsesUploadSchema;
use Tests\Support\UploadTestHelpers;

/**
 * Der Zahlungseingang gilt für die ganze Bestellung.
 *
 * Eine Bestellung mit zwei Artikeln besteht aus zwei Positionen. Vorher wurde
 * jede Position einzeln bezahlt gemeldet: zweimal „als bezahlt markieren“ im
 * Adminbereich, und der Käufer bekam zwei Bestätigungen mit unterschiedlichen
 * Nummern. Bezahlt wird aber die Bestellung als Ganzes.
 *
 * Die Verkäuferin-Mail bleibt bewusst pro Position – jede Verkäuferin darf nur
 * ihre eigene Position sehen.
 */
uses(UsesUploadSchema::class);

/**
 * Bestellung mit Kopf und einer Position je Verkäuferin.
 *
 * @return array{0: Order, 1: \Illuminate\Support\Collection<int, Order>}
 */
function prepaidOrder(int $positions = 2, array $headAttributes = []): array
{
    $buyer = UploadTestHelpers::buyer(['email' => 'kaeuferin@example.test']);

    $head = Order::create(array_merge([
        'user_id' => $buyer->id,
        'first_name' => 'Käuferin',
        'last_name' => 'Test',
        'email' => $buyer->email,
        'subtotal' => 50.00 * $positions,
        'total' => 50.00 * $positions,
        'payment_gateway' => 'pre_payment',
        'payment_status' => 0,
        'status' => 0,
    ], $headAttributes));

    $children = collect(range(1, $positions))->map(function (int $i) use ($head, $buyer) {
        $vendor = UploadTestHelpers::seller(['email' => "verkaeuferin-{$i}@example.test"]);

        $product = Product::create([
            'name' => "Artikel {$i}",
            'slug' => 'artikel-'.$i.'-'.$head->id,
            'user_id' => $vendor->id,
            'price' => 50.00,
            'quantity' => 1,
            'status' => 1,
            'selloption' => 0,
            // Default im Testschema ist 1; von 0 aus ist die Abbuchung eindeutig.
            'sale_count' => 0,
        ]);

        return Order::create([
            'parent_id' => $head->id,
            'user_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'first_name' => 'Käuferin',
            'last_name' => 'Test',
            'email' => $buyer->email,
            'total' => 50.00,
            'payment_gateway' => 'pre_payment',
            'payment_status' => 0,
            'status' => 0,
        ]);
    });

    return [$head->fresh(), $children];
}

it('verschickt genau eine Bestätigung für die ganze Bestellung', function () {
    Mail::fake();

    [$head] = prepaidOrder(2);

    expect($head->markOrderAsPaid())->toBeTrue();

    // Der eigentliche Punkt: EINE Bestätigung, nicht eine pro Position.
    Mail::assertSent(UserOrderEmail::class, 1);

    // Und sie gilt für die Bestellung, nicht für eine Position.
    Mail::assertSent(UserOrderEmail::class, fn (UserOrderEmail $mail): bool => (int) $mail->order->id === (int) $head->id);
});

it('benachrichtigt jede Verkäuferin einzeln', function () {
    Mail::fake();

    [$head, $children] = prepaidOrder(2);

    $head->markOrderAsPaid();

    // Zwei Verkäuferinnen, zwei Mails – jede nur über ihre eigene Position.
    Mail::assertSent(VendorOrderEmail::class, 2);

    foreach ($children as $child) {
        Mail::assertSent(
            VendorOrderEmail::class,
            fn (VendorOrderEmail $mail): bool => (int) $mail->order->id === (int) $child->id
        );
    }
});

it('setzt Kopf und alle Positionen in einem Schritt auf bezahlt', function () {
    Mail::fake();

    [$head, $children] = prepaidOrder(3);

    $head->markOrderAsPaid();

    expect((int) $head->fresh()->payment_status)->toBe(1)
        ->and((int) $head->fresh()->status)->toBe(1);

    foreach ($children as $child) {
        expect((int) $child->fresh()->payment_status)->toBe(1)
            ->and((int) $child->fresh()->status)->toBe(1);
    }
});

it('bucht den Bestand je Position ab, nicht nur einmal', function () {
    Mail::fake();

    [$head, $children] = prepaidOrder(2);

    $head->markOrderAsPaid();

    // Jede Position ist ein Einzelstück (selloption = 0) und geht aus dem Shop.
    foreach ($children as $child) {
        $product = Product::find($child->product_id);

        expect((int) $product->quantity)->toBe(0)
            ->and((int) $product->sale_count)->toBe(1)
            ->and((int) $product->status)->toBe(0);
    }
});

it('lässt sich von einer Position aus für die ganze Bestellung auslösen', function () {
    Mail::fake();

    [$head, $children] = prepaidOrder(2);

    // So kommt es aus dem Adminbereich: geklickt wird eine Zeile.
    expect($children->first()->markOrderAsPaid())->toBeTrue();

    expect((int) $head->fresh()->payment_status)->toBe(1)
        ->and((int) $children->last()->fresh()->payment_status)->toBe(1);

    Mail::assertSent(UserOrderEmail::class, 1);
});

it('meldet einen zweiten Aufruf als bereits bezahlt und schickt nichts nach', function () {
    Mail::fake();

    [$head] = prepaidOrder(2);

    expect($head->markOrderAsPaid())->toBeTrue()
        ->and($head->fresh()->markOrderAsPaid())->toBeFalse();

    // Zwei Klicks im Adminbereich dürfen keine zweite Bestätigung auslösen.
    Mail::assertSent(UserOrderEmail::class, 1);
    Mail::assertSent(VendorOrderEmail::class, 2);
});

it('behandelt eine Bestellung ohne Positionen wie eine einzelne', function () {
    Mail::fake();

    // Altdatensatz: kein Kopf mit Positionen, sondern eine Bestellung allein.
    $order = Order::create([
        'email' => 'kundin@example.test',
        'total' => 20.00,
        'payment_status' => 0,
    ]);

    expect($order->markOrderAsPaid())->toBeTrue()
        ->and((int) $order->fresh()->payment_status)->toBe(1);

    Mail::assertSent(UserOrderEmail::class, 1);
});

it('zieht den Kopf nach, wenn die Positionen schon bezahlt waren', function () {
    Mail::fake();

    [$head, $children] = prepaidOrder(2);

    // Stand, wie ihn das alte Verhalten hinterlassen konnte: Positionen bezahlt,
    // der Kopf nicht. Die Bestellung blieb dadurch in der Vorkasse-Liste stehen.
    foreach ($children as $child) {
        Order::query()->whereKey($child->id)->update(['payment_status' => 1, 'status' => 1]);
    }
    Order::query()->whereKey($head->id)->update(['payment_status' => 0, 'status' => 0]);

    expect($head->fresh()->markOrderAsPaid())->toBeFalse();

    // Kein zweiter Versand, aber der Kopf steht jetzt richtig.
    expect((int) $head->fresh()->payment_status)->toBe(1);
    Mail::assertNothingSent();
});

/**
 * Mail::fake() rendert das Template nicht. Diese Tests bauen die Nachricht
 * deshalb selbst und schauen hinein.
 */
it('listet in der Bestätigung alle Positionen mit Betrag auf', function () {
    [$head, $children] = prepaidOrder(2);

    $html = (new UserOrderEmail($head))->render();
    $text = html_entity_decode(strip_tags($html));

    // Die bezahlte Nummer, einmal – nicht die IDs der Positionen.
    expect($text)->toContain($head->orderNumber());

    // Jede Position mit Artikel und Betrag.
    foreach ($children as $child) {
        expect($text)->toContain($child->product->name);
    }

    // Genau EIN Rechnungslink, und der zeigt auf die Bestellung.
    expect($html)->toContain('/invoice/'.$head->id);
    foreach ($children as $child) {
        expect($html)->not->toContain('/invoice/'.$child->id);
    }

    expect($text)
        ->toContain('2 Artikel')
        ->toContain('Gesamtbetrag')
        ->toContain('100,00 €');
});

it('kündigt eine Rechnung für die ganze Bestellung an', function () {
    [$head] = prepaidOrder(2);

    $text = html_entity_decode(strip_tags((new UserOrderEmail($head))->render()));

    expect($text)
        ->toContain('eine Rechnung für die ganze Bestellung')
        ->toContain($head->orderNumber());
});

it('spricht bei einem einzigen Artikel nicht von mehreren Paketen', function () {
    [$head] = prepaidOrder(1);

    $text = html_entity_decode(strip_tags((new UserOrderEmail($head))->render()));

    expect($text)
        ->toContain('einen Artikel')
        ->not->toContain('mehrere');
});

it('nennt die Herstellerinnen in der Mail nur mit Vornamen', function () {
    [$head, $children] = prepaidOrder(2);

    // Die Verkäuferinnen aus prepaidOrder() tragen name='Test', last_name='User'.
    $vendor = $children->first()->vendor;
    $vendor->update(['name' => 'Jennifer', 'last_name' => 'Lanzo']);

    $text = html_entity_decode(strip_tags((new UserOrderEmail($head->fresh()))->render()));

    expect($text)
        ->toContain('Jennifer')
        ->not->toContain('Lanzo');
});

it('weist den Gutschein in der Bestätigung aus, ohne doppelt abzuziehen', function () {
    // Kopf einer rabattierten Bestellung: total ist bereits subtotal − discount.
    [$head] = prepaidOrder(2, [
        'subtotal' => 100.00,
        'discount' => 15.00,
        'discount_code' => 'SOMMER',
        'total' => 85.00,
    ]);

    $text = html_entity_decode(strip_tags((new UserOrderEmail($head))->render()));

    expect($text)
        ->toContain('SOMMER')
        ->toContain('15,00 €')
        // Der Gesamtbetrag ist der schon rabattierte Wert, nicht 70,00 €.
        ->toContain('85,00 €')
        ->not->toContain('70,00 €');
});

/**
 * Derselbe Weg über die Online-Überweisung: Die Benachrichtigung von
 * Micropayment meldet die Bestellung bezahlt, nicht eine Position.
 */
it('verschickt auch bei der Online-Überweisung nur eine Bestätigung', function () {
    Mail::fake();

    [$head, $children] = prepaidOrder(2);

    (new \App\Payment\MicropaymentOrderSubject($head))->markPaid('TRX-4711');

    Mail::assertSent(UserOrderEmail::class, 1);
    Mail::assertSent(VendorOrderEmail::class, 2);

    expect((int) $head->fresh()->payment_status)->toBe(1);

    // Die Transaktionsnummer steht an jeder Position.
    foreach ($children as $child) {
        expect($child->fresh()->payment_id)->toBe('TRX-4711')
            ->and((int) $child->fresh()->payment_status)->toBe(1);
    }
});

it('löst bei einer erneuten Benachrichtigung keine zweite Bestätigung aus', function () {
    Mail::fake();

    [$head] = prepaidOrder(2);

    $subject = new \App\Payment\MicropaymentOrderSubject($head->fresh());
    $subject->markPaid('TRX-4711');

    // Micropayment stellt eine Benachrichtigung notfalls erneut zu.
    (new \App\Payment\MicropaymentOrderSubject($head->fresh()))->markPaid('TRX-4711');

    Mail::assertSent(UserOrderEmail::class, 1);
    Mail::assertSent(VendorOrderEmail::class, 2);
});

/**
 * Die Vorkasse-Liste im Adminbereich zeigt Bestellungen, nicht Positionen –
 * sonst stünde eine Bestellung mit zwei Artikeln zweimal da und müsste zweimal
 * als bezahlt markiert werden.
 */
it('listet offene Vorkasse je Bestellung statt je Position', function () {
    [$head, $children] = prepaidOrder(2);

    $ids = \App\Filament\Resources\Prepayments\PrepaymentResource::getEloquentQuery()
        ->pluck('id')
        ->map(fn ($id) => (int) $id);

    expect($ids)->toContain((int) $head->id);

    foreach ($children as $child) {
        expect($ids)->not->toContain((int) $child->id);
    }
});

it('nimmt eine bezahlte Bestellung aus der Vorkasse-Liste', function () {
    Mail::fake();

    [$head] = prepaidOrder(2);

    $head->markOrderAsPaid();

    $ids = \App\Filament\Resources\Prepayments\PrepaymentResource::getEloquentQuery()
        ->pluck('id')
        ->map(fn ($id) => (int) $id);

    expect($ids)->not->toContain((int) $head->id);
});

/**
 * Die Bestellliste im Adminbereich zeigt Bestellungen, nicht Positionen.
 */
it('listet bezahlte Bestellungen je Bestellung statt je Position', function () {
    Mail::fake();

    [$head, $children] = prepaidOrder(2);
    $head->markOrderAsPaid();

    $ids = \App\Filament\Resources\Orders\OrderResource::getEloquentQuery()
        ->pluck('id')
        ->map(fn ($id) => (int) $id);

    expect($ids)->toContain((int) $head->id);

    // Der eigentliche Punkt: die Positionen stehen nicht als eigene Zeilen da.
    foreach ($children as $child) {
        expect($ids)->not->toContain((int) $child->id);
    }

    expect($ids->filter(fn (int $id): bool => $id === (int) $head->id))->toHaveCount(1);
});

it('zählt auf dem Dashboard Bestellungen, nicht Positionen', function () {
    Mail::fake();

    [$head] = prepaidOrder(3);
    $head->markOrderAsPaid();

    // Eine Bestellung mit drei Artikeln ist eine Bestellung.
    expect((new \App\Filament\Widgets\DashboardOverviewWidget)->getOrdersCount())->toBe(1);
});

it('findet eine Bestellung auch über die Beleg-Nummer einer Position', function () {
    Mail::fake();

    [$head, $children] = prepaidOrder(2);
    $head->markOrderAsPaid();

    // Die Rechnung des Kunden nennt die Beleg-Nummer der Position. Danach muss
    // sich die Bestellung finden lassen.
    $found = \App\Filament\Resources\Orders\OrderResource::getEloquentQuery()
        ->where(function ($q) use ($children): void {
            $search = (string) $children->first()->id;
            $q->where('id', $search)
                ->orWhereHas('childrens', fn ($c) => $c->where('id', $search));
        })
        ->pluck('id')
        ->map(fn ($id) => (int) $id);

    expect($found)->toContain((int) $head->id);
});
