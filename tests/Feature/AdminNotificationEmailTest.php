<?php

use App\Filament\Resources\Notifications\Pages\CreateNotification;
use App\Filament\Resources\Notifications\Pages\EditNotification;
use App\Mail\UserNotifyEmail;
use App\Models\Role;
use App\Models\User;
use App\Notification;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\Concerns\UsesUploadSchema;

uses(UsesUploadSchema::class);

beforeEach(function () {
    Schema::create('notifications', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description');
        $table->string('image');
        $table->unsignedBigInteger('user_id')->nullable();
        $table->unsignedBigInteger('role')->nullable();
        $table->integer('verified')->default(0);
        $table->boolean('seen')->default(false);
        $table->boolean('is_email')->default(false);
        $table->timestamps();
    });
    Schema::create('permissions', function (Blueprint $table) {
        $table->id();
        $table->string('key');
    });
    Schema::create('permission_role', function (Blueprint $table) {
        $table->unsignedBigInteger('permission_id');
        $table->unsignedBigInteger('role_id');
    });

    foreach ([1 => 'admin', 2 => 'buyer', 3 => 'seller'] as $id => $name) {
        Role::create(['id' => $id, 'name' => $name, 'display_name' => $name]);
    }

    foreach ([
        ['admin@example.test', 1, 1],
        ['buyer@example.test', 2, 0],
        ['verified-seller@example.test', 3, 1],
        ['unverified-seller@example.test', 3, 0],
    ] as [$email, $role, $verified]) {
        User::withoutEvents(fn () => User::create([
            'name' => $email,
            'username' => strstr($email, '@', true),
            'email' => $email,
            'password' => 'test-password',
            'role_id' => $role,
            'verified' => $verified,
        ]));
    }

    config(['app.url' => 'http://127.0.0.1:8000']);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::where('role_id', 1)->first());
    Mail::fake();
});

afterEach(function () {
    Schema::dropIfExists('jobs');
    Schema::dropIfExists('notifications');
    Schema::dropIfExists('permission_role');
    Schema::dropIfExists('permissions');
});

function createAdminMessage(array $overrides = []): void
{
    Livewire::test(CreateNotification::class)
        ->fillForm(array_replace([
            'title' => 'Mailpit-Testnachricht',
            'description' => '<p>Lokaler <strong>Versandtest</strong> &amp; Grüße.</p>',
            'is_email' => true,
            'verified' => 0,
            'role' => null,
            'user_id' => null,
        ], $overrides))
        ->call('create')
        ->assertHasNoFormErrors();
}

it('sendet über das Adminformular an alle, eine Gruppe oder genau einen Nutzer', function (array $filters, array $expected) {
    createAdminMessage($filters);

    Mail::assertQueuedCount(count($expected));
    foreach ($expected as $email) {
        Mail::assertQueued(UserNotifyEmail::class, fn ($mail) => $mail->hasTo($email)
            && count($mail->to) === 1 && $mail->cc === [] && $mail->bcc === []
            && $mail->data->title === 'Mailpit-Testnachricht'
            && $mail->data->button_link === url('/login'));
    }
})->with([
    'alle' => [[], ['admin@example.test', 'buyer@example.test', 'verified-seller@example.test', 'unverified-seller@example.test']],
    'Verkäufer' => [['role' => 3], ['verified-seller@example.test', 'unverified-seller@example.test']],
    'Käufer' => [['role' => 2], ['buyer@example.test']],
    'Administratoren' => [['role' => 1], ['admin@example.test']],
    'verifizierte Verkäufer' => [['role' => 3, 'verified' => 1], ['verified-seller@example.test']],
    'unverifizierte Verkäufer' => [['role' => 3, 'verified' => 2], ['unverified-seller@example.test']],
    'verifiziert, alle Rollen' => [['verified' => 1], ['admin@example.test', 'verified-seller@example.test']],
    'ein Nutzer hat Vorrang' => [['user_id' => 2, 'role' => 3, 'verified' => 1], ['buyer@example.test']],
]);

it('sendet ohne aktivierten E-Mail-Schalter nichts', function () {
    createAdminMessage(['is_email' => false]);

    expect(Notification::count())->toBe(1);
    Mail::assertNothingOutgoing();
});

