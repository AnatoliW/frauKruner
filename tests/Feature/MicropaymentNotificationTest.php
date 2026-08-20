<?php

use App\Coupon;
use App\Mail\UserOrderEmail;
use App\Mail\VendorOrderEmail;
use App\Models\Log;
use App\Models\User;
use App\Order;
use App\Payment\MicropaymentOrderSubject;
use App\Product;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Tests\Support\MicropaymentTestSchema;

beforeEach(function () {
    MicropaymentTestSchema::create();

    Mail::fake();

    // Das echte Frontend-Layout zieht Menüs, Einstellungen und Kategorien aus
    // Tabellen, die im schlanken Test-Schema fehlen. Geprüft wird der Inhalt
    // der eigenen Ansichten, deshalb hier ein Ersatz-Layout.
    View::getFinder()->prependLocation(__DIR__.'/../Support/views');

    config([
        'micropayment.enabled' => true,
        'micropayment.access_key' => 'TEST-ACCESSKEY',
        'micropayment.project' => 'fraukruner',
        'micropayment.testmode' => true,
        'micropayment.online_transfer_url' => 'https://directbanking.micropayment.de/sofort/event/',
        'micropayment.notification.secret_field_name' => 'secretfield',
        'micropayment.notification.secret_field_value' => 'geheim123',
        'micropayment.notification.allow_testmode' => true,
        'micropayment.notification.allow_unauthenticated' => false,
        'micropayment.notification.allow_apicheck' => false,
        'micropayment.notification.check_amount' => true,
        'micropayment.notification.forward' => true,
        'micropayment.notification.target' => '_self',
    ]);
});

afterEach(function () {
    MicropaymentTestSchema::drop();
});

/**
 * Hauptbestellung mit einer Unterbestellung, Produkt und Verkäufer – so wie
 * CheckoutController::createOrders() sie anlegt.
 *
 * @return array{0: Order, 1: Order, 2: Product}
 */
function mcpOrderWithChild(array $attributes = []): array
{
    $vendor = User::create([
        'name' => 'Verkäuferin',
        'email' => 'vendor@example.com',
        'status' => 1,
    ]);

    $product = Product::create([
        'user_id' => $vendor->id,
        'name' => 'Testprodukt',
        'price' => 42.50,
        'quantity' => 3,
        'sale_count' => 0,
        'selloption' => 1,
        'status' => 1,
    ]);

    $base = array_merge([
        'first_name' => 'Anna',
        'last_name' => 'Beispiel',
        'email' => 'buyer@example.com',
        'total' => 42.50,
        'subtotal' => 42.50,
        'status' => 0,
        'payment_status' => 0,
    ], $attributes);

    $parent = Order::create($base);

    $child = Order::create($base + [
        'parent_id' => $parent->id,
        'product_id' => $product->id,
        'vendor_id' => $vendor->id,
    ]);

    return [$parent->fresh(), $child->fresh(), $product->fresh()];
}

/**
 * Vollständige, gültige Benachrichtigung für eine Bestellung.
 */
function mcpNotification(Order $order, array $overrides = []): array
{
    return array_merge([
        'function' => 'billing',
        'title' => (new MicropaymentOrderSubject($order))->reference(),
        'amount' => (new MicropaymentOrderSubject($order))->amountInCents(),
        'currency' => 'EUR',
        'auth' => 'mcp-trx-0815',
        'testmode' => 1,
        'secretfield' => 'geheim123',
        'orderToken' => (new MicropaymentOrderSubject($order))->token(),
    ], $overrides);
}

/**
 * Ruft den Benachrichtigungsendpunkt auf und zerlegt die Textantwort.
 *
 * @return array<string, string>
 */
function mcpNotify(array $query): array
{
    $response = test()->get(route('payment.micropayment.notify', $query));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/plain; charset=utf-8');

    $fields = [];

    foreach (explode("\n", trim($response->getContent())) as $line) {
        if ($line === '') {
            continue;
        }

        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
        $fields[$name] = $value;
    }

    return $fields;
}

