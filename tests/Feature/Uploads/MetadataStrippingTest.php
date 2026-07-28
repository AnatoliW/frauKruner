<?php

/**
 * Entfernung von Metadaten aus hochgeladenen Bildern.
 *
 * App\Services\ExifMetadataService ruft `exiftool -all= -overwrite_original`
 * auf. Bilder von Verkäuferinnen enthalten regelmäßig GPS-Koordinaten und
 * Gerätekennungen – bleiben die in der Datei, ist der Wohnort öffentlich.
 *
 * Diese Tests laufen bewusst gegen das echte `exiftool` des Servers:
 * sie sind der Beleg, dass die Metadaten-Entfernung dort tatsächlich greift.
 */

use App\Product;
use App\Services\ExifMetadataService;
use Illuminate\Support\Facades\Storage;
use Tests\Support\UploadTestHelpers;

beforeEach(function () {
    uploads()->fakeDisks('s3');
});

it('meldet false, wenn der Pfad leer ist', function () {
    expect((new ExifMetadataService)->removeExifMetadata(null))->toBeFalse()
        ->and((new ExifMetadataService)->removeExifMetadata(''))->toBeFalse();
});

it('meldet false, wenn die Datei nicht existiert', function () {
    expect((new ExifMetadataService)->removeExifMetadata('gibt/es/nicht.jpg'))->toBeFalse();
});

it('erkennt, dass exiftool auf diesem System installiert ist', function () {
    expect(UploadTestHelpers::exiftoolAvailable())
        ->toBeTrue('exiftool fehlt – Metadaten werden NICHT entfernt, der Code meldet trotzdem Erfolg.');
})->group('umgebung');

it('entfernt Metadaten aus einer abgelegten Datei', function () {
    $path = 'thumbnail/mit-metadaten.jpg';
    Storage::put($path, UploadTestHelpers::jpegWithMetadata());

    expect(Storage::get($path))->toContain(UploadTestHelpers::METADATA_MARKER);

    $result = (new ExifMetadataService)->removeExifMetadata($path);

    expect($result)->toBeTrue()
        ->and(Storage::get($path))->not->toContain(UploadTestHelpers::METADATA_MARKER);
})->skip(
    fn () => ! UploadTestHelpers::exiftoolAvailable(),
    'exiftool ist auf diesem System nicht installiert.'
);

it('entfernt Metadaten aus einem hochgeladenen Produktbild', function () {
    $seller = uploads()->seller();
    $category = uploads()->category();

    $this->actingAs($seller)->post(
        route('seller.products.store'),
        uploads()->productPayload($category, [
            'thumbnail' => uploads()->imageWithMetadata('titelbild.jpg'),
            'images' => [
                ['image' => uploads()->imageWithMetadata('galerie.jpg'), 'nsfw' => 0],
            ],
        ])
    )->assertSessionHasNoErrors();

    $product = Product::firstOrFail();
    $disk = Storage::disk(config('filesystems.default'));

    expect($disk->get($product->image))
        ->not->toContain(UploadTestHelpers::METADATA_MARKER, 'Metadaten im Thumbnail nicht entfernt.');

    foreach ($product->images as $image) {
        expect($disk->get($image->getRawOriginal('image')))
            ->not->toContain(UploadTestHelpers::METADATA_MARKER, 'Metadaten im Galeriebild nicht entfernt.');
    }

    expect((bool) $product->meta_remove_status)->toBeTrue();
})->skip(
    fn () => ! UploadTestHelpers::exiftoolAvailable(),
    'exiftool ist auf diesem System nicht installiert.'
);

it('räumt die temporären Dateien wieder ab', function () {
    $path = 'thumbnail/temp-check.jpg';
    Storage::put($path, UploadTestHelpers::jpegWithMetadata());

    $tmpDir = storage_path('app/public/tmp');
    $before = is_dir($tmpDir) ? count(glob($tmpDir.'/*')) : 0;

    (new ExifMetadataService)->removeExifMetadata($path);

    $after = is_dir($tmpDir) ? count(glob($tmpDir.'/*')) : 0;

    expect($after)->toBe($before, 'ExifMetadataService lässt Dateien in storage/app/public/tmp liegen.');
});
