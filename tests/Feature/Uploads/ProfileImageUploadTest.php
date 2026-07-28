<?php

/**
 * Profilbild-Upload:
 * POST /info/updated -> ProfileController::info()
 *
 * Regel im Controller: 'profile_img' => 'nullable|image|max:10000' (10.000 KB).
 */

use App\Models\Profile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\UploadTestHelpers;

beforeEach(function () {
    uploads()->fakeDisks('s3');
});

/**
 * Verkäufer müssen zusätzlich eine gültige Steuernummer mitschicken
 * (Rule::requiredIf role_id == 3).
 */
function profilePayload(array $overrides = [], bool $asSeller = false): array
{
    $payload = array_merge([
        'username' => 'testverkaeuferin',
        'description' => 'Kurzbeschreibung',
    ], $overrides);

    if ($asSeller) {
        $payload['meta'] = ['vat' => '12345678901'];
    }

    return $payload;
}

// ---------------------------------------------------------------------------
// Zugriffsschutz
// ---------------------------------------------------------------------------

it('lehnt Profilbild-Uploads ohne Login ab', function () {
    $this->post(route('info.update'), profilePayload([
        'profile_img' => uploads()->image('profil.jpg'),
    ]))->assertRedirect(route('login'));

    expect(Profile::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Erfolgsfall
// ---------------------------------------------------------------------------

it('speichert das Profilbild für Käuferinnen und Verkäuferinnen', function (string $disk, int $role) {
    uploads()->fakeDisks($disk);

    $user = uploads()->user(['role_id' => $role, 'verified' => 1]);

    $response = $this->actingAs($user)->post(
        route('info.update'),
        profilePayload(['profile_img' => uploads()->image('profil.jpg')], $role === UploadTestHelpers::ROLE_SELLER)
    );

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success');

    $profile = Profile::where('user_id', $user->id)->firstOrFail();

    expect($profile->profile_img)->toStartWith('profile/');
    Storage::disk($disk)->assertExists($profile->profile_img);
})->with('disks')->with([
    'Käuferin' => UploadTestHelpers::ROLE_BUYER,
    'Verkäuferin' => UploadTestHelpers::ROLE_SELLER,
]);

it('ersetzt ein vorhandenes Profilbild und löscht das alte', function () {
    $user = uploads()->buyer();

    $this->actingAs($user)->post(route('info.update'), profilePayload([
        'profile_img' => uploads()->image('alt.jpg'),
    ]))->assertSessionHasNoErrors();

    $oldPath = Profile::where('user_id', $user->id)->value('profile_img');

    $this->actingAs($user)->post(route('info.update'), profilePayload([
        'profile_img' => uploads()->image('neu.jpg'),
    ]))->assertSessionHasNoErrors();

    $newPath = Profile::where('user_id', $user->id)->value('profile_img');

    expect($newPath)->not->toBe($oldPath);

    $disk = Storage::disk(config('filesystems.default'));
    $disk->assertExists($newPath);
    $disk->assertMissing($oldPath);
});

it('lässt das Profil ohne Bild unverändert', function () {
    $user = uploads()->buyer();

    $this->actingAs($user)->post(route('info.update'), profilePayload([
        'profile_img' => uploads()->image('profil.jpg'),
    ]))->assertSessionHasNoErrors();

    $path = Profile::where('user_id', $user->id)->value('profile_img');

    $this->actingAs($user)
        ->post(route('info.update'), profilePayload(['username' => 'neuer-name']))
        ->assertSessionHasNoErrors();

    $profile = Profile::where('user_id', $user->id)->firstOrFail();

    expect($profile->profile_img)->toBe($path)
        ->and($profile->username)->toBe('neuer-name');

    Storage::disk(config('filesystems.default'))->assertExists($path);
});

// ---------------------------------------------------------------------------
// Validierung
// ---------------------------------------------------------------------------

it('lehnt Dateien ab, die kein Bild sind', function () {
    $user = uploads()->buyer();

    $this->actingAs($user)->post(route('info.update'), profilePayload([
        'profile_img' => uploads()->disguisedPhpFile('profil.jpg'),
    ]))->assertSessionHasErrors('profile_img');

    expect(Profile::count())->toBe(0);
    Storage::disk('s3')->assertDirectoryEmpty('/');
});

it('lehnt Profilbilder über 10 MB ab', function () {
    $user = uploads()->buyer();

    $tooLarge = UploadedFile::fake()
        ->create('riesig.jpg', 10_001, 'image/jpeg');

    $this->actingAs($user)->post(route('info.update'), profilePayload([
        'profile_img' => $tooLarge,
    ]))->assertSessionHasErrors('profile_img');

    Storage::disk('s3')->assertDirectoryEmpty('/');
});

it('verlangt von Verkäuferinnen eine gültige Steuernummer', function () {
    $seller = uploads()->seller();

    $this->actingAs($seller)->post(route('info.update'), [
        'username' => 'testverkaeuferin',
        'profile_img' => uploads()->image('profil.jpg'),
        'meta' => ['vat' => 'keine-nummer'],
    ])->assertSessionHasErrors('meta.vat');

    Storage::disk('s3')->assertDirectoryEmpty('/');
});
