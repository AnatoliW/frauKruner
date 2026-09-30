<?php

use App\Order;
use App\Payment\MicropaymentOrderSubject;
use Tests\Concerns\UsesUploadSchema;

/**
 * Die Bestellnummer, die die Kundin bezahlt hat.
 *
 * Eine Bestellung besteht aus der Hauptbestellung und je einer Unterbestellung
 * pro Warenkorbposition – alle in derselben Tabelle und damit aus derselben
 * Nummernfolge. Bezahlt wird die Hauptbestellung, Bestätigung und Rechnung
 * hingen früher an der Unterbestellung: Wer 12696 bezahlt hatte, bekam eine
 * Bestätigung über 12697 und 12698.
 *
 * Diese Tests halten fest, dass nach außen überall dieselbe Nummer steht.
 */
uses(UsesUploadSchema::class);

/**
 * Legt eine Bestellung mit der gewünschten Zahl an Positionen an.
 *
 * @return array{0: Order, 1: \Illuminate\Support\Collection<int, Order>}
 */
function makeOrderWithPositions(int $positions, string $createdAt = '2026-03-04 10:00:00'): array
{
    $parent = Order::create([
        'total' => 100,
        'payment_status' => 1,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);

    $children = collect(range(1, $positions))->map(fn () => Order::create([
        'parent_id' => $parent->id,
        'total' => 50,
        'payment_status' => 1,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]));

    return [$parent->fresh(), $children];
}

it('gibt jeder Unterbestellung die Nummer ihrer Hauptbestellung', function () {
    [$parent, $children] = makeOrderWithPositions(2);

    expect($parent->orderNumber())->toBe('FK2026-'.$parent->id);

    // Der eigentliche Fehler: Vorher stand hier die eigene ID der
    // Unterbestellung, also eine Nummer, die niemand bezahlt hat.
    foreach ($children as $child) {
        expect($child->orderNumber())
            ->toBe('FK2026-'.$parent->id)
            ->not->toBe('FK2026-'.$child->id);
    }
});

it('benennt bei mehreren Positionen, um welche es geht', function () {
    [$parent, $children] = makeOrderWithPositions(3);

    expect($children[0]->orderNumberWithPosition())->toBe('FK2026-'.$parent->id.' (Position 1 von 3)');
    expect($children[1]->orderNumberWithPosition())->toBe('FK2026-'.$parent->id.' (Position 2 von 3)');
    expect($children[2]->orderNumberWithPosition())->toBe('FK2026-'.$parent->id.' (Position 3 von 3)');

    expect($children[0]->positionInOrder())->toBe([1, 3]);
});

it('schweigt über die Position, wenn die Bestellung nur eine hat', function () {
    [$parent, $children] = makeOrderWithPositions(1);

    // „Position 1 von 1“ wäre kein Hinweis, sondern Rauschen.
    expect($children[0]->positionInOrder())->toBeNull();
    expect($children[0]->orderNumberWithPosition())->toBe('FK2026-'.$parent->id);
});

it('nennt für die Hauptbestellung keine Position', function () {
    [$parent] = makeOrderWithPositions(2);

    expect($parent->positionInOrder())->toBeNull();
    expect($parent->orderNumberWithPosition())->toBe($parent->orderNumber());
});

it('stimmt zeichengleich mit der Zahlungsreferenz überein', function () {
    [$parent, $children] = makeOrderWithPositions(2);

    $reference = (new MicropaymentOrderSubject($parent))->reference();

    // Weicht das ab, passen Bestätigung und Kontoauszug wieder nicht zusammen.
    expect($parent->orderNumber())->toBe($reference);
    expect($children[0]->orderNumber())->toBe($reference);
});

it('nimmt das Jahr der Hauptbestellung, nicht das der Unterbestellung', function () {
    // Haupt- und Unterbestellung entstehen in derselben Transaktion. An einem
    // Jahreswechsel könnten die Zeitstempel trotzdem auf zwei Jahre fallen –
    // maßgeblich ist das Jahr der Nummer, die bezahlt wurde.
    $parent = Order::create([
        'payment_status' => 1,
        'created_at' => '2025-12-31 23:59:59',
        'updated_at' => '2025-12-31 23:59:59',
    ]);

    $child = Order::create([
        'parent_id' => $parent->id,
        'payment_status' => 1,
        'created_at' => '2026-01-01 00:00:01',
        'updated_at' => '2026-01-01 00:00:01',
    ]);

    expect($child->orderNumber())->toBe('FK2025-'.$parent->id);
});

it('findet die Position auch bei Lücken in den IDs', function () {
    // Zwischen den Positionen einer Bestellung können andere Bestellungen
    // liegen; gezählt wird nur innerhalb der eigenen Bestellung.
    [$parentA, $childrenA] = makeOrderWithPositions(2);
    [, $childrenB] = makeOrderWithPositions(2);

    expect($childrenA[1]->positionInOrder())->toBe([2, 2]);
    expect($childrenB[0]->positionInOrder())->toBe([1, 2]);
    expect($childrenA[1]->orderNumber())->toBe('FK2026-'.$parentA->id);
});
