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

it('nimmt aus einer Gutschrift-Nummer die Verkäuferinnen-ID nicht als Bestellnummer', function () {
    // FK2026-5132-12034: hinten steht die Verkäuferin. Die Nummer am Ende ist
    // hier bewusst das Ergebnis – wer eine Gutschrift-Nr. sucht, bekommt keine
    // Bestellung. Wichtig ist, dass nichts Falsches getroffen wird.
    expect(OrderNumberSearch::number('FK2026-5132-12034'))->toBe('12034');
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
