<?php

use App\Models\Boost;
use App\Models\Payment;
use Carbon\Carbon;

/**
 * Rechnungsnummern der Hervorhebungen ("Push").
 *
 * Alt (vor dem Stichtag, mit Zahlungs-ID): FKB<Zahlungs-ID>
 * Neu (ab dem Stichtag):                   FKP-<Jahr>-<Boost-ID>
 */

/** Boost ohne Datenbank: beide Zahlungs-Relationen vorbelegen, damit keine Query laeuft. */
function pushBoost(string $createdAt, ?string $trnx, int $id = 1787): Boost
{
    $boost = new Boost();
    $boost->id = $id;
    $boost->created_at = Carbon::parse($createdAt);

    $payment = null;

    if ($trnx !== null) {
        $payment = new Payment();
        $payment->payment_trnx_id = $trnx;
    }

    $boost->setRelation('payment', $payment);
    $boost->setRelation('payments', collect(array_filter([$payment])));

    return $boost;
}

beforeEach(function () {
    config(['app.invoice_format_cutoff_date' => '2026-08-20']);
});

test('Pushs vor dem Stichtag behalten die alte Nummer', function () {
    expect(pushBoost('2026-07-21 10:00:00', 'ABC123')->invoice_number)->toBe('FKBABC123')
        ->and(pushBoost('2026-08-19 23:59:59', 'ABC123')->invoice_number)->toBe('FKBABC123');
});

test('Pushs ab dem Stichtag bekommen FKP-Jahr-ID', function () {
    expect(pushBoost('2026-08-20 00:00:00', 'ABC123')->invoice_number)->toBe('FKP-2026-1787')
        ->and(pushBoost('2026-12-31 23:59:59', 'ABC123')->invoice_number)->toBe('FKP-2026-1787');
});

test('das Jahr kommt aus dem Anlagedatum des Pushs', function () {
    expect(pushBoost('2027-01-05 08:00:00', 'ABC123', 1900)->invoice_number)->toBe('FKP-2027-1900');
});

test('ohne Zahlungs-ID gilt immer die neue Nummer', function () {
    expect(pushBoost('2026-07-21 10:00:00', null)->invoice_number)->toBe('FKP-2026-1787')
        ->and(pushBoost('2026-08-25 10:00:00', null)->invoice_number)->toBe('FKP-2026-1787');
});

test('der Stichtag laesst sich per Konfiguration verschieben', function () {
    config(['app.invoice_format_cutoff_date' => '2026-10-01']);

    expect(pushBoost('2026-09-30 23:59:59', 'ABC123')->invoice_number)->toBe('FKBABC123')
        ->and(pushBoost('2026-10-01 00:00:00', 'ABC123')->invoice_number)->toBe('FKP-2026-1787');
});

test('Nutzerkonto und Adminbereich rechnen die Nummer nicht selbst aus', function () {
    $views = [
        'resources/views/auth/seller/pages/charge_invoice.blade.php',
        'resources/views/admin/auth/seller/pages/charge_invoice.blade.php',
        'resources/views/admin/boost_invoice.blade.php',
        'resources/views/filament/resources/boosts/pages/view-boost-invoice.blade.php',
    ];

    foreach ($views as $view) {
        $contents = file_get_contents(base_path($view));

        expect($contents)->toContain('invoice_number')
            ->and($contents)->not->toContain('FKB')
            ->and($contents)->not->toContain('FKP')
            ->and($contents)->not->toContain('PFK');
    }
});
