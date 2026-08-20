<?php

use App\Order;
use App\Payment\MicropaymentGateway;
use App\Payment\MicropaymentOrderSubject;
use App\Payment\MicropaymentSubject;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'micropayment.enabled' => true,
        'micropayment.access_key' => 'TEST-ACCESSKEY',
        'micropayment.project' => 'fraukruner',
        'micropayment.testmode' => true,
        'micropayment.language' => 'de',
        'micropayment.online_transfer_url' => 'https://directbanking.micropayment.de/sofort/event/',
    ]);
});

/**
 * Bestellung ohne Datenbank. Reicht überall dort, wo nur gerechnet und
 * formatiert wird.
 */
function mcpOrder(array $attributes = []): MicropaymentOrderSubject
{
    $order = new Order(array_merge([
        'first_name' => 'Anna',
        'last_name' => 'Müller-Lüdenscheidt',
        'email' => 'anna@example.com',
        'total' => 42.50,
    ], $attributes));

    $order->id = $attributes['id'] ?? 4711;
    $order->exists = true;
    $order->created_at = Carbon\Carbon::parse('2026-03-05 10:00:00');

    return new MicropaymentOrderSubject($order);
}

/**
 * Signatur exakt so, wie sie das offizielle Beispielpaket berechnet
 * (php_integration-web_example-advanced/tools.php).
 *
 * Der Nachbau steht bewusst hier im Test und nicht in der Anwendung: Er ist der
 * unabhängige Maßstab, gegen den MicropaymentGateway geprüft wird.
 */
function referenceSealUrl(string $accessKey, string $url): string
{
    $matches = [];
    preg_match('/^(http(?:s?):\/\/[^?]*?\?)?\?*(.*?)(?:&?seal=([^&]*)(&?.*)?)?$/', $url, $matches);
    $matches = array_merge($matches, [1 => '', 2 => '', 3 => '', 4 => '']);

    return (string) $matches[1].(string) $matches[2].'&seal='.md5(urldecode((string) $matches[2]).$accessKey).(string) $matches[4];
}

function referencePaymentWindowUrl(string $accessKey, string $baseUrl, array $secured, array $unsecured): string
{
    $unsealed = rtrim($baseUrl, '?').'?'.http_build_query($secured, '', '&').'&seal=&'.http_build_query($unsecured, '', '&');

    return rtrim(referenceSealUrl($accessKey, $unsealed), '&');
}

describe('Signatur', function () {
    it('erzeugt dieselbe URL wie das offizielle Beispielpaket', function (array $secured, array $unsecured, string $accessKey) {
        config(['micropayment.access_key' => $accessKey]);

        $base = 'https://directbanking.micropayment.de/sofort/event/';

        expect(MicropaymentGateway::buildUrl($base, $secured, $unsecured))
            ->toBe(referencePaymentWindowUrl($accessKey, $base, $secured, $unsecured));
    })->with([
        'typische Bestellung' => [
            ['project' => 'fraukruner', 'title' => 'FK2026-4711', 'amount' => 4250, 'currency' => 'EUR', 'testmode' => 1],
            ['lang' => 'de'],
            'TEST-ACCESSKEY',
        ],
        'Umlaute und Sonderzeichen' => [
            ['project' => 'demo shop', 'title' => 'FK2026-1', 'paytext' => 'Ärger & Freude = 100% "Test" / mehr?', 'amount' => 1, 'currency' => 'EUR'],
            ['lang' => 'de'],
            'TEST-ACCESSKEY',
        ],
        'Sonderzeichen im AccessKey' => [
            ['project' => 'demo', 'title' => 'FK2026-9', 'amount' => 500, 'currency' => 'EUR'],
            ['lang' => 'de'],
            'k+e/y=&x',
        ],
        'ohne ungeschützte Parameter' => [
            ['project' => 'demo', 'title' => 'FK2026-9', 'amount' => 500, 'currency' => 'EUR'],
            [],
            'TEST-ACCESSKEY',
        ],
    ]);

    it('ändert sich, sobald ein geschützter Parameter abweicht', function () {
        $a = MicropaymentGateway::seal(['project' => 'fraukruner', 'amount' => 4250]);
        $b = MicropaymentGateway::seal(['project' => 'fraukruner', 'amount' => 4251]);

        expect($a)->not->toBe($b);
    });

    it('ändert sich mit dem AccessKey', function () {
        $parameters = ['project' => 'fraukruner', 'amount' => 4250];

        $a = MicropaymentGateway::seal($parameters);

        config(['micropayment.access_key' => 'ANDERER-KEY']);
        $b = MicropaymentGateway::seal($parameters);

        expect($a)->not->toBe($b);
    });
});

