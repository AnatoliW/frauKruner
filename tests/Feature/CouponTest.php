<?php

use App\Coupon;
use App\Http\Controllers\CheckoutController;
use App\Order;
use App\Support\CouponMessages;
use App\Support\CouponSession;
use Illuminate\Support\Facades\Mail;
use Tests\Support\CouponTestSchema;

beforeEach(function () {
    CouponTestSchema::create();
});

afterEach(function () {
    CouponTestSchema::drop();
});

function coupon(array $attributes = []): Coupon
{
    return Coupon::create(array_merge([
        'code' => 'SOMMER',
        'discount' => 10.00,
        'expire_at' => now()->addMonth(),
        'limit' => 1,
        'minimum_cart' => 20.00,
        'used' => 0,
    ], $attributes));
}

/**
 * Ruft eine private Methode des CheckoutControllers auf. splitDiscount() ist
 * bewusst nicht öffentlich, die Aufteilung von Geldbeträgen soll aber direkt
 * geprüft werden können.
 */
function callPrivate(object $object, string $method, array $arguments = [])
{
    $reflection = new ReflectionMethod($object, $method);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs($object, $arguments);
}

/**
 * Legt Positionen direkt in die Session, aus der App\Support\Cart liest.
 * 'model' bleibt null, damit die Hydrierung keine Produkttabelle braucht.
 */
function putCart(array $prices): void
{
    $items = [];

    foreach ($prices as $index => $price) {
        $id = (string) ($index + 1);

        $items[$id] = [
            'id' => $id,
            'name' => 'Artikel '.$id,
            'price' => $price,
            'quantity' => 1,
            'attributes' => [],
            'model' => null,
        ];
    }

    session()->put('cart.items', $items);
}

function cartItems(array $prices, int $quantity = 1): Illuminate\Support\Collection
{
    return collect($prices)->map(fn ($price) => (object) [
        'price' => $price,
        'quantity' => $quantity,
    ]);
}

describe('Coupon::scopeCode', function () {
    it('findet den Gutschein unabhängig von umgebenden Leerzeichen', function () {
        coupon(['code' => 'SOMMER']);

        expect(Coupon::query()->code('  SOMMER  ')->first())->not->toBeNull();
    });

    it('gibt null zurück, wenn es den Code nicht gibt', function () {
        coupon(['code' => 'SOMMER']);

        expect(Coupon::query()->code('WINTER')->first())->toBeNull();
    });
});

it('trimmt den Code beim Speichern', function () {
    $coupon = coupon(['code' => '  RABATT  ']);

    expect($coupon->fresh()->code)->toBe('RABATT');
});

describe('Coupon::isExpired', function () {
    it('gilt am Ablauftag noch', function () {
        expect(coupon(['expire_at' => now()])->isExpired())->toBeFalse();
    });

    it('gilt am Folgetag nicht mehr', function () {
        expect(coupon(['expire_at' => now()->subDay()])->isExpired())->toBeTrue();
    });
});

