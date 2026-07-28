<?php

/**
 * Video-Upload zu einer Bestellung (Verkäufer-Dashboard):
 * POST seller/dashboard/video/upload -> PagesController::VideoUpload()
 *
 * Laut Oberfläche: "max. 1 GB in .mp4".
 */

use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    uploads()->fakeDisks('s3');
});

// ---------------------------------------------------------------------------
// Zugriffsschutz
// ---------------------------------------------------------------------------

it('lehnt Video-Uploads ohne Login ab', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $this->post(route('seller.video.upload'), [
        'order_id' => $order->id,
        'video' => uploads()->video(),
    ])->assertRedirect(route('login'));

    expect($order->fresh()->video)->toBeNull();
});

it('lehnt Video-Uploads von Käufern ab', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    expectForbidden(fn () => $this->actingAs(uploads()->buyer())
        ->post(route('seller.video.upload'), [
            'order_id' => $order->id,
            'video' => uploads()->video(),
        ]));

    expect($order->fresh()->video)->toBeNull();
});

it('lässt einen fremden Verkäufer kein Video an eine fremde Bestellung hängen', function () {
    $owner = uploads()->seller();
    $order = uploads()->order($owner);
    $attacker = uploads()->seller();

    $this->actingAs($attacker)->post(route('seller.video.upload'), [
        'order_id' => $order->id,
        'video' => uploads()->video(),
    ]);

    expect($order->fresh()->video)->toBeNull();
    Storage::disk('s3')->assertDirectoryEmpty('/');
});

// ---------------------------------------------------------------------------
// Erfolgsfall
// ---------------------------------------------------------------------------

it('speichert das Video und merkt sich den Upload-Zeitpunkt', function (string $disk) {
    uploads()->fakeDisks($disk);

    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $response = $this->actingAs($seller)->post(route('seller.video.upload'), [
        'order_id' => $order->id,
        'video' => uploads()->video('bestellung.mp4', 4096),
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success');

    $order->refresh();

    expect($order->video)->toStartWith('Video/')
        ->and($order->video_uploaded_at)->not->toBeNull();

    Storage::disk($disk)->assertExists($order->video);
})->with('disks');

it('ersetzt ein bereits hochgeladenes Video und löscht das alte', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $this->actingAs($seller)->post(route('seller.video.upload'), [
        'order_id' => $order->id,
        'video' => uploads()->video('erstes.mp4'),
    ])->assertSessionHasNoErrors();

    $firstPath = $order->fresh()->video;

    $this->actingAs($seller)->post(route('seller.video.upload'), [
        'order_id' => $order->id,
        'video' => uploads()->video('zweites.mp4'),
    ])->assertSessionHasNoErrors();

    $secondPath = $order->fresh()->video;

    expect($secondPath)->not->toBe($firstPath);

    Storage::disk(config('filesystems.default'))->assertExists($secondPath);
    Storage::disk('s3')->assertMissing($firstPath);
});

it('verlangt eine order_id', function () {
    $seller = uploads()->seller();

    $this->actingAs($seller)
        ->post(route('seller.video.upload'), ['video' => uploads()->video()])
        ->assertSessionHasErrors('order_id');

    Storage::disk('s3')->assertDirectoryEmpty('/');
});

it('kommt ohne ausgewählte Datei ohne Fehler zurück', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $this->actingAs($seller)
        ->post(route('seller.video.upload'), ['order_id' => $order->id])
        ->assertSessionHasNoErrors();

    expect($order->fresh()->video)->toBeNull();
});

it('nimmt ein 1-GB-Video an', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $this->actingAs($seller)->post(route('seller.video.upload'), [
        'order_id' => $order->id,
        // 1 GB – die in der Oberfläche angegebene Obergrenze.
        'video' => uploads()->video('gross.mp4', 1024 * 1024),
    ])->assertSessionHasNoErrors();

    expect($order->fresh()->video)->not->toBeNull();
});