it('sendet beim Bearbeiten und nachträglichen Aktivieren keine E-Mail', function (bool $initialEmail) {
    createAdminMessage(['is_email' => $initialEmail]);
    Mail::fake();

    Livewire::test(EditNotification::class, ['record' => Notification::first()->getRouteKey()])
        ->fillForm(['title' => 'Korrigierter Titel', 'is_email' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Notification::first()->title)->toBe('Korrigierter Titel');
    Mail::assertNothingOutgoing();
})->with([true, false]);

it('meldet eine leere Empfängerauswahl, statt an andere Nutzer auszuweichen', function () {
    DB::table('users')->where('role_id', 3)->update(['email' => 'invalid']);

    Livewire::test(CreateNotification::class)
        ->fillForm(['title' => 'Leere Gruppe', 'description' => '<p>Test</p>', 'is_email' => true, 'role' => 3])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified('Nachricht gespeichert, keine E-Mail-Empfänger gefunden');

    Mail::assertNothingOutgoing();
});

it('meldet Versandfehler und erhält die gespeicherte Nachricht', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP-Testfehler'));

    Livewire::test(CreateNotification::class)
        ->fillForm(['title' => 'Fehlertest', 'description' => '<p>Test</p>', 'is_email' => true])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified('Nachricht gespeichert, E-Mail-Versand fehlgeschlagen');

    expect(Notification::count())->toBe(1);
});

it('stellt über die Datenbank-Queue erst beim Worker-Lauf zu', function () {
    Schema::create('jobs', function (Blueprint $table) {
        $table->id();
        $table->string('queue')->index();
        $table->longText('payload');
        $table->unsignedTinyInteger('attempts');
        $table->unsignedInteger('reserved_at')->nullable();
        $table->unsignedInteger('available_at');
        $table->unsignedInteger('created_at');
    });
    $this->app->forgetInstance('mail.manager');
    Mail::clearResolvedInstance('mail.manager');
    config(['queue.default' => 'database', 'mail.default' => 'array']);

    createAdminMessage(['user_id' => 2]);

    expect(DB::table('jobs')->count())->toBe(1)
        ->and(Mail::mailer('array')->getSymfonyTransport()->messages())->toHaveCount(0);

    Artisan::call('queue:work', ['connection' => 'database', '--once' => true, '--tries' => 1]);

    expect(DB::table('jobs')->count())->toBe(0);
    $messages = Mail::mailer('array')->getSymfonyTransport()->messages();
    expect($messages)->toHaveCount(1)
        ->and($messages->first()->getOriginalMessage()->getTo()[0]->getAddress())->toBe('buyer@example.test');
});

it('verweigert Käufern das Erstellen von Admin-Nachrichten', function () {
    $this->actingAs(User::where('role_id', 2)->first());

    expectForbidden(fn () => Livewire::test(CreateNotification::class));

    expect(Notification::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

it('stellt echte Testmails über SMTP an das lokale Mailpit zu', function (array $filters, int $count) {
    // Nur explizit starten: RUN_MAILPIT_TESTS=1 php artisan test --filter=Mailpit
    $this->app->forgetInstance('mail.manager');
    Mail::clearResolvedInstance('mail.manager');
    config([
        'queue.default' => 'sync',
        'mail.default' => 'smtp',
        'mail.from.address' => 'notifications@example.test',
        'mail.mailers.smtp' => [
            'transport' => 'smtp', 'scheme' => 'smtp',
            'host' => '127.0.0.1', 'port' => 1025,
            'username' => null, 'password' => null, 'timeout' => 5,
        ],
    ]);

    $before = Http::get('http://127.0.0.1:8025/api/v1/messages')->throw()->json('total');
    createAdminMessage($filters);
    $after = Http::get('http://127.0.0.1:8025/api/v1/messages')->throw()->json('total');

    expect($after - $before)->toBe($count);
})->with([
    'Mailpit alle' => [[], 4],
    'Mailpit Gruppe' => [['role' => 3], 2],
    'Mailpit Einzelversand' => [['user_id' => 2], 1],
    'Mailpit verifiziert' => [['role' => 3, 'verified' => 1], 1],
    'Mailpit unverifiziert' => [['role' => 3, 'verified' => 2], 1],
])->skip(fn () => getenv('RUN_MAILPIT_TESTS') !== '1', 'Mailpit-Test nur auf ausdrücklichen lokalen Aufruf.');