describe('Coupon::rejectionFor', function () {
    it('lehnt einen abgelaufenen Gutschein ab', function () {
        expect(coupon(['expire_at' => now()->subDay()])->rejectionFor(100.0))
            ->toBe(Coupon::REJECT_EXPIRED);
    });

    it('lehnt einen aufgebrauchten Gutschein ab', function () {
        expect(coupon(['limit' => 2, 'used' => 2])->rejectionFor(100.0))
            ->toBe(Coupon::REJECT_USED_UP);
    });

    it('lehnt einen leeren Warenkorb ab', function () {
        expect(coupon()->rejectionFor(0.0))->toBe(Coupon::REJECT_EMPTY_CART);
    });

    it('lehnt einen unterschrittenen Mindestbestellwert ab', function () {
        expect(coupon(['minimum_cart' => 50.00])->rejectionFor(49.99))
            ->toBe(Coupon::REJECT_MINIMUM_CART);
    });

    /**
     * Bliebe kein Restbetrag übrig, wäre die Bestellung 0 € wert – Stripe und
     * PayPal lehnen das ab. Der Gutschein muss vorher abgewiesen werden.
     */
    it('lehnt einen Gutschein ab, der den Warenkorb vollständig deckt', function () {
        expect(coupon(['discount' => 20.00, 'minimum_cart' => 0])->rejectionFor(20.00))
            ->toBe(Coupon::REJECT_COVERS_CART);
    });

    it('lehnt einen Gutschein ab, der über dem Warenkorbwert liegt', function () {
        expect(coupon(['discount' => 50.00, 'minimum_cart' => 0])->rejectionFor(20.00))
            ->toBe(Coupon::REJECT_COVERS_CART);
    });

    it('lässt einen Gutschein zu, wenn ein Restbetrag bleibt', function () {
        expect(coupon(['discount' => 19.99, 'minimum_cart' => 0])->rejectionFor(20.00))->toBeNull();
    });

    it('gibt null zurück, wenn der Gutschein passt', function () {
        expect(coupon(['minimum_cart' => 20.00])->rejectionFor(20.00))->toBeNull();
    });
});

describe('CouponMessages', function () {
    it('nennt den Mindestbestellwert im Klartext', function () {
        $coupon = coupon(['minimum_cart' => 50.00]);

        expect(CouponMessages::forRejection($coupon, Coupon::REJECT_MINIMUM_CART))
            ->toContain('50.00 €');
    });

    it('hat für jeden Reason-Code einen eigenen Satz', function () {
        $coupon = coupon();

        $reasons = [
            Coupon::REJECT_EXPIRED,
            Coupon::REJECT_USED_UP,
            Coupon::REJECT_EMPTY_CART,
            Coupon::REJECT_MINIMUM_CART,
            Coupon::REJECT_COVERS_CART,
        ];

        $messages = array_map(fn ($reason) => CouponMessages::forRejection($coupon, $reason), $reasons);

        expect($messages)->toHaveCount(count(array_unique($messages)));
    });

    it('weist beim Entfernen auf den geänderten Gesamtbetrag hin', function () {
        expect(CouponMessages::removed('Dieser Gutschein ist abgelaufen.'))
            ->toContain('Gesamtbetrag');
    });
});

describe('Coupon::discountForCart', function () {
    it('gibt den vollen Rabatt zurück, solange der Warenkorb größer ist', function () {
        expect(coupon(['discount' => 10.00])->discountForCart(100.0))->toBe(10.0);
    });

    it('deckelt den Rabatt auf den Warenkorbwert', function () {
        expect(coupon(['discount' => 50.00])->discountForCart(20.0))->toBe(20.0);
    });
});

describe('Coupon::redeem', function () {
    it('erhöht den Zähler und meldet Erfolg', function () {
        $coupon = coupon(['limit' => 3, 'used' => 0]);

        expect($coupon->redeem())->toBeTrue()
            ->and($coupon->fresh()->used)->toBe(1);
    });

    it('bucht nicht über das Limit hinaus', function () {
        $coupon = coupon(['limit' => 1, 'used' => 1]);

        expect($coupon->redeem())->toBeFalse()
            ->and($coupon->fresh()->used)->toBe(1);
    });

    it('lässt bei parallelen Einlösungen nur so viele durch wie das Limit erlaubt', function () {
        coupon(['limit' => 2, 'used' => 0]);

        // Zwei Instanzen mit demselben veralteten Stand – so sieht der Fall
        // aus, wenn zwei Bestellungen gleichzeitig abgeschlossen werden.
        $first = Coupon::query()->code('SOMMER')->first();
        $second = Coupon::query()->code('SOMMER')->first();
        $third = Coupon::query()->code('SOMMER')->first();

        expect($first->redeem())->toBeTrue()
            ->and($second->redeem())->toBeTrue()
            ->and($third->redeem())->toBeFalse()
            ->and($first->fresh()->used)->toBe(2);
    });
});

