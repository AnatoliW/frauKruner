<?php

/**
 * Ausweis-Upload zur Verifizierung:
 * POST /verification/updated -> ProfileController::verification()
 *
 * Drei Bilder: Selfie mit Ausweis, Ausweis Vorderseite, Ausweis Rückseite.
 * Diese Dateien sind besonders sensibel – sie dürfen nie öffentlich
 * erreichbar sein und müssen zuverlässig ersetzt/gelöscht werden.
 */

use App\Models\Verification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    uploads()->fakeDisks('s3');
});

/**
 * @return array<string, mixed>
 */
function verificationPayload(array $overrides = []): array
{
    return array_merge([
        'street' => 'Teststraße',
        'house_no' => '12a',
        'city' => 'Berlin',
        'zip' => '10115',
        'date_of_birth' => '1990-05-17',
        'iban' => 'DE02120300000000202051',
        'bic' => 'BYLADEM1001',
    ], $overrides);
}

/**
 * @return array<string, mixed>
 */
function verificationDocuments(): array
{
    return [
        'person_id_shot_img' => uploads()->image('selfie.jpg'),
        'id_card_front_img' => uploads()->image('ausweis-vorne.jpg'),
        'id_card_back_img' => uploads()->image('ausweis-hinten.jpg'),
    ];
}

// ---------------------------------------------------------------------------
// Zugriffsschutz
// ---------------------------------------------------------------------------

it('lehnt Ausweis-Uploads ohne Login ab', function () {
    $this->post(route('verification.update'), verificationPayload(verificationDocuments()))
        ->assertRedirect(route('login'));

    expect(Verification::count())->toBe(0);
    Storage::disk('s3')->assertDirectoryEmpty('/');
});

// ---------------------------------------------------------------------------
// Erfolgsfall
// ---------------------------------------------------------------------------

it('speichert alle drei Ausweisbilder', function (string $disk) {
    uploads()->fakeDisks($disk);

    $user = uploads()->buyer();

    $response = $this->actingAs($user)
        ->post(route('verification.update'), verificationPayload(verificationDocuments()));

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success');

    $verification = Verification::where('user_id', $user->id)->firstOrFail();

    foreach (['person_id_shot_img', 'id_card_front_img', 'id_card_back_img'] as $field) {
        expect($verification->{$field})->not->toBeNull("Feld {$field} wurde nicht gespeichert.");
        Storage::disk($disk)->assertExists($verification->{$field});
    }

    // Die drei Dateien dürfen sich nicht gegenseitig überschreiben.
    expect(collect([
        $verification->person_id_shot_img,
        $verification->id_card_front_img,
        $verification->id_card_back_img,
    ])->unique())->toHaveCount(3);
})->with('disks');

it('setzt den Antrag beim erneuten Hochladen zurück in die Prüfliste', function () {
    $user = uploads()->buyer();
    uploads()->verificationFor($user, ['status' => 0]);

    $this->actingAs($user)->post(route('verification.update'), verificationPayload([
        'update' => '1',
        'id_card_front_img' => uploads()->image('ausweis-neu.jpg'),
    ]))->assertSessionHasNoErrors();

    expect(Verification::where('user_id', $user->id)->value('status'))->toBe(1);
});

it('ersetzt ein Ausweisbild und löscht das alte', function () {
    $user = uploads()->buyer();

    $this->actingAs($user)
        ->post(route('verification.update'), verificationPayload(verificationDocuments()))
        ->assertSessionHasNoErrors();

    $oldPath = Verification::where('user_id', $user->id)->value('id_card_front_img');

    $this->actingAs($user)->post(route('verification.update'), verificationPayload([
        'update' => '1',
        'id_card_front_img' => uploads()->image('ausweis-neu.jpg'),
    ]))->assertSessionHasNoErrors();

    $newPath = Verification::where('user_id', $user->id)->value('id_card_front_img');

    expect($newPath)->not->toBe($oldPath);

    $disk = Storage::disk(config('filesystems.default'));
    $disk->assertExists($newPath);
    $disk->assertMissing($oldPath);
});

it('lässt beim Aktualisieren nicht angefasste Ausweisbilder stehen', function () {
    $user = uploads()->buyer();

    $this->actingAs($user)
        ->post(route('verification.update'), verificationPayload(verificationDocuments()))
        ->assertSessionHasNoErrors();

    $before = Verification::where('user_id', $user->id)->first();

    $this->actingAs($user)->post(route('verification.update'), verificationPayload([
        'update' => '1',
        'id_card_front_img' => uploads()->image('ausweis-neu.jpg'),
    ]))->assertSessionHasNoErrors();

    $after = Verification::where('user_id', $user->id)->first();

    expect($after->person_id_shot_img)->toBe($before->person_id_shot_img)
        ->and($after->id_card_back_img)->toBe($before->id_card_back_img);

    Storage::disk(config('filesystems.default'))->assertExists($after->person_id_shot_img);
});

// ---------------------------------------------------------------------------
// Validierung
// ---------------------------------------------------------------------------

it('verlangt beim ersten Antrag alle drei Bilder', function () {
    $user = uploads()->buyer();

    $this->actingAs($user)
        ->post(route('verification.update'), verificationPayload())
        ->assertSessionHasErrors([
            'person_id_shot_img',
            'id_card_front_img',
            'id_card_back_img',
        ]);

    expect(Verification::count())->toBe(0);
});

it('lehnt Ausweis-Dateien ab, die kein Bild sind', function () {
    $user = uploads()->buyer();

    $this->actingAs($user)->post(route('verification.update'), verificationPayload([
        'person_id_shot_img' => uploads()->disguisedPhpFile('selfie.jpg'),
        'id_card_front_img' => uploads()->image('ausweis-vorne.jpg'),
        'id_card_back_img' => uploads()->image('ausweis-hinten.jpg'),
    ]))->assertSessionHasErrors('person_id_shot_img');

    expect(Verification::count())->toBe(0);
    Storage::disk('s3')->assertDirectoryEmpty('/');
});

// ---------------------------------------------------------------------------
// Vertraulichkeit
// ---------------------------------------------------------------------------

it('legt Ausweisbilder nicht auf einer öffentlich ausgelieferten Disk ab', function () {
    $user = uploads()->buyer();

    $this->actingAs($user)
        ->post(route('verification.update'), verificationPayload(verificationDocuments()))
        ->assertSessionHasNoErrors();

    $verification = Verification::where('user_id', $user->id)->firstOrFail();

    foreach (['person_id_shot_img', 'id_card_front_img', 'id_card_back_img'] as $field) {
        Storage::disk('public')->assertMissing($verification->{$field});
    }
});