describe('Benachrichtigung – Abwehr', function () {
    it('lehnt eine Meldung ohne Ereignis ab', function () {
        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['function' => null]));

        expect($result['status'])->toBe('error');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('lehnt ein falsches Geheimfeld ab', function () {
        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['secretfield' => 'falsch']));

        expect($result['status'])->toBe('error');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('lehnt eine Meldung ohne Geheimfeld ab, sobald eines konfiguriert ist', function () {
        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['secretfield' => null]));

        expect($result['status'])->toBe('error');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('prüft das Geheimfeld während der Einrichtung nicht, wenn das ausdrücklich freigeschaltet ist', function () {
        config([
            'micropayment.notification.secret_field_value' => '',
            'micropayment.notification.allow_unauthenticated' => true,
        ]);

        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['secretfield' => null]));

        expect($result['status'])->toBe('ok');
        expect($order->fresh()->payment_status)->toBe(1);
    });

    // Ohne Geheimfeld reicht es, Bestellnummer und Betrag zu kennen, um eine
    // Bestellung als bezahlt zu melden – bei der eigenen Bestellung weiß eine
    // Angreiferin beides. Ein vergessenes Geheimfeld allein darf den Endpunkt
    // deshalb nicht öffnen; die Freischaltung muss ausdrücklich erfolgen.
    it('lehnt ohne Geheimfeld ab, solange die Einrichtung nicht freigeschaltet ist', function () {
        config(['micropayment.notification.secret_field_value' => '']);

        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['secretfield' => null]));

        expect($result['status'])->toBe('error');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    // Produktiv gibt es die Freischaltung überhaupt nicht.
    it('lehnt produktiv alles ab, solange kein Geheimfeld konfiguriert ist', function () {
        config([
            'micropayment.notification.secret_field_value' => '',
            'micropayment.notification.allow_unauthenticated' => true,
        ]);
        app()->detectEnvironment(fn () => 'production');

        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['secretfield' => null]));

        expect($result['status'])->toBe('error');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('lehnt eine unbekannte Bestellreferenz ab', function () {
        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['title' => 'FK2026-99999999']));

        expect($result['status'])->toBe('error');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('lehnt ein falsches Merkmal ab', function () {
        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['orderToken' => str_repeat('f', 32)]));

        expect($result['status'])->toBe('error');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('lehnt ein unbekanntes Ereignis ab', function () {
        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['function' => 'raketenstart']));

        expect($result['status'])->toBe('error');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('lehnt Testbuchungen ab, sobald der Testmodus gesperrt ist', function () {
        config(['micropayment.notification.allow_testmode' => false]);

        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['testmode' => 1]));

        expect($result['status'])->toBe('error');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('lehnt das Testwerkzeug des Control-Centers ab, solange es gesperrt ist', function () {
        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['apicheck' => 1]));

        expect($result['status'])->toBe('error');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('beantwortet das freigeschaltete Testwerkzeug mit ok, ohne zu buchen', function () {
        config(['micropayment.notification.allow_apicheck' => true]);

        [$order] = mcpOrderWithChild();

        // Das Werkzeug schickt Beispieldaten statt einer echten Referenz.
        $result = mcpNotify(mcpNotification($order, ['apicheck' => 1, 'title' => 'Kruner']));

        expect($result['status'])->toBe('ok');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('bucht auch dann nicht, wenn das Testwerkzeug eine echte Referenz nennt', function () {
        config(['micropayment.notification.allow_apicheck' => true]);

        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['apicheck' => 1]));

        expect($result['status'])->toBe('ok');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('lehnt alles ab, solange die Zahlart abgeschaltet ist', function () {
        config(['micropayment.enabled' => false]);

        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order));

        expect($result['status'])->toBe('error');
        expect($order->fresh()->payment_status)->toBe(0);
    });
});