describe('CheckoutController::splitDiscount', function () {
    it('verteilt den Rabatt proportional zum Positionswert', function () {
        $shares = callPrivate(
            new CheckoutController,
            'splitDiscount',
            [cartItems([30.00, 10.00]), 20.00]
        );

        expect(array_values($shares))->toBe([15.0, 5.0]);
    });

    it('verteilt auch krumme Beträge cent-genau', function () {
        $discount = 10.00;

        $shares = callPrivate(
            new CheckoutController,
            'splitDiscount',
            [cartItems([10.00, 10.00, 10.00]), $discount]
        );

        expect(array_sum($shares))->toBe($discount)
            ->and(array_values($shares))->toBe([3.34, 3.33, 3.33]);
    });

    it('ergibt in Summe immer exakt den Rabatt der Hauptbestellung', function () {
        $discount = 17.77;

        $shares = callPrivate(
            new CheckoutController,
            'splitDiscount',
            [cartItems([19.99, 5.01, 73.50, 0.99]), $discount]
        );

        expect(round(array_sum($shares), 2))->toBe($discount);
    });

    it('gibt ohne Rabatt überall 0 zurück', function () {
        $shares = callPrivate(
            new CheckoutController,
            'splitDiscount',
            [cartItems([30.00, 10.00]), 0.0]
        );

        expect(array_values($shares))->toBe([0.0, 0.0]);
    });

    it('kommt mit einem leeren Warenkorb klar', function () {
        $shares = callPrivate(
            new CheckoutController,
            'splitDiscount',
            [collect(), 10.00]
        );

        expect($shares)->toBe([]);
    });

    /**
     * Die Gewichtung läuft über price * quantity, genau wie die
     * Zwischensumme der Hauptbestellung und der Positionswert der
     * Unterbestellung. Sonst zöge die Rechnung einen Rabatt für die ganze
     * Position von einem Einzelpreis ab.
     */
    it('gewichtet nach Positionswert, nicht nach Stückpreis', function () {
        $shares = callPrivate(
            new CheckoutController,
            'splitDiscount',
            [cartItems([10.00, 10.00], quantity: 3), 30.00]
        );

        expect(array_values($shares))->toBe([15.0, 15.0]);
    });
});

