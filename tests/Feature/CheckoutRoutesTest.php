<?php

use App\Mail\UserPrepaymentOrder;
use App\Order;
use Illuminate\Support\Facades\Mail;
use Tests\Support\MicropaymentTestSchema;

test('get store-checkout redirects to checkout page', function () {
    $response = $this->get('/store-checkout');

    $response->assertRedirect(route('checkout'));
});

/**
 * Ein leerer Warenkorb darf keine Bestellung erzeugen.
 *
 * Ohne diese Prüfung rechnet processOrder() `max(0, 0 - 0)` und legt eine
 * Bestellung über 0,00 € ohne Unterbestellungen an. Bei der Online-Überweisung
 * wird daraus ein Betrag von 0 im Zahlungsfenster, und Micropayment bucht
 * dafür seinen Mindestbetrag von 0,49 € – bezahlt, aber nie gutgeschrieben,
 * weil die Betragsprüfung anschlägt. Produktiv sind so 346 Bestellungen
 * entstanden, alle ohne eine einzige Unterbestellung.
 *
 * Der Weg dorthin ist ein erneut abgeschicktes Kassenformular: processOrder()
 * ruft am Ende \Cart::clear(), die Kassenseite selbst prüft den leeren
 * Warenkorb längst (PageController::checkout()) – nur das Absenden tat es nicht.
 */
describe('Kasse mit leerem Warenkorb', function () {
    beforeEach(fn () => MicropaymentTestSchema::create());
    afterEach(fn () => MicropaymentTestSchema::drop());

    test('store-checkout legt bei leerem Warenkorb keine Bestellung an', function () {
        expect(\Cart::isEmpty())->toBeTrue();

        $this->post(route('checkout.store'), [
            'first_name' => 'Anna',
            'last_name' => 'Beispiel',
            'email' => 'buyer@example.com',
            'street' => 'Musterweg',
            'house_no' => '1',
            'zip' => '12345',
            'federal_state' => 'Berlin',
            'datenschutz' => 'on',
        ])->assertRedirect('/shop');

        expect(Order::count())->toBe(0);
    });
});

test('user prepayment order mailable defines a from address', function () {
    config([
        'app.url' => 'http://127.0.0.1:8000',
        'mail.from.address' => mail_from_address(),
        'mail.from.name' => mail_from_name(),
    ]);

    $order = new Order([
        'id' => 4973,
        'total' => 73.19,
        'email' => 'buyer@example.com',
    ]);
    $order->exists = true;
    $order->created_at = now();

    $mailable = new UserPrepaymentOrder($order);
    $mailable->build();

    expect($mailable->from)->toHaveCount(1)
        ->and($mailable->from[0]['address'])->not->toBeEmpty()
        ->and(filter_var($mailable->from[0]['address'], FILTER_VALIDATE_EMAIL))->not->toBeFalse();
});

test('user prepayment order can be queued for sending', function () {
    Mail::fake();

    config([
        'app.url' => 'http://127.0.0.1:8000',
        'mail.from.address' => mail_from_address(),
        'mail.from.name' => mail_from_name(),
    ]);

    $order = new Order([
        'id' => 4973,
        'total' => 73.19,
        'email' => 'buyer@example.com',
    ]);
    $order->exists = true;
    $order->created_at = now();

    Mail::to('buyer@example.com')->send(new UserPrepaymentOrder($order));

    Mail::assertSent(UserPrepaymentOrder::class);
});
