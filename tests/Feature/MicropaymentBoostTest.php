<?php

use App\Models\Boost;
use App\Models\Payment;
use App\Models\User;
use App\Order;
use App\Package;
use App\Payment\MicropaymentBoostSubject;
use App\Payment\MicropaymentOrderSubject;
use App\Payment\MicropaymentSubject;
use App\Product;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Tests\Support\MicropaymentTestSchema;

beforeEach(function () {
    MicropaymentTestSchema::create();

    Mail::fake();

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
        'settings.finance.vat' => 19,
    ]);
});

afterEach(function () {
    MicropaymentTestSchema::drop();
});

/**
 * Legt eine Hervorhebung samt Zahlung an – so wie BoostController::boostStore().
 *
 * `profile` bildet „Profil pushen“ ab: geboostet wird die Verkäuferin selbst.
 * `product` bildet die Hervorhebung eines Artikels ab.
 *
 * @return array{0: Payment, 1: Boost, 2: User|Product}
 */
function mcpBoost(string $kind = 'profile', array $attributes = []): array
{
    $user = User::create([
        'name' => 'Verkäuferin',
        'last_name' => 'Beispiel',
        'email' => 'seller@example.com',
        'status' => 1,
    ]);

    $package = Package::create([
        'name' => 'Push 30 Tage',
        'price' => 50.00,
        'days' => 30,
    ]);

    $boostable = $kind === 'profile'
        ? $user
        : Product::create(['user_id' => $user->id, 'name' => 'Testprodukt', 'price' => 10.00, 'status' => 1]);

    $boost = Boost::create(array_merge([
        'package_id' => $package->id,
        'user_id' => $user->id,
        'boostable_id' => $boostable->id,
        'boostable_type' => $boostable::class,
        'price' => 59.50,
        'base_price' => 50.00,
        'tax' => 9.50,
        'status' => 0,
        'user_info' => [
            'f_name' => 'Verkäuferin',
            'l_name' => 'Beispiel',
            'email' => 'seller@example.com',
        ],
    ], $attributes));

    $payment = $boost->payment()->create([
        'status' => 'PENDING',
        'tax' => 9.50,
        'amount' => 59.50,
    ]);

    return [$payment->fresh(), $boost->fresh(), $boostable->fresh()];
}

/**
 * Gültige Benachrichtigung für eine Hervorhebung.
 */
function mcpBoostNotification(Payment $payment, array $overrides = []): array
{
    $subject = new MicropaymentBoostSubject($payment);

    return array_merge([
        'function' => 'billing',
        'title' => $subject->reference(),
        'amount' => $subject->amountInCents(),
        'currency' => 'EUR',
        'auth' => 'mcp-trx-boost',
        'testmode' => 1,
        'secretfield' => 'geheim123',
        'orderToken' => $subject->token(),
    ], $overrides);
}

