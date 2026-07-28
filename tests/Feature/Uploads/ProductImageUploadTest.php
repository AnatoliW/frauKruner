<?php

/**
 * Produkt-Upload (Verkäufer-Dashboard):
 * POST seller/dashboard/products/create   -> ProductsController::store()
 * POST seller/dashboard/products/{p}/update -> ProductsController::update()
 *
 * Deckt Thumbnail + Galerie-Bilder ab.
 */

use App\Models\Image;
use App\Product;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    uploads()->fakeDisks('s3');
});

// ---------------------------------------------------------------------------
// Zugriffsschutz
// ---------------------------------------------------------------------------

it('lehnt Produkt-Uploads ohne Login ab', function () {
    $response = $this->post(route('seller.products.store'), [
        'thumbnail' => uploads()->image('foto.jpg'),
    ]);

    $response->assertRedirect(route('login'));
    Storage::disk('s3')->assertDirectoryEmpty('/');
});

it('lehnt Produkt-Uploads von Käufern ab', function () {
    expectForbidden(fn () => $this->actingAs(uploads()->buyer())
        ->post(route('seller.products.store'), [
            'thumbnail' => uploads()->image('foto.jpg'),
        ]));

    Storage::disk('s3')->assertDirectoryEmpty('/');
});

// ---------------------------------------------------------------------------
// Erfolgsfall
// ---------------------------------------------------------------------------

it('speichert Thumbnail und Galerie-Bilder auf der konfigurierten Disk', function (string $disk) {
    uploads()->fakeDisks($disk);

    $seller = uploads()->seller();
    $category = uploads()->category();

    $response = $this->actingAs($seller)->post(
        route('seller.products.store'),
        uploads()->productPayload($category, [
            'thumbnail' => uploads()->image('titelbild.jpg'),
            'images' => [
                ['image' => uploads()->image('galerie-1.jpg'), 'nsfw' => 0],
                ['image' => uploads()->image('galerie-2.jpg'), 'nsfw' => 1],
            ],
        ])
    );

    $response->assertRedirect(route('seller.products'));
    $response->assertSessionHasNoErrors();

    $product = Product::where('user_id', $seller->id)->firstOrFail();

    expect($product->image)->toBe('thumbnail/titelbild.jpg');
    Storage::disk($disk)->assertExists('thumbnail/titelbild.jpg');

    $gallery = Image::where('product_id', $product->id)->get();
    expect($gallery)->toHaveCount(2);

    foreach ($gallery as $image) {
        Storage::disk($disk)->assertExists($image->getRawOriginal('image'));
        expect($image->getRawOriginal('image'))->toStartWith('images/');
    }

    // NSFW-Kennzeichnung muss pro Bild erhalten bleiben.
    expect($gallery->pluck('nsfw')->sort()->values()->all())->toBe([0, 1]);
})->with('disks');

it('vergibt einen eindeutigen Slug pro Produkt', function () {
    $seller = uploads()->seller();
    $category = uploads()->category();

    foreach (['a.jpg', 'b.jpg'] as $file) {
        $this->actingAs($seller)->post(
            route('seller.products.store'),
            uploads()->productPayload($category, ['thumbnail' => uploads()->image($file)])
        )->assertSessionHasNoErrors();
    }

    $slugs = Product::where('user_id', $seller->id)->pluck('slug');

    expect($slugs)->toHaveCount(2)
        ->and($slugs->unique())->toHaveCount(2);
});

it('überschreibt kein fremdes Thumbnail mit gleichem Dateinamen', function () {
    $category = uploads()->category();

    $first = uploads()->seller();
    $this->actingAs($first)->post(
        route('seller.products.store'),
        uploads()->productPayload($category, ['thumbnail' => uploads()->image('IMG_1234.jpg', 300, 300)])
    )->assertSessionHasNoErrors();

    $second = uploads()->seller();
    $this->actingAs($second)->post(
        route('seller.products.store'),
        uploads()->productPayload($category, ['thumbnail' => uploads()->image('IMG_1234.jpg', 600, 600)])
    )->assertSessionHasNoErrors();

    $paths = Product::pluck('image');

    expect($paths->unique())->toHaveCount(2, 'Beide Produkte zeigen auf dieselbe Datei – Bild wurde überschrieben.');

    foreach ($paths as $path) {
        Storage::disk(config('filesystems.default'))->assertExists($path);
    }
});

