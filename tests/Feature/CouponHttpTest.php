<?php

use App\Coupon;
use App\Support\CouponSession;
use Tests\Support\CouponTestSchema;

/**
 * Die Gutschein-Endpunkte über den echten Request-Weg – inklusive Routing,
 * Validierung und Session. CouponTest prüft die Regeln dahinter; hier geht es
 * darum, dass sie am Endpunkt auch ankommen.
 *
 * Die Views werden dabei nicht gerendert: Alle geprüften Antworten sind
 * Redirects. Das Frontend-Layout fragt Tabellen ab, die im schlanken
 * Test-Schema nicht existieren.
 */
beforeEach(function () {
    CouponTestSchema::create();
});

afterEach(function () {
    CouponTestSchema::drop();
});

function couponFor(array $attributes = []): Coupon
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
 * Warenkorb direkt in die Session legen, aus der App\Support\Cart liest.
 * 'model' bleibt null, damit die Hydrierung keine Produkttabelle braucht.
 */
function cartSession(array $prices): array
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

    return ['cart.items' => $items];
}

describe('POST /add-coupon', function () {
    it('legt Rabatt und Code in die Session', function () {
        couponFor();

        $response = $this->withSession(cartSession([100.00]))
            ->from('/cart')
            ->post(route('coupon'), ['coupon_code' => 'SOMMER']);

        $response->assertRedirect('/cart')
            ->assertSessionHasNoErrors()
            ->assertSessionHas(CouponSession::DISCOUNT_KEY, 10.0)
            ->assertSessionHas(CouponSession::CODE_KEY, 'SOMMER');
    });

    /**
     * Der Kern der Umstellung: Vorher wurde beim Anwenden hochgezählt. Wer den
     * Gutschein im Warenkorb ein paar Mal an- und abwählte, hatte ihn damit
     * aufgebraucht, ohne je etwas zu bestellen.
     */
    it('zählt die Einlösung beim Anwenden noch nicht hoch', function () {
        couponFor(['limit' => 1, 'used' => 0]);

        $this->withSession(cartSession([100.00]))
            ->from('/cart')
            ->post(route('coupon'), ['coupon_code' => 'SOMMER']);

        expect(Coupon::query()->code('SOMMER')->first()->used)->toBe(0);
    });

    it('findet den Code auch mit Leerzeichen drumherum', function () {
        couponFor();

        $this->withSession(cartSession([100.00]))
            ->from('/cart')
            ->post(route('coupon'), ['coupon_code' => '  SOMMER  '])
            ->assertSessionHas(CouponSession::CODE_KEY, 'SOMMER');
    });

    it('weist einen unbekannten Code ab', function () {
        couponFor();

        $this->withSession(cartSession([100.00]))
            ->from('/cart')
            ->post(route('coupon'), ['coupon_code' => 'WINTER'])
            ->assertSessionHasErrors()
            ->assertSessionMissing(CouponSession::DISCOUNT_KEY);
    });

    it('weist einen abgelaufenen Gutschein ab', function () {
        couponFor(['expire_at' => now()->subDay()]);

        $this->withSession(cartSession([100.00]))
            ->from('/cart')
            ->post(route('coupon'), ['coupon_code' => 'SOMMER'])
            ->assertSessionHasErrors()
            ->assertSessionMissing(CouponSession::DISCOUNT_KEY);
    });

    it('weist einen unterschrittenen Mindestbestellwert ab', function () {
        couponFor(['minimum_cart' => 50.00]);

        $this->withSession(cartSession([30.00]))
            ->from('/cart')
            ->post(route('coupon'), ['coupon_code' => 'SOMMER'])
            ->assertSessionHasErrors()
            ->assertSessionMissing(CouponSession::DISCOUNT_KEY);
    });

    it('weist einen Gutschein ab, der den ganzen Warenkorb deckt', function () {
        couponFor(['discount' => 30.00, 'minimum_cart' => 0]);

        $this->withSession(cartSession([30.00]))
            ->from('/cart')
            ->post(route('coupon'), ['coupon_code' => 'SOMMER'])
            ->assertSessionHasErrors()
            ->assertSessionMissing(CouponSession::DISCOUNT_KEY);
    });

    it('verlangt überhaupt einen Code', function () {
        $this->withSession(cartSession([100.00]))
            ->from('/cart')
            ->post(route('coupon'), [])
            ->assertSessionHasErrors('coupon_code');
    });
});

describe('POST /delete-coupon', function () {
    it('räumt Rabatt und Code aus der Session', function () {
        $session = cartSession([100.00]) + [
            CouponSession::DISCOUNT_KEY => 10.0,
            CouponSession::CODE_KEY => 'SOMMER',
        ];

        $this->withSession($session)
            ->from('/cart')
            ->post(route('coupon.destroy'))
            ->assertRedirect('/cart')
            ->assertSessionMissing(CouponSession::DISCOUNT_KEY)
            ->assertSessionMissing(CouponSession::CODE_KEY);
    });

    /**
     * Als GET-Link reichte jeder Prefetch oder Crawler, um der Kundin den
     * Rabatt wegzunehmen.
     */
    it('ist per GET nicht mehr erreichbar', function () {
        $this->get('/delete-coupon')->assertMethodNotAllowed();
    });
});
