<?php

use Tests\TestCase;

uses(TestCase::class);

test('mail_from_address returns a valid fallback when env is null', function () {
    config(['app.url' => 'http://127.0.0.1:8000']);

    putenv('MAIL_FROM_ADDRESS=null');
    $_ENV['MAIL_FROM_ADDRESS'] = 'null';
    $_SERVER['MAIL_FROM_ADDRESS'] = 'null';

    $address = mail_from_address();

    expect($address)->toBe('noreply@fraukruner.de')
        ->and(filter_var($address, FILTER_VALIDATE_EMAIL))->not->toBeFalse();
});

test('mail_from_address uses configured env value when present', function () {
    putenv('MAIL_FROM_ADDRESS=orders@fraukruner.de');
    $_ENV['MAIL_FROM_ADDRESS'] = 'orders@fraukruner.de';
    $_SERVER['MAIL_FROM_ADDRESS'] = 'orders@fraukruner.de';

    expect(mail_from_address())->toBe('orders@fraukruner.de');
});
