<?php

use App\Models\Boost;
use App\Models\Payment;
use App\Models\User;
use App\Package;
use Tests\Concerns\UsesBoostSchema;

uses(UsesBoostSchema::class);

function pushPackage(int $days = 12, float $price = 10.00): Package
{
    return Package::create([
        'name' => $days . ' Tage Profil',
        'price' => $price,
        'days' => $days,
        'type' => 'Profile',
    ]);
}

function pushTarget(): User
{
    return User::forceCreate([
        'name' => 'Testine',
        'last_name' => 'Pushmann',
        'email' => 'push-target@example.test',
        'password' => 'x',
        'role_id' => 3,
        'boosted' => 0,
    ]);
}

test('ein Admin-Push legt keine Zahlung an', function () {
    $boost = Boost::freeAdminPush(pushTarget(), pushPackage(), adminId: 1);

    expect(Payment::count())->toBe(0)
        ->and($boost->payment)->toBeNull()
        ->and($boost->payments)->toHaveCount(0);
});

test('ein Admin-Push kostet nichts', function () {
    $boost = Boost::freeAdminPush(pushTarget(), pushPackage(days: 12, price: 10.00), adminId: 1);

    expect((float) $boost->price)->toBe(0.0)
        ->and((float) $boost->base_price)->toBe(0.0)
        ->and((float) $boost->tax)->toBe(0.0);
});

test('ein Admin-Push ist sofort aktiv und laeuft ueber die Paketlaufzeit', function () {
    $user = pushTarget();
    $boost = Boost::freeAdminPush($user, pushPackage(days: 12), adminId: 1);

    $user->refresh();

    expect((int) $boost->status)->toBe(1)
        ->and((int) $user->boosted)->toBe(1)
        ->and($user->boost_start_date)->not->toBeNull()
        ->and((int) \Carbon\Carbon::parse($user->boost_start_date)->diffInDays(\Carbon\Carbon::parse($user->boost_end_date)))->toBe(12);
});

test('der Admin bleibt als ausloesender Nutzer am Push vermerkt', function () {
    $boost = Boost::freeAdminPush(pushTarget(), pushPackage(), adminId: 42);

    expect((int) $boost->user_id)->toBe(42);
});

test('ohne Zahlung meldet der Push-Status KOSTENLOS statt zu scheitern', function () {
    $boost = Boost::freeAdminPush(pushTarget(), pushPackage(), adminId: 1);

    expect($boost->payment_status)->toBe('KOSTENLOS');
});

test('kostenlose Pushs bleiben im Adminbereich sichtbar, in der Bezahlt-Liste nicht', function () {
    $free = Boost::freeAdminPush(pushTarget(), pushPackage(), adminId: 1);

    $paid = Boost::freeAdminPush(pushTarget(), pushPackage(), adminId: 1);
    $paid->payment()->create([
        'payment_trnx_id' => 'TRX-1',
        'status' => 'PAID',
        'amount' => 11.90,
        'tax' => 1.90,
    ]);

    expect(Boost::paidOrFree()->pluck('id')->all())->toContain($free->id, $paid->id)
        ->and(Boost::paid()->pluck('id')->all())->toBe([$paid->id]);
});