// ---------------------------------------------------------------------------
// Validierung
// ---------------------------------------------------------------------------

it('verlangt ein Thumbnail', function () {
    $seller = uploads()->seller();
    $category = uploads()->category();

    $this->actingAs($seller)
        ->post(route('seller.products.store'), uploads()->productPayload($category))
        ->assertSessionHasErrors('thumbnail');

    expect(Product::count())->toBe(0);
});

it('lehnt nicht erlaubte Bildformate als Thumbnail ab', function (string $file) {
    $seller = uploads()->seller();
    $category = uploads()->category();

    $this->actingAs($seller)->post(
        route('seller.products.store'),
        uploads()->productPayload($category, ['thumbnail' => uploads()->image($file)])
    )->assertSessionHasErrors('thumbnail');

    expect(Product::count())->toBe(0);
    Storage::disk('s3')->assertDirectoryEmpty('/');
})->with(['bild.gif', 'bild.bmp']);

it('lehnt eine als Bild getarnte PHP-Datei ab', function () {
    $seller = uploads()->seller();
    $category = uploads()->category();

    $this->actingAs($seller)->post(
        route('seller.products.store'),
        uploads()->productPayload($category, ['thumbnail' => uploads()->disguisedPhpFile('shell.jpg')])
    )->assertSessionHasErrors('thumbnail');

    expect(Product::count())->toBe(0);
    Storage::disk('s3')->assertDirectoryEmpty('/');
});

it('akzeptiert webp als Thumbnail und legt die Datei auch ab', function () {
    $seller = uploads()->seller();
    $category = uploads()->category();

    $this->actingAs($seller)->post(
        route('seller.products.store'),
        uploads()->productPayload($category, ['thumbnail' => uploads()->image('titelbild.webp')])
    )->assertSessionHasNoErrors();

    $product = Product::firstOrFail();

    expect($product->image)->toBe('thumbnail/titelbild.webp');
    Storage::disk(config('filesystems.default'))->assertExists('thumbnail/titelbild.webp');
})->group('upload-bugs');

// ---------------------------------------------------------------------------
// Aktualisieren
// ---------------------------------------------------------------------------

it('ersetzt das Thumbnail beim Bearbeiten und löscht die alte Datei', function () {
    $seller = uploads()->seller();
    $category = uploads()->category();

    $this->actingAs($seller)->post(
        route('seller.products.store'),
        uploads()->productPayload($category, ['thumbnail' => uploads()->image('alt.jpg')])
    )->assertSessionHasNoErrors();

    $product = Product::firstOrFail();
    $oldPath = $product->image;

    $this->actingAs($seller)->post(
        route('seller.products.update', $product),
        uploads()->productPayload($category, ['thumbnail' => uploads()->image('neu.jpg')])
    )->assertRedirect(route('seller.products'));

    $product->refresh();

    expect($product->image)->toBe('thumbnail/neu.jpg');

    $disk = Storage::disk(config('filesystems.default'));
    $disk->assertExists('thumbnail/neu.jpg');
    $disk->assertMissing($oldPath);
});

it('entfernt Galerie-Bilder samt Datei, wenn sie beim Bearbeiten abgewählt werden', function () {
    $seller = uploads()->seller();
    $category = uploads()->category();

    $this->actingAs($seller)->post(
        route('seller.products.store'),
        uploads()->productPayload($category, [
            'thumbnail' => uploads()->image('titel.jpg'),
            'images' => [
                ['image' => uploads()->image('bleibt.jpg'), 'nsfw' => 0],
                ['image' => uploads()->image('fliegt-raus.jpg'), 'nsfw' => 0],
            ],
        ])
    )->assertSessionHasNoErrors();

    $product = Product::firstOrFail();
    $keep = $product->images()->first();
    $remove = $product->images()->latest('id')->first();
    $removedPath = $remove->getRawOriginal('image');

    $this->actingAs($seller)->post(
        route('seller.products.update', $product),
        uploads()->productPayload($category, [
            'images' => [
                ['id' => $keep->id, 'nsfw' => 0],
            ],
        ])
    )->assertRedirect(route('seller.products'));

    expect(Image::find($remove->id))->toBeNull()
        ->and(Image::find($keep->id))->not->toBeNull();

    Storage::disk(config('filesystems.default'))->assertMissing($removedPath);
});