describe('Zahlungsfenster-URL', function () {
    it('setzt seal hinter die geschützten und vor die ungeschützten Parameter', function () {
        $url = MicropaymentGateway::paymentUrl(mcpOrder());

        $sealPosition = strpos($url, '&seal=');
        $amountPosition = strpos($url, 'amount=');
        $langPosition = strpos($url, 'lang=');

        expect($amountPosition)->toBeLessThan($sealPosition)
            ->and($sealPosition)->toBeLessThan($langPosition);
    });

    it('trägt alle Angaben, die die Buchung ausmachen', function () {
        $url = MicropaymentGateway::paymentUrl(mcpOrder());

        parse_str(parse_url($url, PHP_URL_QUERY), $parameters);

        expect($parameters)
            ->toHaveKey('project', 'fraukruner')
            ->toHaveKey('title', 'FK2026-4711')
            ->toHaveKey('amount', '4250')
            ->toHaveKey('currency', 'EUR')
            ->toHaveKey('testmode', '1')
            ->toHaveKey('lang', 'de')
            ->and($parameters['seal'])->toHaveLength(32);
    });

    it('meldet den Testmodus als 0, sobald er abgeschaltet ist', function () {
        config(['micropayment.testmode' => false]);

        expect(MicropaymentGateway::securedParameters(mcpOrder())['testmode'])->toBe(0);
    });

    it('benutzt die konfigurierte Basis-URL', function () {
        config(['micropayment.online_transfer_url' => 'https://beispiel.test/tink/event/']);

        expect(MicropaymentGateway::paymentUrl(mcpOrder()))->toStartWith('https://beispiel.test/tink/event/?');
    });
});

describe('Darstellung des Zahlungsfensters', function () {
    it('überträgt die konfigurierten Werte', function () {
        config([
            'micropayment.window.theme' => 'x1',
            'micropayment.window.gfx' => '39780-B4A9848AB9',
            'micropayment.window.bgcolor' => 'ffffff',
            'micropayment.window.producttype' => 'product',
        ]);

        expect(MicropaymentGateway::securedParameters(mcpOrder()))
            ->toHaveKey('theme', 'x1')
            ->toHaveKey('gfx', '39780-B4A9848AB9')
            ->toHaveKey('bgcolor', 'ffffff')
            ->toHaveKey('producttype', 'product');
    });

    // Leere Werte würden als `theme=` in der URL landen. Micropayment soll
    // stattdessen die Voreinstellung des Projekts verwenden.
    it('lässt leere Werte ganz weg', function () {
        config([
            'micropayment.window.theme' => '',
            'micropayment.window.gfx' => '',
            'micropayment.window.bgcolor' => '',
            'micropayment.window.producttype' => '',
        ]);

        $parameters = MicropaymentGateway::securedParameters(mcpOrder());

        expect($parameters)
            ->not->toHaveKey('theme')
            ->not->toHaveKey('gfx')
            ->not->toHaveKey('bgcolor')
            ->not->toHaveKey('producttype');

        expect(MicropaymentGateway::paymentUrl(mcpOrder()))->not->toContain('theme=');
    });

    // array_filter() würde hier zuschlagen: 0 gilt als leer. Der Testmodus muss
    // aber genau dann übertragen werden, wenn er abgeschaltet ist.
    it('behält testmode=0 und amount=0', function () {
        config(['micropayment.testmode' => false]);

        $parameters = MicropaymentGateway::securedParameters(mcpOrder(['total' => 0]));

        expect($parameters)
            ->toHaveKey('testmode', 0)
            ->toHaveKey('amount', 0);
    });

    it('nimmt die Darstellung in die Signatur auf', function () {
        config(['micropayment.window.theme' => '']);
        $ohne = MicropaymentGateway::seal(MicropaymentGateway::securedParameters(mcpOrder()));

        config(['micropayment.window.theme' => 'x1']);
        $mit = MicropaymentGateway::seal(MicropaymentGateway::securedParameters(mcpOrder()));

        expect($ohne)->not->toBe($mit);
    });
});

describe('Abgleich mit einer echten Bezahlfenster-URL', function () {
    // Vorlage aus dem Micropayment Control-Center. Sie ist der Beweis dafür,
    // dass unser Signaturverfahren stimmt: Aus denselben Parametern muss
    // dasselbe Siegel herauskommen.
    it('rechnet das Siegel einer funktionierenden URL nach', function () {
        $accessKey = 'nicht-der-echte-schluessel';

        config(['micropayment.access_key' => $accessKey]);

        $parameters = [
            'project' => '16r4-reruk-1d20afbe',
            'testmode' => 1,
            'theme' => 'x1',
            'gfx' => '39780-B4A9848AB9',
            'bgcolor' => 'ffffff',
            'amount' => 49,
            'title' => 'Kruner',
            'producttype' => 'product',
        ];

        $query = 'project=16r4-reruk-1d20afbe&testmode=1&theme=x1&gfx=39780-B4A9848AB9'
            .'&bgcolor=ffffff&amount=49&title=Kruner&producttype=product';

        expect(MicropaymentGateway::seal($parameters))->toBe(md5($query.$accessKey));
    });

    it('baut die URL in derselben Reihenfolge auf', function () {
        config(['micropayment.access_key' => 'egal']);

        $parameters = [
            'project' => '16r4-reruk-1d20afbe',
            'testmode' => 1,
            'theme' => 'x1',
            'gfx' => '39780-B4A9848AB9',
            'bgcolor' => 'ffffff',
            'amount' => 49,
            'title' => 'Kruner',
            'producttype' => 'product',
        ];

        $url = MicropaymentGateway::buildUrl('https://directbanking.micropayment.de/sofort/event/', $parameters);

        expect($url)->toStartWith(
            'https://directbanking.micropayment.de/sofort/event/'
            .'?project=16r4-reruk-1d20afbe&testmode=1&theme=x1&gfx=39780-B4A9848AB9'
            .'&bgcolor=ffffff&amount=49&title=Kruner&producttype=product&seal='
        );
    });
});