describe('CouponSession::revalidate', function () {
    beforeEach(function () {
        session()->flush();
    });

    it('rechnet den Rabatt gegen den aktuellen Warenkorb neu', function () {
        coupon(['code' => 'SOMMER', 'discount' => 10.00, 'minimum_cart' => 20.00]);
        putCart([100.00]);
        CouponSession::apply(Coupon::query()->code('SOMMER')->first(), 100.00);

        expect(CouponSession::revalidate())->toBeNull()
            ->and(CouponSession::discount())->toBe(10.0);
    });

    /**
     * Der Kern des Fixes: Wer nach dem Einlösen Artikel entfernt, darf den
     * Rabatt nicht behalten, nur weil er in der Session steht.
     */
    it('entfernt den Gutschein, wenn der Warenkorb unter den Mindestwert fällt', function () {
        coupon(['code' => 'SOMMER', 'discount' => 10.00, 'minimum_cart' => 50.00]);
        putCart([100.00]);
        CouponSession::apply(Coupon::query()->code('SOMMER')->first(), 100.00);

        putCart([30.00]);

        expect(CouponSession::revalidate())->toContain('Mindesteinkaufswert')
            ->and(CouponSession::discount())->toBe(0.0)
            ->and(CouponSession::code())->toBeNull();
    });

    it('entfernt den Gutschein, wenn er zwischenzeitlich abgelaufen ist', function () {
        $coupon = coupon(['code' => 'SOMMER', 'minimum_cart' => 0]);
        putCart([100.00]);
        CouponSession::apply($coupon, 100.00);

        $coupon->update(['expire_at' => now()->subDay()]);

        expect(CouponSession::revalidate())->toContain('abgelaufen')
            ->and(CouponSession::discount())->toBe(0.0);
    });

    it('entfernt den Gutschein, wenn er im Backend gelöscht wurde', function () {
        $coupon = coupon(['code' => 'SOMMER', 'minimum_cart' => 0]);
        putCart([100.00]);
        CouponSession::apply($coupon, 100.00);

        $coupon->delete();

        expect(CouponSession::revalidate())->toContain('nicht mehr gültig')
            ->and(CouponSession::discount())->toBe(0.0);
    });

    it('verwirft einen Rabatt ohne zugehörigen Code', function () {
        session()->put(CouponSession::DISCOUNT_KEY, 25.00);

        expect(CouponSession::revalidate())->toBeNull()
            ->and(CouponSession::discount())->toBe(0.0);
    });

    it('tut nichts, wenn gar kein Gutschein hinterlegt ist', function () {
        putCart([100.00]);

        expect(CouponSession::revalidate())->toBeNull()
            ->and(CouponSession::discount())->toBe(0.0);
    });

    /**
     * Der Gutschein gilt weiter, ist aber weniger wert als beim Anwenden. Ohne
     * Meldung würde die Bestellung teurer angelegt als der Betrag, den die
     * Kundin auf der Seite gesehen hat.
     */
    it('meldet, wenn sich der Rabattbetrag geändert hat', function () {
        $coupon = coupon(['code' => 'SOMMER', 'discount' => 10.00, 'minimum_cart' => 0]);
        putCart([100.00]);
        CouponSession::apply($coupon, 100.00);

        $coupon->update(['discount' => 5.00]);

        expect(CouponSession::revalidate())->toContain('geändert')
            ->and(CouponSession::discount())->toBe(5.0)
            ->and(CouponSession::code())->toBe('SOMMER');
    });

    it('meldet nichts, solange der Betrag gleich bleibt', function () {
        $coupon = coupon(['code' => 'SOMMER', 'discount' => 10.00, 'minimum_cart' => 0]);
        putCart([100.00]);
        CouponSession::apply($coupon, 100.00);

        // Weniger im Warenkorb, aber immer noch über dem Mindestwert: Der feste
        // Rabattbetrag ändert sich dadurch nicht.
        putCart([80.00]);

        expect(CouponSession::revalidate())->toBeNull()
            ->and(CouponSession::discount())->toBe(10.0);
    });
});

describe('Order::redeemCoupon', function () {
    it('bucht die Einlösung genau einmal', function () {
        coupon(['code' => 'SOMMER', 'limit' => 5, 'used' => 0]);

        $order = Order::create(['discount_code' => 'SOMMER', 'discount' => 10.00]);

        $order->redeemCoupon();
        $order->redeemCoupon();

        expect(Coupon::query()->code('SOMMER')->first()->used)->toBe(1)
            ->and($order->fresh()->coupon_redeemed_at)->not->toBeNull();
    });

    it('bucht nichts ohne Gutschein an der Bestellung', function () {
        coupon(['code' => 'SOMMER', 'limit' => 5, 'used' => 0]);

        Order::create(['discount_code' => null, 'discount' => 0])->redeemCoupon();

        expect(Coupon::query()->code('SOMMER')->first()->used)->toBe(0);
    });

    /**
     * Das Limit wurde zwischen der Eingabe im Warenkorb und dem Abschluss der
     * Bestellung ausgeschöpft. Die Bestellung darf davon unberührt bleiben –
     * nachträglich teurer machen ist keine Option.
     */
    it('lässt die Bestellung bestehen, wenn das Limit inzwischen voll ist', function () {
        coupon(['code' => 'SOMMER', 'limit' => 1, 'used' => 1]);

        $order = Order::create(['discount_code' => 'SOMMER', 'discount' => 10.00]);

        $order->redeemCoupon();

        expect(Coupon::query()->code('SOMMER')->first()->used)->toBe(1)
            ->and($order->fresh()->discount)->toEqual(10.00);
    });

    /**
     * Der volle Rabatt steht auf der Hauptbestellung, die Unterbestellungen
     * tragen nur Anteile. Gebucht werden darf deshalb nur oben, sonst zählt
     * jede Position als eigene Einlösung.
     */
    it('bucht über couponOrder() immer auf der Hauptbestellung', function () {
        coupon(['code' => 'SOMMER', 'limit' => 5, 'used' => 0]);

        $parent = Order::create(['discount_code' => 'SOMMER', 'discount' => 10.00]);
        $first = Order::create(['parent_id' => $parent->id, 'discount_code' => 'SOMMER', 'discount' => 6.00]);
        $second = Order::create(['parent_id' => $parent->id, 'discount_code' => 'SOMMER', 'discount' => 4.00]);

        $first->couponOrder()->redeemCoupon();
        $second->couponOrder()->redeemCoupon();

        expect(Coupon::query()->code('SOMMER')->first()->used)->toBe(1)
            ->and($parent->fresh()->coupon_redeemed_at)->not->toBeNull()
            ->and($first->fresh()->coupon_redeemed_at)->toBeNull();
    });
});

