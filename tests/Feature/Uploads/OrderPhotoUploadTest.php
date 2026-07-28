<?php

/**
 * Foto-Upload zu einer Bestellung (Verkäufer-Dashboard):
 * POST seller/dashboard/photo/upload        -> PagesController::photoUpload()
 * POST seller/dashboard/photo/delete/{img}  -> PagesController::photoDelete()
 */

use App\Models\Orderimage;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    uploads()->fakeDisks('s3');
});

// ---------------------------------------------------------------------------
// Zugriffsschutz
// ---------------------------------------------------------------------------

it('lehnt Foto-Uploads ohne Login ab', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $this->post(route('seller.photo.upload'), [
        'order_id' => $order->id,
        'upload_photo' => [uploads()->image('foto.jpg')],
    ])->assertRedirect(route('login'));

    expect(Orderimage::count())->toBe(0);
});

it('lehnt Foto-Uploads von Käufern ab', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    expectForbidden(fn () => $this->actingAs(uploads()->buyer())
        ->post(route('seller.photo.upload'), [
            'order_id' => $order->id,
            'upload_photo' => [uploads()->image('foto.jpg')],
        ]));

    expect(Orderimage::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Erfolgsfall
// ---------------------------------------------------------------------------

it('speichert mehrere Fotos zu einer Bestellung', function (string $disk) {
    uploads()->fakeDisks($disk);

    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $response = $this->actingAs($seller)->post(route('seller.photo.upload'), [
        'order_id' => $order->id,
        'upload_photo' => [
            uploads()->image('foto-1.jpg'),
            uploads()->image('foto-2.png'),
            uploads()->image('foto-3.jpg'),
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success');

    $images = Orderimage::where('order_id', $order->id)->get();
    expect($images)->toHaveCount(3);

    foreach ($images as $image) {
        expect($image->image)->toStartWith('upload_photo/');
        Storage::disk($disk)->assertExists($image->image);
    }
})->with('disks');

it('verlangt eine order_id', function () {
    $seller = uploads()->seller();

    $this->actingAs($seller)->post(route('seller.photo.upload'), [
        'upload_photo' => [uploads()->image('foto.jpg')],
    ])->assertSessionHasErrors('order_id');

    expect(Orderimage::count())->toBe(0);
});

it('kommt ohne ausgewählte Datei ohne Fehler zurück', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $this->actingAs($seller)
        ->post(route('seller.photo.upload'), ['order_id' => $order->id])
        ->assertSessionHasNoErrors();

    expect(Orderimage::count())->toBe(0);
});

it('löscht ein Foto samt Datei wieder', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $this->actingAs($seller)->post(route('seller.photo.upload'), [
        'order_id' => $order->id,
        'upload_photo' => [uploads()->image('foto.jpg')],
    ])->assertSessionHasNoErrors();

    $image = Orderimage::firstOrFail();
    $path = $image->image;

    $this->actingAs($seller)
        ->post(route('seller.photo.delete', $image))
        ->assertSessionHas('success');

    expect(Orderimage::find($image->id))->toBeNull();
    Storage::disk(config('filesystems.default'))->assertMissing($path);
});

// ---------------------------------------------------------------------------
// Bekannte Lücken – siehe README in diesem Ordner
// ---------------------------------------------------------------------------

it('verhindert, dass ein Verkäufer Fotos an fremde Bestellungen hängt', function () {
    $owner = uploads()->seller();
    $order = uploads()->order($owner);

    $attacker = uploads()->seller();

    $this->actingAs($attacker)->post(route('seller.photo.upload'), [
        'order_id' => $order->id,
        'upload_photo' => [uploads()->image('fremd.jpg')],
    ]);

    expect(Orderimage::where('order_id', $order->id)->count())
        ->toBe(0, 'Fremder Verkäufer konnte ein Foto an eine fremde Bestellung hängen.');
})->group('upload-bugs');

it('verhindert, dass ein Verkäufer fremde Fotos löscht', function () {
    $owner = uploads()->seller();
    $order = uploads()->order($owner);

    $this->actingAs($owner)->post(route('seller.photo.upload'), [
        'order_id' => $order->id,
        'upload_photo' => [uploads()->image('foto.jpg')],
    ])->assertSessionHasNoErrors();

    $image = Orderimage::firstOrFail();
    $attacker = uploads()->seller();

    $this->actingAs($attacker)->post(route('seller.photo.delete', $image));

    expect(Orderimage::find($image->id))
        ->not->toBeNull('Fremder Verkäufer konnte ein fremdes Foto löschen.');
})->group('upload-bugs');

it('lehnt nicht-Bilddateien beim Foto-Upload ab', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $this->actingAs($seller)->post(route('seller.photo.upload'), [
        'order_id' => $order->id,
        'upload_photo' => [uploads()->disguisedPhpFile('foto.jpg')],
    ]);

    expect(Orderimage::count())
        ->toBe(0, 'Serverseitig fehlt jede Prüfung des Dateityps für upload_photo[].');
})->group('upload-bugs');