function mcpBoostNotify(array $query): array
{
    $response = test()->get(route('payment.micropayment.notify', $query));

    $response->assertOk();

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

describe('Referenz der Hervorhebung', function () {
    it('trägt ein eigenes Präfix', function () {
        [$payment] = mcpBoost();

        expect((new MicropaymentBoostSubject($payment))->reference())
            ->toBe('BP'.$payment->created_at->year.'-'.$payment->id);
    });

    // Bestellung 1 und Hervorhebung 1 dürfen sich nicht verwechseln lassen.
    it('führt zur Hervorhebung und nicht zur gleichnamigen Bestellung', function () {
        [$payment] = mcpBoost();

        $subject = MicropaymentSubject::fromReference('BP'.$payment->created_at->year.'-'.$payment->id);

        expect($subject)->toBeInstanceOf(MicropaymentBoostSubject::class)
            ->and($subject->payment->id)->toBe($payment->id);
    });

    it('vergibt ein anderes Merkmal als eine Bestellung mit derselben Nummer', function () {
        [$payment] = mcpBoost();

        $order = Order::create(['email' => 'x@example.com', 'total' => 59.50]);

        expect((new MicropaymentBoostSubject($payment))->token())
            ->not->toBe((new MicropaymentOrderSubject($order))->token());
    });

    it('rechnet den Betrag der Zahlung in Cent um', function () {
        [$payment] = mcpBoost();

        expect((new MicropaymentBoostSubject($payment))->amountInCents())->toBe(5950);
    });

    // Abgebucht wird der Bruttobetrag der Zahlung, nicht der Nettopreis des
    // Pakets. Sonst zahlte die Verkäuferin die MwSt. nicht mit.
    it('bucht den Bruttobetrag ab, nicht den Nettopreis', function () {
        [$payment] = mcpBoost();

        $brutto = (float) $payment->amount;
        $mwst = (float) $payment->tax;

        expect($brutto - $mwst)->toEqualWithDelta(50.00, 0.005)
            ->and((new MicropaymentBoostSubject($payment))->amountInCents())
            ->toBe((int) round($brutto * 100));
    });

    // boost.tax läuft über Shop::tax() und liefert ab 50 € Paketpreis 0 zurück –
    // die MwSt. steht dann nur auf der Zahlung. Wer den Betrag am Boost statt an
    // der Zahlung ablesen würde, bekäme hier zu wenig.
    it('verlässt sich nicht auf boost.tax', function () {
        [$payment, $boost] = mcpBoost('profile', ['tax' => 0]);

        expect($boost->fresh()->tax)->toEqual(0.0)
            ->and($payment->tax)->toEqual(9.50)
            ->and((new MicropaymentBoostSubject($payment))->amountInCents())->toBe(5950);
    });

    it('nennt das Paket im Verwendungszweck', function () {
        [$payment] = mcpBoost();

        expect((new MicropaymentBoostSubject($payment))->paytext())->toContain('Push 30 Tage');
    });

    it('übernimmt die beim Anlegen eingefrorenen Kundendaten', function () {
        [$payment] = mcpBoost();

        $subject = new MicropaymentBoostSubject($payment);

        expect($subject->firstName())->toBe('Verkäuferin')
            ->and($subject->lastName())->toBe('Beispiel')
            ->and($subject->email())->toBe('seller@example.com');
    });
});

describe('Weiterleitung zum Zahlungsfenster', function () {
    it('leitet mit gültiger Signatur weiter', function () {
        [$payment] = mcpBoost();

        $response = $this->get(URL::temporarySignedRoute(
            'payment.micropayment.boost', now()->addMinutes(30), ['payment' => $payment->id]
        ));

        $response->assertStatus(303);

        expect($response->headers->get('Location'))
            ->toStartWith('https://directbanking.micropayment.de/sofort/event/?')
            ->toContain('title=BP'.$payment->created_at->year.'-'.$payment->id)
            ->toContain('amount=5950');

        // Zahlart festgehalten, Status aber weiterhin offen.
        expect($payment->fresh())
            ->payment_method->toBe('online_transfer')
            ->status->toBe('PENDING');
    });

    it('weist einen Aufruf ohne Signatur ab', function () {
        [$payment] = mcpBoost();

        $this->get(route('payment.micropayment.boost', $payment))
            ->assertRedirect(route('seller.payment', $payment));

        expect($payment->fresh()->payment_method)->toBeNull();
    });

    it('leitet eine bereits bezahlte Hervorhebung auf die Ergebnisseite', function () {
        [$payment] = mcpBoost();
        $payment->update(['status' => 'PAID']);

        $subject = new MicropaymentBoostSubject($payment->fresh());

        $this->get(URL::temporarySignedRoute(
            'payment.micropayment.boost', now()->addMinutes(30), ['payment' => $payment->id]
        ))->assertRedirect(route('payment.micropayment.result', [
            'state' => 'completed',
            'ref' => $subject->reference(),
            'token' => $subject->token(),
        ]));
    });
});

describe('Zahlungseingang schaltet frei', function () {
    it('schaltet ein gepushtes Profil frei', function () {
        [$payment, $boost, $user] = mcpBoost('profile');

        expect($user->boosted)->toBe(0);

        $result = mcpBoostNotify(mcpBoostNotification($payment));

        expect($result['status'])->toBe('ok')
            ->and($result['url'])->toContain('/result/completed');

        expect($payment->fresh())
            ->status->toBe('PAID')
            ->payment_trnx_id->toBe('mcp-trx-boost')
            ->payment_method->toBe('online_transfer');

        expect($boost->fresh()->status)->toBe(1);

        expect($user->fresh())
            ->boosted->toBe(1)
            ->boost_start_date->not->toBeNull()
            ->boost_end_date->not->toBeNull();
    });

    it('schaltet ein gepushtes Produkt frei', function () {
        [$payment, , $product] = mcpBoost('product');

        mcpBoostNotify(mcpBoostNotification($payment));

        expect($product->fresh()->boosted)->toBe(1);
    });

    it('setzt das Enddatum auf die Laufzeit des Pakets', function () {
        [$payment, , $user] = mcpBoost('profile');

        mcpBoostNotify(mcpBoostNotification($payment));

        // User castet boost_end_date nicht, der Wert kommt als Zeichenkette zurück.
        expect(Carbon\Carbon::parse($user->fresh()->boost_end_date)->toDateString())
            ->toBe(now()->addDays(30)->toDateString());
    });

    // Micropayment stellt eine Meldung notfalls erneut zu. Ein zweiter Durchlauf
    // dürfte die Laufzeit nicht nach hinten schieben.
    it('verschiebt bei doppelter Zustellung die Laufzeit nicht', function () {
        [$payment, , $user] = mcpBoost('profile');

        mcpBoostNotify(mcpBoostNotification($payment));
        $ende = (string) $user->fresh()->boost_end_date;

        mcpBoostNotify(mcpBoostNotification($payment, ['auth' => 'mcp-trx-zweiter-versuch']));

        expect((string) $user->fresh()->boost_end_date)->toBe($ende);

        // Auch die Transaktionsnummer des ersten Eingangs bleibt stehen.
        expect($payment->fresh()->payment_trnx_id)->toBe('mcp-trx-boost');
    });
});

describe('Abwehr beim Boost', function () {
    it('schaltet bei abweichendem Betrag nicht frei', function () {
        [$payment, $boost, $user] = mcpBoost('profile');

        $result = mcpBoostNotify(mcpBoostNotification($payment, ['amount' => 1]));

        expect($result['url'])->toContain('/result/mismatch');

        expect($payment->fresh()->status)->toBe('PENDING')
            ->and($boost->fresh()->status)->toBe(0)
            ->and($user->fresh()->boosted)->toBe(0);
    });

    it('schaltet bei falschem Geheimfeld nicht frei', function () {
        [$payment, , $user] = mcpBoost('profile');

        $result = mcpBoostNotify(mcpBoostNotification($payment, ['secretfield' => 'falsch']));

        expect($result['status'])->toBe('error');
        expect($user->fresh()->boosted)->toBe(0);
    });

    it('schaltet bei falschem Merkmal nicht frei', function () {
        [$payment, , $user] = mcpBoost('profile');

        $result = mcpBoostNotify(mcpBoostNotification($payment, ['orderToken' => str_repeat('f', 32)]));

        expect($result['status'])->toBe('error');
        expect($user->fresh()->boosted)->toBe(0);
    });

    it('schaltet bei pending nicht frei', function () {
        [$payment, , $user] = mcpBoost('profile');

        $result = mcpBoostNotify(mcpBoostNotification($payment, ['function' => 'pending']));

        expect($result['url'])->toContain('/result/pending');

        expect($payment->fresh()->status)->toBe('PENDING')
            ->and($user->fresh()->boosted)->toBe(0);
    });

    it('schaltet bei einem Fehlerereignis nicht frei', function ($event) {
        [$payment, , $user] = mcpBoost('profile');

        mcpBoostNotify(mcpBoostNotification($payment, ['function' => $event]));

        expect($payment->fresh()->status)->toBe('PENDING')
            ->and($user->fresh()->boosted)->toBe(0);
    })->with(['error', 'info']);

    it('nimmt eine Hervorhebung bei Storno nicht selbsttätig zurück', function () {
        [$payment, , $user] = mcpBoost('profile');

        mcpBoostNotify(mcpBoostNotification($payment));
        mcpBoostNotify(mcpBoostNotification($payment, ['function' => 'storno']));

        expect($user->fresh()->boosted)->toBe(1);
    });
});

describe('Ergebnisseite der Hervorhebung', function () {
    it('zeigt Referenz und Betrag mit passendem Merkmal', function () {
        [$payment] = mcpBoost();

        $subject = new MicropaymentBoostSubject($payment);

        $this->get(route('payment.micropayment.result', [
            'state' => 'completed',
            'ref' => $subject->reference(),
            'token' => $subject->token(),
        ]))->assertOk()
            ->assertSee($subject->reference())
            ->assertSee('59,50 €');
    });

    it('verschweigt sie ohne Merkmal', function () {
        [$payment] = mcpBoost();

        $subject = new MicropaymentBoostSubject($payment);

        $this->get(route('payment.micropayment.result', [
            'state' => 'completed',
            'ref' => $subject->reference(),
        ]))->assertOk()->assertDontSee($subject->reference());
    });

    // `?ref[]=x` liefert ein Array statt einer Zeichenkette. Die Seite muss
    // darauf mit einer Statusmeldung antworten, nicht mit einem Fehler 500.
    it('kommt mit Parametern in Listenform zurecht', function () {
        $this->get(route('payment.micropayment.result', ['state' => 'completed']).'?ref[]=FK2026-1&token[]=x')
            ->assertOk();
    });
});

describe('Bezahlseite der Hervorhebung', function () {
    // Die Zahlungsnummern sind fortlaufend und die Route kennt nur `role:seller`.
    // Ohne Prüfung bekäme jede Verkäuferin über den signierten Bezahllink Name
    // und E-Mail-Adresse jeder anderen.
    it('zeigt einer fremden Verkäuferin nichts an', function () {
        [$payment] = mcpBoost();

        $fremde = User::create([
            'name' => 'Fremde',
            'last_name' => 'Verkäuferin',
            'email' => 'fremde@example.com',
            'status' => 1,
        ]);

        $this->actingAs($fremde)
            ->withoutMiddleware()
            ->get(route('seller.payment', $payment))
            ->assertNotFound();
    });
});