describe('Order::markAsPaid', function () {
    /**
     * Der Gutschein hängt am Abschluss der Bestellung, nicht am Zahlungseingang:
     * Gebucht wird in CheckoutController::processPayment(), sobald die Kundin die
     * Zahlungsart bestätigt hat – bei Vorkasse genauso wie bei Stripe und PayPal.
     * Eine Vorkasse-Bestellung, die nie bezahlt wird, hat den Gutschein trotzdem
     * verbraucht.
     *
     * markAsPaid() darf deshalb nicht zusätzlich buchen. Sonst würde jede
     * Vorkasse-Bestellung, die noch unter der alten Regel angelegt wurde, beim
     * Setzen auf "bezahlt" ein zweites Mal zählen.
     */
    it('bucht den Gutschein nicht noch einmal', function () {
        Mail::fake();

        coupon(['code' => 'SOMMER', 'limit' => 5, 'used' => 0]);

        $order = Order::create([
            'discount_code' => 'SOMMER',
            'discount' => 10.00,
            'total' => 90.00,
            'payment_gateway' => 'pre_payment',
            'payment_status' => 0,
        ]);

        expect($order->markAsPaid())->toBeTrue()
            ->and(Coupon::query()->code('SOMMER')->first()->used)->toBe(0)
            ->and($order->fresh()->coupon_redeemed_at)->toBeNull()
            ->and($order->fresh()->payment_status)->toBe(1);
    });

    /**
     * Der Normalfall: Beim Abschluss der Bestellung wurde bereits gebucht. Der
     * Zahlungseingang darf den Zähler nicht ein zweites Mal hochsetzen.
     */
    it('lässt eine bereits gebuchte Einlösung unverändert', function () {
        Mail::fake();

        coupon(['code' => 'SOMMER', 'limit' => 5, 'used' => 0]);

        $order = Order::create([
            'discount_code' => 'SOMMER',
            'discount' => 10.00,
            'payment_gateway' => 'pre_payment',
            'payment_status' => 0,
        ]);

        // So, wie processPayment() es beim Bestätigen der Zahlungsart tut.
        $order->redeemCoupon();
        $order->markAsPaid();

        expect(Coupon::query()->code('SOMMER')->first()->used)->toBe(1);
    });
});

describe('Order::claimConfirmation', function () {
    it('gibt nur beim ersten Aufruf true zurück', function () {
        $order = Order::create(['email' => 'kundin@example.test']);

        expect($order->claimConfirmation())->toBeTrue()
            ->and($order->claimConfirmation())->toBeFalse()
            ->and($order->fresh()->confirmed_at)->not->toBeNull();
    });

    it('beansprucht jede Bestellung für sich', function () {
        $first = Order::create(['email' => 'a@example.test']);
        $second = Order::create(['email' => 'b@example.test']);

        expect($first->claimConfirmation())->toBeTrue()
            ->and($second->claimConfirmation())->toBeTrue();
    });
});