describe('Benachrichtigung – Betrag', function () {
    it('bucht nicht, wenn zu wenig gemeldet wird', function () {
        [$order, $child, $product] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['amount' => 1]));

        // `ok`, damit Micropayment die Meldung nicht endlos wiederholt – die
        // Bestellung bleibt aber unbezahlt und wird von Hand geprüft.
        expect($result['status'])->toBe('ok')
            ->and($result['url'])->toContain('/result/mismatch');

        expect($order->fresh()->payment_status)->toBe(0)
            ->and($child->fresh()->payment_status)->toBe(0)
            ->and($product->fresh()->sale_count)->toBe(0);

        Mail::assertNothingSent();
    });

    it('bucht nicht bei abweichender Währung', function () {
        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['currency' => 'CHF']));

        expect($result['url'])->toContain('/result/mismatch');
        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('bucht bei genau passendem Betrag', function () {
        [$order] = mcpOrderWithChild(['total' => 19.99]);

        $result = mcpNotify(mcpNotification($order, ['amount' => 1999]));

        expect($result['url'])->toContain('/result/completed');
        expect($order->fresh()->payment_status)->toBe(1);
    });

    it('bucht ohne Betragsprüfung auch bei Abweichung', function () {
        config(['micropayment.notification.check_amount' => false]);

        [$order] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order, ['amount' => 1]));

        expect($order->fresh()->payment_status)->toBe(1);
    });
});

describe('Benachrichtigung – Zahlungseingang', function () {
    it('markiert Haupt- und Unterbestellung als bezahlt', function () {
        [$order, $child] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order));

        expect($result['status'])->toBe('ok')
            ->and($result['url'])->toContain('/result/completed')
            ->and($result['target'])->toBe('_self')
            ->and($result['forward'])->toBe('1');

        expect($order->fresh())
            ->payment_status->toBe(1)
            ->status->toBe(1)
            ->payment_gateway->toBe('online_transfer')
            ->payment_id->toBe('mcp-trx-0815');

        expect($child->fresh())
            ->payment_status->toBe(1)
            ->payment_gateway->toBe('online_transfer')
            ->payment_id->toBe('mcp-trx-0815');
    });

    it('bucht den Verkauf beim Produkt ab', function () {
        [$order, , $product] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order));

        expect($product->fresh())
            ->sale_count->toBe(1)
            ->quantity->toBe(2);
    });

    it('nimmt ein Einzelstück aus dem Shop', function () {
        [$order, $child, $product] = mcpOrderWithChild();
        $product->update(['selloption' => 0]);

        mcpNotify(mcpNotification($order));

        expect($product->fresh()->getAttributes()['status'])->toBe(0);
    });

    it('verschickt je eine Mail an Käuferin und Verkäufer', function () {
        [$order] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order));

        Mail::assertSent(UserOrderEmail::class, 1);
        Mail::assertSent(VendorOrderEmail::class, 1);
    });

    /**
     * Der Mailversand läuft synchron in der Anfrage von Micropayment. Ist der
     * Mailserver nicht erreichbar, darf er die Zahlung nicht mitreißen: Sie ist
     * gebucht, bevor die erste Mail rausgeht, und die erneute Zustellung käme
     * wegen des bedingten UPDATE in markAsPaid() nie wieder bis zum Versand.
     * Ohne Absicherung sähe die Kundin „Die Weiterleitung wurde nicht erlaubt“,
     * obwohl das Geld angekommen ist.
     *
     * `twice()` hält zugleich fest, dass die zweite Mail noch versucht wird,
     * nachdem die erste geworfen hat – ein Verkäufer soll seine Bestellung auch
     * dann sehen, wenn die Adresse der Käuferin den Mailserver ärgert.
     */
    it('bucht die Zahlung auch dann, wenn der Mailversand scheitert', function () {
        [$order] = mcpOrderWithChild();

        Mail::shouldReceive('to')->twice()->andReturnSelf();
        Mail::shouldReceive('send')->twice()->andThrow(new RuntimeException('SMTP nicht erreichbar'));

        $fields = mcpNotify(mcpNotification($order));

        expect($fields['status'])->toBe('ok')
            ->and($order->fresh()->payment_status)->toBe(1);
    });

    it('hält die Bestellung als bestätigt fest', function () {
        [$order] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order));

        expect($order->fresh()->confirmed_at)->not->toBeNull();
    });

    it('behandelt rebill wie billing', function () {
        [$order] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order, ['function' => 'rebill']));

        expect($order->fresh()->payment_status)->toBe(1);
    });

    it('kommt ohne Transaktionsnummer aus', function () {
        [$order] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order, ['auth' => null]));

        expect($order->fresh()->payment_status)->toBe(1);
    });

    it('schreibt einen Protokolleintrag', function () {
        [$order] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order));

        expect(Log::where('email', 'buyer@example.com')->count())->toBeGreaterThan(0);
    });

    // Das Geheimfeld steht als gewöhnlicher Parameter in der Meldung. Landete es
    // im Protokoll, könnte jeder, der die Tabelle `logs` im Adminbereich sieht,
    // damit fremde Bestellungen als bezahlt melden. Für `orderToken` gilt dasselbe.
    it('schreibt weder Geheimfeld noch Merkmal ins Protokoll', function () {
        [$order] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order));

        $details = Log::where('email', 'buyer@example.com')->pluck('details')->implode("\n");

        expect($details)
            ->not->toContain('geheim123')
            ->not->toContain('secretfield')
            ->not->toContain('orderToken')
            ->and($details)->toContain('billing');
    });
});

