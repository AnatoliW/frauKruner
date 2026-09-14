<?php

use App\Models\Boost;
use App\Models\User;
use App\Package;
use App\Product;
use Carbon\Carbon;
use Tests\Concerns\UsesBoostSchema;

uses(UsesBoostSchema::class);

function expiringProfile(string $email = 'expire-target@example.test'): User
{
    return User::forceCreate([
        'name' => 'Testine',
        'last_name' => 'Pushmann',
        'email' => $email,
        'password' => 'x',
        'role_id' => 3,
        'boosted' => 0,
    ]);
}

function expiringProduct(): Product
{
    return Product::forceCreate([
        'title' => 'Getragene Socken',
        'user_id' => 1,
        'boosted' => 0,
    ]);
}

function expiringPackage(int $days = 7, string $type = 'Profile'): Package
{
    return Package::create([
        'name' => $days.' Tage',
        'price' => 10.00,
        'days' => $days,
        'type' => $type,
    ]);
}

/**
 * Legt einen bereits freigeschalteten Push an und datiert ihn zurueck, als waere
 * er vor $daysAgo Tagen ausgelaufen.
 */
function expiredBoostFor($boostable, int $days = 7, int $daysAgo = 1): Boost
{
    $type = $boostable instanceof Product ? 'Product' : 'Profile';
    $boost = Boost::freeAdminPush($boostable, expiringPackage($days, $type), adminId: 1);

    $end = Carbon::now()->subDays($daysAgo);
    $boost->forceFill(['start_day' => $end->copy()->subDays($days), 'end_day' => $end])->save();
    $boostable->forceFill(['boost_end_date' => $end])->save();

    return $boost->refresh();
}

test('ein abgelaufener Profil-Push wird beendet', function () {
    $user = expiringProfile();
    $boost = expiredBoostFor($user);

    $this->artisan('boosts:expire')->assertSuccessful();

    expect((int) $boost->refresh()->status)->toBe(0)
        ->and((int) $user->refresh()->boosted)->toBe(0);
});

test('ein abgelaufener Produkt-Push wird beendet', function () {
    $product = expiringProduct();
    $boost = expiredBoostFor($product);

    $this->artisan('boosts:expire')->assertSuccessful();

    expect((int) $boost->refresh()->status)->toBe(0)
        ->and((int) $product->refresh()->boosted)->toBe(0);
});

test('ein laufender Push bleibt unangetastet', function () {
    $user = expiringProfile();
    $boost = Boost::freeAdminPush($user, expiringPackage(7), adminId: 1);

    $this->artisan('boosts:expire')->assertSuccessful();

    expect((int) $boost->refresh()->status)->toBe(1)
        ->and((int) $user->refresh()->boosted)->toBe(1);
});

test('das Enddatum bleibt als Verlauf stehen', function () {
    $user = expiringProfile();
    expiredBoostFor($user);

    $this->artisan('boosts:expire')->assertSuccessful();

    expect($user->refresh()->boost_end_date)->not->toBeNull();
});

test('ein zweiter, laengerer Push ueberlebt das Ablaufen des kuerzeren', function () {
    // Der Fall aus der Praxis: Waehrend ein langer Push laeuft, wird ein kurzer
    // dazugekauft. process() schiebt boost_end_date am Profil auf das fruehe
    // Ende des kurzen Pushs zurueck - der lange Push laeuft aber weiter.
    $user = expiringProfile();
    $langer = Boost::freeAdminPush($user, expiringPackage(365), adminId: 1);
    $kurzer = expiredBoostFor($user, days: 1, daysAgo: 2);

    $this->artisan('boosts:expire')->assertSuccessful();

    expect((int) $kurzer->refresh()->status)->toBe(0)
        ->and((int) $langer->refresh()->status)->toBe(1)
        ->and((int) $user->refresh()->boosted)->toBe(1);
});

test('ein von Hand gesetztes Kennzeichen ohne Push-Datensatz wird zurueckgesetzt', function () {
    $product = expiringProduct();
    $product->forceFill([
        'boosted' => 1,
        'boost_start_date' => Carbon::now()->subDays(10),
        'boost_end_date' => Carbon::now()->subDay(),
    ])->save();

    $this->artisan('boosts:expire')->assertSuccessful();

    expect((int) $product->refresh()->boosted)->toBe(0);
});

test('auch geloeschte Produkte verlieren ihren abgelaufenen Push', function () {
    $product = expiringProduct();
    $boost = expiredBoostFor($product);
    $product->delete();

    $this->artisan('boosts:expire')->assertSuccessful();

    expect((int) $boost->refresh()->status)->toBe(0)
        ->and((int) Product::withTrashed()->find($product->id)->boosted)->toBe(0);
});

test('der Probelauf aendert nichts', function () {
    $user = expiringProfile();
    $boost = expiredBoostFor($user);

    $this->artisan('boosts:expire', ['--dry-run' => true])->assertSuccessful();

    expect((int) $boost->refresh()->status)->toBe(1)
        ->and((int) $user->refresh()->boosted)->toBe(1);
});
