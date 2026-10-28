<?php

use App\Support\OrderNumberSearch;

/**
 * Was im Adminbereich in die Suche getippt oder eingefügt wird, ist selten die
 * nackte Zahl. Diese Fälle müssen alle dieselbe Bestellung finden.
 */
it('liest die nackte Nummer', function () {
    expect(OrderNumberSearch::number('5131'))->toBe('5131');
});

it('liest die Nummer aus einer Bestellnummer, nicht das Jahr', function () {
    // Der häufigste Fall: aus Mail oder Kontoauszug eingefügt.
    expect(OrderNumberSearch::number('FK2026-5131'))->toBe('5131');
    expect(OrderNumberSearch::number('FK2024-4941'))->toBe('4941');
});

it('liest aus einer Gutschrift-Nummer die Belegnummer, nicht die Herstellerin', function () {
    // FK2026-5132-12034: vorn das Jahr, in der Mitte der Beleg, hinten die
    // Herstellerin. Gesucht ist der Beleg 5132. Die Zahl am Ende zu nehmen hieß
    // früher: die unbeteiligte Bestellung 12034 zu treffen.
    expect(OrderNumberSearch::number('FK2026-5132-12034'))->toBe('5132');
    expect(OrderNumberSearch::number('fk2024-3552-5186'))->toBe('3552');
});

it('kommt mit Leerzeichen aus der Zwischenablage zurecht', function () {
    expect(OrderNumberSearch::number('  5131  '))->toBe('5131');
    expect(OrderNumberSearch::number("5131\n"))->toBe('5131');
});

it('schneidet führende Nullen ab', function () {
    expect(OrderNumberSearch::number('0005131'))->toBe('5131');
    expect(OrderNumberSearch::number('000'))->toBe('0');
});

it('gibt null zurück, wenn die Eingabe keine Nummer ist', function () {
    // Sonst würde eine Namenssuche auf eine leere Menge laufen.
    expect(OrderNumberSearch::number('Müller'))->toBeNull();
    expect(OrderNumberSearch::number('kundin@example.test'))->toBeNull();
    expect(OrderNumberSearch::number(''))->toBeNull();
    expect(OrderNumberSearch::number('   '))->toBeNull();
    expect(OrderNumberSearch::number(null))->toBeNull();
});

it('findet die Nummer auch in einem Satz, der auf sie endet', function () {
    expect(OrderNumberSearch::number('Bestellung FK2026-5131'))->toBe('5131');
});

it('liest die Verkäuferin nur aus einer vollständigen Gutschriftnummer', function ($search, $vendorId) {
    expect(OrderNumberSearch::vendorId($search))->toBe($vendorId);
})->with([
    ['FK2026-5146-5186', '5186'],
    ['FK2026-5149-5680-2', '5680'],
    ['  fk2024-3552-005186  ', '5186'],
    ['Gutschrift-Nr. FK2026-5146-5186', '5186'],
    ['FK2026-5146', null],
    ['5146', null],
    ['5146-5186', null],
    ['FK2026-5146-5186 Text', null],
    [null, null],
]);

it('liest die Bestellung aus einer Gutschriftnummer mit Pos.', function () {
    // Nicht die 2 am Ende: Die träfe die unbeteiligte Bestellung mit der ID 2.
    expect(OrderNumberSearch::number('FK2026-5149-5680-2'))->toBe('5149');
});

it('liest die Pos. nur aus einer Gutschriftnummer mit Pos.', function ($search, $pos) {
    expect(OrderNumberSearch::position($search))->toBe($pos);
})->with([
    ['FK2026-5149-5680-2', 2],
    [' Gutschrift-Nr. fk2026-5149-5680-12 ', 12],
    ['FK2026-5149-5680', null],
    ['FK2026-5149', null],
    ['5149-5680-2', null],
    [null, null],
]);
