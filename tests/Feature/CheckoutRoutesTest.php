<?php

use App\Mail\UserPrepaymentOrder;
use App\Order;
use Illuminate\Support\Facades\Mail;

test('get store-checkout redirects to checkout page', function () {
    $response = $this->get('/store-checkout');

    $response->assertRedirect(route('checkout'));
});

test('user prepayment order mailable defines a from address', function () {
    config([
        'app.url' => 'http://127.0.0.1:8000',
        'mail.from.address' => mail_from_address(),
        'mail.from.name' => mail_from_name(),
    ]);

    $order = new Order([
        'id' => 4973,
        'total' => 73.19,
        'email' => 'buyer@example.com',
    ]);
    $order->exists = true;
    $order->created_at = now();

    $mailable = new UserPrepaymentOrder($order);
    $mailable->build();

    expect($mailable->from)->toHaveCount(1)
        ->and($mailable->from[0]['address'])->not->toBeEmpty()
        ->and(filter_var($mailable->from[0]['address'], FILTER_VALIDATE_EMAIL))->not->toBeFalse();
});

test('user prepayment order can be queued for sending', function () {
    Mail::fake();

    config([
        'app.url' => 'http://127.0.0.1:8000',
        'mail.from.address' => mail_from_address(),
        'mail.from.name' => mail_from_name(),
    ]);

    $order = new Order([
        'id' => 4973,
        'total' => 73.19,
        'email' => 'buyer@example.com',
    ]);
    $order->exists = true;
    $order->created_at = now();

    Mail::to('buyer@example.com')->send(new UserPrepaymentOrder($order));

    Mail::assertSent(UserPrepaymentOrder::class);
});