describe('Benachrichtigung – doppelte Zustellung', function () {
    it('bucht bei einer zweiten Meldung weder Bestand noch Mails erneut', function () {
        [$order, , $product] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order));
        mcpNotify(mcpNotification($order));
        mcpNotify(mcpNotification($order));

        expect($product->fresh())
            ->sale_count->toBe(1)
            ->quantity->toBe(2);

        Mail::assertSent(UserOrderEmail::class, 1);
        Mail::assertSent(VendorOrderEmail::class, 1);
    });

    it('löst den Gutschein nur einmal ein', function () {
        Coupon::create([
            'code' => 'SOMMER',
            'discount' => 10.00,
            'expire_at' => now()->addMonth(),
            'limit' => 5,
            'minimum_cart' => 0,
            'used' => 0,
        ]);

        [$order] = mcpOrderWithChild(['discount_code' => 'SOMMER', 'discount' => 10.00]);

        mcpNotify(mcpNotification($order));
        mcpNotify(mcpNotification($order));

        expect(Coupon::where('code', 'SOMMER')->first()->used)->toBe(1);
    });
});

describe('Benachrichtigung – offene Zahlung', function () {
    it('markiert pending nicht als bezahlt', function () {
        [$order, $child, $product] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['function' => 'pending']));

        expect($result['status'])->toBe('ok')
            ->and($result['url'])->toContain('/result/pending');

        expect($order->fresh()->payment_status)->toBe(0)
            ->and($child->fresh()->payment_status)->toBe(0)
            ->and($product->fresh()->sale_count)->toBe(0);

        Mail::assertNothingSent();
    });

    it('hält die Zahlart und die Bestätigung schon bei pending fest', function () {
        [$order] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order, ['function' => 'pending']));

        expect($order->fresh())
            ->payment_gateway->toBe('online_transfer')
            ->confirmed_at->not->toBeNull();
    });

    it('bucht den Zahlungseingang auch nach einem vorherigen pending', function () {
        [$order, , $product] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order, ['function' => 'pending']));
        mcpNotify(mcpNotification($order, ['function' => 'billing']));

        expect($order->fresh()->payment_status)->toBe(1)
            ->and($product->fresh()->sale_count)->toBe(1);

        Mail::assertSent(UserOrderEmail::class, 1);
    });

    it('markiert init nicht als bezahlt', function () {
        [$order] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order, ['function' => 'init']));

        expect($order->fresh()->payment_status)->toBe(0);
    });

    it('markiert einen Fehler nicht als bezahlt', function ($event) {
        [$order] = mcpOrderWithChild();

        $result = mcpNotify(mcpNotification($order, ['function' => $event]));

        expect($result['url'])->toContain('/result/failed');
        expect($order->fresh()->payment_status)->toBe(0);
    })->with(['error', 'info']);

    it('verarbeitet Storno und Rückbuchung nicht selbsttätig', function ($event) {
        [$order, , $product] = mcpOrderWithChild();

        mcpNotify(mcpNotification($order));
        $result = mcpNotify(mcpNotification($order, ['function' => $event]));

        expect($result['status'])->toBe('ok')
            ->and($result['url'])->toContain('/result/fallback');

        // Bestand und Status bleiben, bis jemand die Rückbuchung von Hand entscheidet.
        expect($order->fresh()->payment_status)->toBe(1)
            ->and($product->fresh()->sale_count)->toBe(1);
    })->with(['storno', 'refund', 'backpay', 'expire', 'quit']);
});