describe('Betrag', function () {
    it('rechnet in ganze Cent um', function ($total, $cents) {
        expect(mcpOrder(['total' => $total])->amountInCents())->toBe($cents);
    })->with([
        [42.50, 4250],
        [0.01, 1],
        [1000.00, 100000],
        // Kaufmännisch runden statt abschneiden: (int) (19.99 * 100) wäre 1998.
        [19.99, 1999],
        [0.07, 7],
        [8.29, 829],
    ]);
});

describe('Referenz', function () {
    it('benutzt dasselbe Format wie die Vorkasse', function () {
        expect(mcpOrder()->reference())->toBe('FK2026-4711');
    });

    it('weist unpassende Referenzen ab', function ($reference) {
        expect(MicropaymentSubject::fromReference($reference))->toBeNull();
    })->with([
        'leer' => [''],
        'null' => [null],
        'ohne Präfix' => ['4711'],
        'falsches Präfix' => ['XY2026-4711'],
        'ohne Nummer' => ['FK2026-'],
        'Buchstaben statt Nummer' => ['FK2026-abc'],
        'angehängter Text' => ['FK2026-4711x'],
        'SQL-Versuch' => ["FK2026-4711' OR '1'='1"],
    ]);
});

describe('Merkmal', function () {
    it('ist für dieselbe Bestellung stabil', function () {
        expect(mcpOrder()->token())->toBe(mcpOrder()->token());
    });

    it('unterscheidet sich je Bestellung', function () {
        expect(mcpOrder(['id' => 4711])->token())
            ->not->toBe(mcpOrder(['id' => 4712])->token());
    });

    it('erkennt das eigene Merkmal an und fremde nicht', function () {
        $order = mcpOrder();

        expect($order->tokenMatches($order->token()))->toBeTrue()
            ->and($order->tokenMatches(str_repeat('f', 32)))->toBeFalse()
            ->and($order->tokenMatches(mcpOrder(['id' => 4712])->token()))->toBeFalse();
    });

    // Ob Micropayment eigene Parameter an die Benachrichtigung durchreicht,
    // hängt an der Konfiguration der Zahlart im Control-Center. Fehlt das
    // Merkmal, darf die Zahlung nicht daran scheitern - abgesichert wird der
    // Endpunkt über Geheimfeld und Betragsprüfung.
    it('lässt eine Meldung ohne Merkmal durch', function () {
        expect(mcpOrder()->tokenMatches(null))->toBeTrue()
            ->and(mcpOrder()->tokenMatches(''))->toBeTrue();
    });
});

describe('Konfiguration', function () {
    it('gilt als eingerichtet, wenn AccessKey und Projekt gesetzt sind', function () {
        expect(MicropaymentGateway::isConfigured())->toBeTrue();
    });

    it('gilt als nicht eingerichtet, solange etwas fehlt', function (array $config) {
        config($config);

        expect(MicropaymentGateway::isConfigured())->toBeFalse();
    })->with([
        'ohne AccessKey' => [['micropayment.access_key' => '']],
        'ohne Projekt' => [['micropayment.project' => '']],
        'ohne beides' => [['micropayment.access_key' => '', 'micropayment.project' => '']],
    ]);

    it('meldet nichts Fehlendes, wenn alles gesetzt ist', function () {
        expect(MicropaymentGateway::missingConfiguration())->toBe([]);
    });

    // Der Vorschaumodus greift auch, wenn nur das Projektkürzel fehlt. Die
    // Meldung muss dann das Projektkürzel nennen und nicht den AccessKey.
    it('benennt genau den fehlenden Eintrag', function (array $config, array $expected) {
        config($config);

        expect(array_keys(MicropaymentGateway::missingConfiguration()))->toBe($expected);
    })->with([
        'nur AccessKey fehlt' => [
            ['micropayment.access_key' => ''],
            ['MICROPAYMENT_ACCESS_KEY'],
        ],
        'nur Projekt fehlt' => [
            ['micropayment.project' => ''],
            ['MICROPAYMENT_PROJECT'],
        ],
        'beides fehlt' => [
            ['micropayment.access_key' => '', 'micropayment.project' => ''],
            ['MICROPAYMENT_ACCESS_KEY', 'MICROPAYMENT_PROJECT'],
        ],
    ]);

    it('wertet einen Eintrag aus reinen Leerzeichen als fehlend', function () {
        config(['micropayment.project' => '   ']);

        expect(MicropaymentGateway::missingConfiguration())->toHaveKey('MICROPAYMENT_PROJECT');
    });
});