it('speichert ein Video mit echten Bytes vollständig', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);
    $file = uploads()->realVideo('echte-bytes.mp4', 3);
    $groesse = $file->getSize();

    $this->actingAs($seller)->post(route('seller.video.upload'), [
        'order_id' => $order->id,
        'video' => $file,
    ])->assertSessionHasNoErrors();

    $order->refresh();

    // Nicht nur "irgendwas gespeichert", sondern byte-genau die gleiche Datei.
    expect(Storage::disk(config('filesystems.default'))->size($order->video))
        ->toBe($groesse, 'Das gespeicherte Video ist unvollständig.');
});

// ---------------------------------------------------------------------------
// Was passiert, wenn PHP das Video wegen der Server-Limits verwirft
//
// Bei Überschreitung von upload_max_filesize (oder bei einem Abbruch während
// der Übertragung) kommt der Request sehr wohl an – nur der Datei-Eintrag ist
// leer und trägt einen Fehlercode. `$request->hasFile('video')` ist dann false.
// Genau der Fall, den Verkäuferinnen mit großen Videos ständig treffen.
// ---------------------------------------------------------------------------

it('lässt ein vorhandenes Video stehen, wenn der neue Upload scheitert', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $this->actingAs($seller)->post(route('seller.video.upload'), [
        'order_id' => $order->id,
        'video' => uploads()->video('erstes.mp4'),
    ])->assertSessionHasNoErrors();

    $vorhanden = $order->fresh()->video;

    $this->actingAs($seller)->post(route('seller.video.upload'), [
        'order_id' => $order->id,
        'video' => uploads()->rejectedVideo(UPLOAD_ERR_INI_SIZE),
    ]);

    expect($order->fresh()->video)->toBe($vorhanden);
    Storage::disk(config('filesystems.default'))->assertExists($vorhanden);
});

// ---------------------------------------------------------------------------
// Bekannte Lücken – siehe README in diesem Ordner
// ---------------------------------------------------------------------------

it('meldet keinen Erfolg, wenn PHP das Video verworfen hat', function (int $error) {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);
    $grund = uploads()->uploadErrorLabel($error);

    $this->actingAs($seller)->post(route('seller.video.upload'), [
        'order_id' => $order->id,
        'video' => uploads()->rejectedVideo($error),
    ]);

    expect($order->fresh()->video)->toBeNull();

    expect(session('success'))->toBeNull(
        "Upload gescheitert ({$grund}), die Verkäuferin bekommt trotzdem "
        .'"Video erfolgreich hochgeladen." zu sehen. VideoUpload() fällt am Ende '
        .'bedingungslos in die Erfolgsmeldung, wenn hasFile() false ist.'
    );
})->with([
    'zu groß für upload_max_filesize' => UPLOAD_ERR_INI_SIZE,
    'Übertragung abgebrochen' => UPLOAD_ERR_PARTIAL,
    'kein Temp-Verzeichnis' => UPLOAD_ERR_NO_TMP_DIR,
    'konnte nicht geschrieben werden' => UPLOAD_ERR_CANT_WRITE,
])->group('upload-bugs');

it('lehnt Dateien ab, die kein Video sind', function (string $name) {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $this->actingAs($seller)->post(route('seller.video.upload'), [
        'order_id' => $order->id,
        'video' => uploads()->video($name, 16),
    ]);

    expect($order->fresh()->video)
        ->toBeNull("Datei {$name} wurde als Video akzeptiert – es gibt keine mimes-Regel für 'video'.");
})->with(['schadcode.php', 'archiv.zip', 'tabelle.xlsx'])->group('upload-bugs');

it('lehnt Videos oberhalb der angegebenen 1-GB-Grenze mit einer Meldung ab', function () {
    $seller = uploads()->seller();
    $order = uploads()->order($seller);

    $response = $this->actingAs($seller)->post(route('seller.video.upload'), [
        'order_id' => $order->id,
        'video' => uploads()->video('zu-gross.mp4', 1024 * 1024 + 1024),
    ]);

    expect($order->fresh()->video)->toBeNull(
        'Es gibt keine max-Regel für "video"; die 1-GB-Grenze steht nur im Blade-Text. '
        .'Eine Regel wie "video" => ["required","file","mimetypes:video/mp4","max:1048576"] '
        .'würde die Verkäuferin sofort informieren, statt sie 1 GB umsonst hochladen zu lassen.'
    );

    $response->assertSessionHasErrors('video');
})->group('upload-bugs');