describe('Weiterleitung zum Zahlungsfenster', function () {
    it('leitet mit gültiger Signatur zum Zahlungsfenster weiter', function () {
        [$order] = mcpOrderWithChild();

        $response = $this->get(URL::temporarySignedRoute(
            'payment.micropayment.order', now()->addMinutes(30), ['order' => $order->id]
        ));

        $response->assertStatus(303);

        expect($response->headers->get('Location'))
            ->toStartWith('https://directbanking.micropayment.de/sofort/event/?')
            ->toContain('title=FK'.$order->created_at->year.'-'.$order->id)
            ->toContain('amount=4250')
            ->toContain('seal=');

        expect($order->fresh()->payment_gateway)->toBe('online_transfer');
    });

    it('weist einen Aufruf ohne Signatur ab', function () {
        [$order] = mcpOrderWithChild();

        $this->get(route('payment.micropayment.order', $order))
            ->assertRedirect(route('payment', $order));

        expect($order->fresh()->payment_gateway)->toBeNull();
    });

    it('weist eine abgelaufene Signatur ab', function () {
        [$order] = mcpOrderWithChild();

        $url = URL::temporarySignedRoute(
            'payment.micropayment.order', now()->subMinute(), ['order' => $order->id]
        );

        $this->get($url)->assertRedirect(route('payment', $order));
    });

    it('weist eine manipulierte Bestellnummer ab', function () {
        [$order] = mcpOrderWithChild();
        [$other] = mcpOrderWithChild();

        $url = URL::temporarySignedRoute(
            'payment.micropayment.order', now()->addMinutes(30), ['order' => $order->id]
        );

        $this->get(str_replace('/'.$order->id.'?', '/'.$other->id.'?', $url))
            ->assertRedirect(route('payment', $other));
    });

    it('zeigt ohne Zugangsdaten eine Vorschau statt weiterzuleiten', function (array $config) {
        config($config);

        [$order] = mcpOrderWithChild();

        $response = $this->get(URL::temporarySignedRoute(
            'payment.micropayment.order', now()->addMinutes(30), ['order' => $order->id]
        ));

        $response->assertOk()
            ->assertViewIs('micropayment.preview')
            ->assertSee(config('micropayment.online_transfer_url'), false)
            ->assertSee(route('payment.micropayment.notify'), false);
    })->with([
        'ohne AccessKey' => [['micropayment.access_key' => '']],
        'ohne Projekt' => [['micropayment.project' => '']],
    ]);

    // Fehlt nur das Projektkürzel, darf die Seite nicht den AccessKey
    // anmahnen - genau daran ist die erste Fassung gescheitert.
    it('benennt auf der Vorschau den tatsächlich fehlenden Eintrag', function () {
        config(['micropayment.project' => '']);

        [$order] = mcpOrderWithChild();

        $this->get(URL::temporarySignedRoute(
            'payment.micropayment.order', now()->addMinutes(30), ['order' => $order->id]
        ))
            ->assertOk()
            ->assertSee('MICROPAYMENT_PROJECT')
            ->assertDontSee('MICROPAYMENT_ACCESS_KEY');
    });

    it('leitet eine bereits bezahlte Bestellung auf die Ergebnisseite', function () {
        [$order] = mcpOrderWithChild(['payment_status' => 1]);

        $this->get(URL::temporarySignedRoute(
            'payment.micropayment.order', now()->addMinutes(30), ['order' => $order->id]
        ))->assertRedirect(route('payment.micropayment.result', [
            'state' => 'completed',
            'ref' => (new MicropaymentOrderSubject($order))->reference(),
            'token' => (new MicropaymentOrderSubject($order))->token(),
        ]));
    });

    it('schickt bei abgeschalteter Zahlart zurück zur Zahlungsart', function () {
        config(['micropayment.enabled' => false]);

        [$order] = mcpOrderWithChild();

        $this->get(URL::temporarySignedRoute(
            'payment.micropayment.order', now()->addMinutes(30), ['order' => $order->id]
        ))->assertRedirect(route('payment', $order));
    });
});

describe('Ergebnisseite', function () {
    it('zeigt die Bestelldaten nur mit passendem Merkmal', function () {
        [$order] = mcpOrderWithChild();

        $this->get(route('payment.micropayment.result', [
            'state' => 'completed',
            'ref' => (new MicropaymentOrderSubject($order))->reference(),
            'token' => (new MicropaymentOrderSubject($order))->token(),
        ]))->assertOk()->assertSee('FK'.$order->created_at->year.'-'.$order->id);
    });

    it('verschweigt die Bestelldaten ohne Merkmal', function ($token) {
        [$order] = mcpOrderWithChild();

        $this->get(route('payment.micropayment.result', array_filter([
            'state' => 'completed',
            'ref' => (new MicropaymentOrderSubject($order))->reference(),
            'token' => $token,
        ])))->assertOk()->assertDontSee('FK'.$order->created_at->year.'-'.$order->id);
    })->with([
        'ohne Merkmal' => [null],
        'falsches Merkmal' => ['falsch'],
        'fremdes Merkmal' => [str_repeat('a', 32)],
    ]);

    it('kommt ohne Bestellung aus', function () {
        $this->get(route('payment.micropayment.result', ['state' => 'completed']))->assertOk();
    });

    it('fängt einen unbekannten Zustand ab', function () {
        $this->get(route('payment.micropayment.result', ['state' => 'quatsch']))
            ->assertOk()
            ->assertSee('Danke für deine Bestellung');
    });
});

describe('Zahlungsart im Checkout', function () {
    it('leitet die Online-Überweisung auf die signierte Weiterleitung', function () {
        [$order] = mcpOrderWithChild();

        $response = $this->withSession(['checkout.order_id' => $order->id])
            ->post(route('payment.process'), [
                'payment_type' => 'online_transfer',
                'order_id' => $order->id,
            ]);

        $response->assertRedirect();

        expect($response->headers->get('Location'))
            ->toContain('/payment/micropayment/order/'.$order->id)
            ->toContain('signature=');
    });

    it('verlangt für die Online-Überweisung keine payment_id', function () {
        [$order] = mcpOrderWithChild();

        $this->withSession(['checkout.order_id' => $order->id])
            ->post(route('payment.process'), [
                'payment_type' => 'online_transfer',
                'order_id' => $order->id,
            ])->assertSessionHasNoErrors();
    });

    it('meldet eine unbekannte Bestellung zurück', function () {
        $this->from(route('checkout'))
            ->post(route('payment.process'), [
                'payment_type' => 'online_transfer',
                'order_id' => 999999,
            ])
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors();
    });

    // /payment/process ist ohne Anmeldung erreichbar und von der CSRF-Prüfung
    // ausgenommen. Die Zahlungsfenster-URL trägt Name und E-Mail-Adresse – ohne
    // Prüfung ließen sie sich über eine geratene Bestellnummer abrufen.
    it('gibt eine fremde Bestellung nicht zur Zahlung frei', function () {
        [$order] = mcpOrderWithChild();

        $this->from(route('checkout'))
            ->post(route('payment.process'), [
                'payment_type' => 'online_transfer',
                'order_id' => $order->id,
            ])
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors();
    });

    it('lässt die angemeldete Kundin ihre eigene Bestellung bezahlen', function () {
        $buyer = User::create([
            'name' => 'Käuferin',
            'last_name' => 'Beispiel',
            'email' => 'buyer@example.com',
            'status' => 1,
        ]);

        [$order] = mcpOrderWithChild(['user_id' => $buyer->id]);

        $this->actingAs($buyer)
            ->post(route('payment.process'), [
                'payment_type' => 'online_transfer',
                'order_id' => $order->id,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    });
});
