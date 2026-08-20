# Micropayment — Online bank transfer ("Online-Überweisung")

How the Micropayment payment method is wired into this shop, how to run it
locally, how to debug it when it misbehaves, and what to check before deploying.

Everything here has been verified against the running application. Where a claim
comes from Micropayment's own documentation or example package, the source is
named so you can check it yourself.

---

## 1. What this integration is — and what it is not

Micropayment offers two very different things. Knowing which one you are looking
at saves hours.

| | Service client (NVP) | **Payment window (used here)** |
|---|---|---|
| Package | `mcp-serviceclient_1_26` | `php_integration-web_example-advanced` |
| Flow | server-to-server API calls | customer is redirected to a hosted page |
| Payment methods | direct debit, credit card, prepay… | online bank transfer, and others |
| Result delivery | synchronous API response | asynchronous server-to-server notification |

**Online bank transfer has no server-to-server API.** There is no way to charge
a customer from our own code. The hosted payment window is the only route, and
the payment result only ever arrives through the notification endpoint.

This has one consequence that governs the whole design: **the browser coming
back from Micropayment proves nothing.** A customer can navigate to the success
URL by hand. Only the notification may change a payment status.

### About the payment method's name

The method is labelled *Online-Überweisung* and was Klarna/Sofort historically.
It was migrated to **Tink** (Open Banking, "Pay by Bank") in 2024. The technical
identifiers in URLs still say `sofort`. That is expected — do not "fix" it.

---

## 2. The flow

```
  Customer                Our app                    Micropayment            Bank
     │                       │                             │                  │
     │  clicks "Online-      │                             │                  │
     │  Überweisung"         │                             │                  │
     │──────────────────────>│                             │                  │
     │                       │ builds signed URL           │                  │
     │  303 redirect         │                             │                  │
     │<──────────────────────│                             │                  │
     │─────────────────────────────────────────────────────>│                 │
     │                       │                             │  bank dialogue   │
     │                       │                             │<────────────────>│
     │                       │                             │                  │
     │                       │   GET /payment/micropayment/notify             │
     │                       │<────────────────────────────│                  │
     │                       │   status=ok, url=…, forward=1                  │
     │                       │────────────────────────────>│                  │
     │   redirect to url=    │                             │                  │
     │<─────────────────────────────────────────────────────│                 │
     │                       │                             │                  │
```

**The redirect back to the shop is driven entirely by the notification
response.** The payment window URL has no return-URL parameter — verified in
Micropayment's own `tools.php::generatePaymentWindowUrl()`, which builds nothing
but secured parameters, the seal, and unsecured parameters.

If our notification endpoint is unreachable, or answers with anything other
than `status=ok`, Micropayment has nowhere to send the customer and shows:

> Die Weiterleitung wurde nicht erlaubt. Bitte wenden Sie sich an den Anbieter!

That message means *"your server did not confirm the notification"* — not that
something is wrong with the redirect itself. See [§7](#7-troubleshooting).

---

## 3. The code

### Files

| Path | Purpose |
|---|---|
| `config/micropayment.php` | all settings, heavily commented |
| `app/Payment/MicropaymentGateway.php` | builds and signs the payment window URL |
| `app/Payment/MicropaymentSubject.php` | abstract: what can be paid for |
| `app/Payment/MicropaymentOrderSubject.php` | a shop order |
| `app/Payment/MicropaymentBoostSubject.php` | a profile/product boost |
| `app/Http/Controllers/MicropaymentController.php` | redirect, notification, result page |
| `resources/views/micropayment/result.blade.php` | result page for the customer |
| `resources/views/micropayment/preview.blade.php` | shown when credentials are missing |
| `config/trustedproxy.php` | needed when running behind a tunnel, see [§5](#5-local-testing) |

### Routes

```
GET /payment/micropayment/notify            payment.micropayment.notify
GET /payment/micropayment/result/{state}    payment.micropayment.result
GET /payment/micropayment/order/{order}     payment.micropayment.order     (signed)
GET /payment/micropayment/boost/{payment}   payment.micropayment.boost     (signed)
```

The two redirect routes are **signed** (`URL::temporarySignedRoute`, 30 minutes).
The payment window URL contains the customer's name and e-mail, and record IDs
are sequential — without a signature anyone could enumerate them.

### The subject abstraction

Orders and boosts are paid the same way but live in different models. Rather than
duplicating the controller, both implement `MicropaymentSubject`:

```php
abstract public function reference(): string;      // "FK2026-1234" / "FKP-2026-1787"
abstract public function amountInCents(): int;
abstract public function paytext(): string;
abstract public function firstName(): string;
abstract public function lastName(): string;
abstract public function email(): string;
abstract public function isPaid(): bool;
abstract public function markRedirected(): void;
abstract public function markInitiated(string $transactionId): void;
abstract public function markPaid(string $transactionId): void;
abstract public function backUrl(): string;
abstract public function logContext(): array;
abstract public static function findByKey(int $key): ?static;
```

The reference prefix routes a notification back to the right model:

```php
private const KINDS = [
    'FK' => MicropaymentOrderSubject::class,   // shop order
    'FKP' => [MicropaymentBoostSubject::class, 'findByBoostKey'],  // boost, keyed by boost
    'BP'  => [MicropaymentBoostSubject::class, 'findByKey'],       // boost, legacy, keyed by payment
];
```

`MicropaymentSubject::fromReference('FKP-2026-1787')` parses the prefix and the ID
and loads the record. This is why **one** notification endpoint serves both flows.

### One number for the receipt and the bank statement

A boost's reference is `FKP-<year>-<boost id>` — byte for byte the invoice
number from `Boost::invoice_number`. It is what Micropayment receives as the
payment reference, so it is what the customer reads on their bank statement.
Two numbers for one transaction cannot be reconciled by anyone; that is why the
format lives in one place and both sides read it.

Orders already worked this way: `FK<year>-<order id>` is both the reference and
the invoice number.

**`BP<year>-<payment id>` is the earlier boost format and stays supported for
good.** A payment that entered the payment window before the switch reports back
with it, and a notification we cannot resolve is money received against a boost
that never activates. Two things carry that:

- `KINDS` keeps the `BP` prefix, resolving by *payment* id.
- `MicropaymentBoostSubject::tokenMatches()` also accepts the token derived from
  the old reference. This is the non-obvious half: the token is an HMAC over the
  reference, so changing the reference invalidates the token of every payment
  already in flight. Resolving the reference is not enough on its own.

Both are locked down by tests that fail when either is removed.

Free admin pushes have no payment and therefore no reference; their invoice
keeps `FKP-<year>-<boost id>` derived from the boost alone. Boosts up to number
314 carry several payments from the PayPal era, so `findByBoostKey()` picks the
newest deliberately rather than leaving it to the database. From 315 onward it
is one payment per boost, which is everything a notification can reach.

### Adding a third payable thing

1. Implement `MicropaymentSubject`.
2. Register a two-letter prefix in `KINDS`.
3. Add a signed redirect route and a controller method (three lines, mirroring
   `redirectBoost()`).

No change to the notification endpoint is required.

---

## 4. Security

Four independent layers. Each one exists for a reason; do not remove one because
another looks sufficient.

### Parameter sealing

Micropayment prescribes the algorithm (`tools.php::sealUrl` in the example
package):

```php
md5(urldecode(http_build_query($securedParameters)) . $accessKey)
```

The seal is appended as `&seal=…`. **Everything after `seal` is unsigned.** That
is why `lang` is the only unsecured parameter — it cannot influence a booking, so
it can change without re-signing.

Amount, currency, project, reference and test mode are all sealed. Without this,
a customer could edit the amount in the URL bar.

### Per-subject token

```php
substr(hash_hmac('sha256', 'micropayment:'.$reference, config('app.key')), 0, 32)
```

Sent as `orderToken` and echoed back in the notification. An order and a boost
payment with the same numeric ID produce different tokens. It also gates the
result page: without the right token the page shows a bare status and no amount,
so sequential IDs cannot be used to read other people's data.

### Secret field

An extra parameter configured in the control center next to the payment method,
then mirrored into `MICROPAYMENT_SECRET_FIELD_VALUE`. This is the only thing
that actually authenticates the notification endpoint — the token is optional
(see above) and the amount of your own order is something you know.

While the env value is empty the check is skipped, which is convenient during
setup. **With `APP_ENV=production` an empty value rejects every notification**
instead of booking it unchecked, so an incomplete deployment cannot hand out
goods for free. Locally the notification is accepted and a warning is written to
the log.

### Order access

`POST /payment/process` is reachable without a login and exempt from the CSRF
check — guest checkout needs that. The payment window URL, however, carries the
buyer's first name, surname and email, and order numbers run consecutively.
`CheckoutController::mayPayFor()` therefore only starts the online transfer when
the order belongs to the logged-in customer or was placed in the current session
(`checkout.order_id`, set in `store()`). Both cover every page that links to the
payment page.

The boost side is guarded at the source: `Seller\PaymentController::payment()`
aborts with 404 unless the payment belongs to the seller who is logged in. The
signed link is only ever handed out there.

### Amount check

Every `billing`/`rebill`/`pending` notification is compared against the expected
amount and currency. On mismatch nothing is booked and the event is logged with
both values.

---

## 5. Local testing

### 5.1 Without Micropayment at all

Most of what can break is on our side: booking, activation, idempotency, the
result page. All of it is reachable without involving Micropayment, because a
notification is nothing but a GET request.

Print a ready-to-run command for the newest pending payment:

```bash
php artisan tinker --execute='
$p = App\Models\Payment::where("status","PENDING")->latest("id")->first();
$s = new App\Payment\MicropaymentBoostSubject($p);
echo "curl -s \"".config("app.url")."/payment/micropayment/notify?".http_build_query([
  "function" => "billing", "title" => $s->reference(), "amount" => $s->amountInCents(),
  "currency" => "EUR", "auth" => "localtest-".$p->id, "testmode" => 1, "orderToken" => $s->token(),
])."\"\n";'
```

Running the printed command **really books the payment** and starts the boost.
Use a record you are willing to lose. This works against `localhost:8000` — no
tunnel needed.

Expected response:

```
status=ok
url=…/payment/micropayment/result/completed?ref=FKP-2026-1787&token=…
target=_self
forward=1
```

### 5.2 Without credentials — preview mode

With `MICROPAYMENT_ACCESS_KEY` or `MICROPAYMENT_PROJECT` empty, the redirect
route renders `micropayment/preview.blade.php` instead of sending the customer
to Micropayment's error page. It lists exactly which env entries are missing, all
parameters, the generated URL and the notification URL. Useful for reviewing the
URL construction before any account exists.

### 5.3 The full flow — tunnel required

Micropayment calls the notification endpoint from **their** server. It cannot
reach `localhost`. A public HTTPS address is mandatory.

```bash
# once
brew install cloudflared

# every session
PHP_CLI_SERVER_WORKERS=4 php artisan serve    # terminal 1
./tunnel.sh                                    # terminal 2
```

`tunnel.sh` opens the tunnel, writes `APP_URL` into `.env`, prints the
notification URL to paste into the control center, and restores `APP_URL` on
Ctrl-C.

Then, **both** of these must be true:

1. **Register the notification URL** in the control center, at the *payment
   method* (Micropayment's example calls it "Required during the payment method
   setup") — not in the project settings:

   ```
   https://<tunnel-host>/payment/micropayment/notify
   ```

2. **Browse the shop through the tunnel address**, not `localhost:8000`.
   Payment links are signed and only valid for the host that generated them, and
   returning on a different host means a different session.

A quick-tunnel address changes on every restart, so step 1 has to be repeated
each time. Leaving the tunnel running avoids that.

### 5.4 Why `TRUSTED_PROXIES` exists

The tunnel terminates HTTPS and forwards plain HTTP to `artisan serve`. Without
trusting the proxy, Laravel considers the request unencrypted and generates
`http://` URLs — while `hasValidSignature()` validates against the *request*
scheme. Signed links would be generated as `https` and validated as `http`, and
every payment link would be rejected as expired.

`config/trustedproxy.php` reads `TRUSTED_PROXIES` from the env. Laravel's own
`TrustProxies` middleware falls back to this config key, so no change to
`bootstrap/app.php` is needed.

**Empty is the safe default and the correct production value.** A trusted proxy
may dictate the request's host, scheme and client IP — `*` belongs only where the
app is reachable exclusively through that proxy.

### 5.5 Control center notification test

The control center has a notification test tool. Enable it temporarily:

```
MICROPAYMENT_ALLOW_APICHECK=true
```

The tool sends sample data — the project name as `title`, not a real reference.
A correct implementation could never resolve that, so the endpoint short-circuits
on `apicheck=1` and answers `status=ok` **without booking anything**. The branch
is unreachable while the flag is off.

This tool answers one question definitively: *can Micropayment reach us at all?*

The tool also sends `testmode=1`. `guardNotification()` therefore checks
`apicheck` **before** the test-mode lock and exempts the tool from it — an
`apicheck` call never books, so the lock it would otherwise hit has nothing to
protect. Without that exemption the tool would be unusable in exactly the place
it is needed: production, where `MICROPAYMENT_ALLOW_TESTMODE_NOTIFICATION` is
`false`, and testing mere reachability would mean opening the one switch under
which a test booking pays for a real order.

The exemption is not a hole. `apicheck=1` only skips the test-mode lock once the
tool itself is enabled, and the secret field is still checked afterwards — so a
successful run also proves that `MICROPAYMENT_SECRET_FIELD_VALUE` matches the
control center. `tests/Feature/MicropaymentNotificationTest.php` locks both
halves down: the tool answers with a locked test mode, and `apicheck=1` does not
get past the lock while the tool is disabled.

---

## 6. Configuration reference

All keys live in `config/micropayment.php` and are documented there. Summary:

| Env variable | Default | Notes |
|---|---|---|
| `MICROPAYMENT_ENABLED` | `true` | hides the payment option when false |
| `MICROPAYMENT_ACCESS_KEY` | — | from "Meine Konfiguration"; empty ⇒ preview mode |
| `MICROPAYMENT_PROJECT` | — | **technical** identifier, e.g. `16r4-reruk-1d20afbe` — not the display name |
| `MICROPAYMENT_ACCOUNT` | — | account number |
| `MICROPAYMENT_TESTMODE` | `false` | no money moves while true; the default is deliberately `false`, so a forgotten value fails a payment instead of shipping goods unpaid |
| `MICROPAYMENT_ONLINE_TRANSFER_URL` | `https://directbanking.micropayment.de/sofort/event/` | see warning below |
| `MICROPAYMENT_LANGUAGE` | `de` | unsecured parameter |
| `MICROPAYMENT_THEME` / `_GFX` / `_BGCOLOR` / `_PRODUCTTYPE` | — | appearance, from the control center; empty values are not sent |
| `MICROPAYMENT_SECRET_FIELD_NAME` | `secretfield` | rename it to improve security |
| `MICROPAYMENT_SECRET_FIELD_VALUE` | — | **empty rejects every notification** — outside production only when `ALLOW_UNAUTHENTICATED` is set explicitly |
| `MICROPAYMENT_ALLOW_UNAUTHENTICATED_NOTIFICATION` | `false` | setup only; has no effect at all with `APP_ENV=production` |
| `MICROPAYMENT_ALLOW_TESTMODE_NOTIFICATION` | `false` | must stay `false` in production; the control-center test tool is exempt, see [§5.5](#55-control-center-notification-test) |
| `MICROPAYMENT_ALLOW_APICHECK` | `false` | temporary, for the control center test tool |
| `MICROPAYMENT_CHECK_AMOUNT` | `true` | keep on |
| `MICROPAYMENT_NOTIFICATION_FORWARD` | `true` | send the customer to the result page |
| `MICROPAYMENT_NOTIFICATION_TARGET` | `_self` | |
| `TRUSTED_PROXIES` | empty | `*` only behind a tunnel, see [§5.4](#54-why-trusted_proxies-exists) |
| `APP_URL` | — | must be the public address; used for CLI-generated links |

### Do not "correct" the payment window URL

Micropayment documents the structure as
`https://<paymentdomain>.micropayment.de/<payment>/<service>/`. Here the domain
is `directbanking` while the path says `sofort`. This mismatch looks like a typo
and is not one — the value was taken verbatim from a working control-center URL
and verified against the live payment window. Changing it to
`sofort.micropayment.de/sofort/event/` breaks the integration.

---

## 7. Troubleshooting

### "Die Weiterleitung wurde nicht erlaubt. Bitte wenden Sie sich an den Anbieter!"

This is the one you will meet. It means Micropayment did not receive
`status=ok` from our notification endpoint. The payment itself usually succeeded.

Work through it in this order:

**Step 1 — did anything reach the tunnel?**

```bash
PORT=$(grep -oE 'metrics server on 127\.0\.0\.1:[0-9]+' storage/logs/cloudflared.log | tail -1 | grep -oE '[0-9]+$')
while :; do
  printf "%s  requests: %s\n" "$(date +%H:%M:%S)" \
    "$(curl -s http://127.0.0.1:$PORT/metrics | awk '/^cloudflared_tunnel_total_requests/ {print $2}')"
  sleep 2
done
```

cloudflared picks a random metrics port on every start, hence reading it from
the log. This counter includes requests that never reach Laravel (wrong path,
404), which makes it the most honest signal available. If it does not move while
you browse, you are not going through the tunnel.

**Step 2 — did a notification reach the app?**

```bash
tail -f storage/logs/laravel.log | grep -i micropayment
```

> **The log is in UTC.** `config('app.timezone')` is `UTC`, so timestamps run
> two hours behind German summer time. A payment at 16:30 on your clock appears
> as 14:30 in the log. This has caused more than one wrong diagnosis.

**Step 3 — if nothing arrives**, the notification URL at the payment method is
wrong, stale (quick-tunnel addresses change on restart) or not stored. Use the
control center's notification test to confirm reachability independently of any
payment.

**Step 4 — if it arrives but is rejected**, the log line names the reason:

| Log message | Cause |
|---|---|
| `Parameter \`function\` fehlt.` | not a real notification |
| `Benachrichtigungen aus dem Testmodus sind nicht erlaubt.` | `MICROPAYMENT_ALLOW_TESTMODE_NOTIFICATION=false` while testing |
| `Das Benachrichtigungs-Testwerkzeug ist nicht freigeschaltet.` | `MICROPAYMENT_ALLOW_APICHECK=false` |
| `Geheimfeld fehlt oder ist ungültig.` | `MICROPAYMENT_SECRET_FIELD_VALUE` differs from the control center |
| `Unbekannte Referenz \`…\`` | `title` is not a `FK…`/`FKP-…`/`BP…` reference, or the record is gone |
| `Ungültiges Merkmal für \`…\`` | `orderToken` mismatch — usually a changed `APP_KEY` |
| `Betrag weicht ab` | amount or currency differs; both values are logged |

### "Der Bezahllink ist abgelaufen"

The signed redirect route rejected the signature. Either the link is older than
30 minutes, or it was generated on a different host than the one it was opened
on — the classic case being a link generated on `localhost` and opened through
the tunnel, or a restarted tunnel with a new address.

### The preview page appears instead of a redirect

`MICROPAYMENT_ACCESS_KEY` or `MICROPAYMENT_PROJECT` is empty. The page names the
missing entries. Note that `MICROPAYMENT_PROJECT` wants the technical identifier,
not the project's display name — a display name is accepted by the config and
then rejected by Micropayment with a generic error page.

### Payment stays `PENDING`

Expected for `pending` and `init` events — with open banking these are normal
intermediate steps. Nothing is booked and no e-mail is sent until `billing`
arrives. If it never does, work through the steps above.

---

## 8. Notification events

Handled in `MicropaymentController::handleEvent()`:

| Event | Behaviour |
|---|---|
| `billing`, `rebill` | amount checked, then **booked and activated** |
| `pending` | amount checked, marked initiated, nothing booked |
| `init` | marked initiated |
| `error`, `info` | logged, result state `failed` |
| `pause`, `quit`, `expire`, `payin`, `change`, `storno`, `refund`, `backpay` | logged only |
| anything else | rejected with `status=error` |

Reversals (`storno`, `refund`, `backpay`) are deliberately **not** automated. An
automatic reversal would have to undo a boost or a shipped order; that decision
is made by hand. They are logged so nothing is lost.

### Idempotency

Micropayment redelivers a notification whenever we answer `status=error`, and
may deliver the same event more than once. Every write path guards on the
current state:

- `MicropaymentBoostSubject::markPaid()` claims the row with a conditional
  `UPDATE … WHERE status <> 'PAID'` and only activates the boost when that
  update actually changed a row
- `Order::markAsPaid()` claims the row the same way
  (`UPDATE … WHERE payment_status <> 1 OR payment_status IS NULL`) and only
  books stock and sends mail when it won the claim
- `Order::claimConfirmation()` and `redeemCoupon()` likewise set their column
  conditionally

Without that guard a redelivered `billing` would extend a boost a second time
or send a second order confirmation.

**Claim the row — never `if ($this->isPaid()) return;`.** A check followed by a
write is not atomic: two overlapping deliveries can both pass the check before
either writes. The window is real because handling a payment sends mail
synchronously, so one handler can still be running when the retry arrives.
`Order::markAsPaid()` is also shared with the admin's manual "mark as paid"
button, where a double click produces exactly the same race.

A regression test in `tests/Feature/CouponTest.php` reproduces it with a second
model instance holding the stale state — the same situation a parallel call
sees. It fails against a read-then-write implementation.

---

## 9. Tests

```bash
php artisan test --filter=Micropayment
```

140 tests, 486 assertions.

| File | Covers |
|---|---|
| `tests/Unit/MicropaymentGatewayTest.php` | URL construction and sealing |
| `tests/Feature/MicropaymentNotificationTest.php` | notification guards, booking, idempotency |
| `tests/Feature/MicropaymentBoostTest.php` | the boost flow and its money path |

The unit test contains an **independent reimplementation** of Micropayment's
official `sealUrl()`/`generatePaymentWindowUrl()` and compares both against four
datasets, including umlauts, `&`, `=`, `%`, special characters in the access key,
and an empty key. A second test recomputes the seal of a real control-center URL
and asserts it byte-for-byte. If someone "optimises" the sealing, these fail.

Tests use a hand-rolled SQLite schema (`tests/Support/MicropaymentTestSchema.php`)
following the existing `CouponTestSchema` pattern, and a stub layout in
`tests/Support/views/` because the real layout queries tables the lean schema
does not carry.

---

## 10. Deploying

### Before going live

- [ ] `APP_ENV=production` — `.env.example` ships `local`, and several guards key off this
- [ ] `MICROPAYMENT_TESTMODE=false`
- [ ] `MICROPAYMENT_ALLOW_TESTMODE_NOTIFICATION=false` — otherwise a test booking marks a real order paid
- [ ] `MICROPAYMENT_ALLOW_UNAUTHENTICATED_NOTIFICATION` removed or `false` — it accepts notifications without the secret field, i.e. anyone who knows an order number and its amount can mark it paid
- [ ] `MICROPAYMENT_ALLOW_APICHECK=false` — the secret field still gates it, but the tool has no business being reachable in normal operation
- [ ] `MICROPAYMENT_SECRET_FIELD_VALUE` set, matching the control center — without it every notification is rejected
- [ ] `MICROPAYMENT_CHECK_AMOUNT=true`
- [ ] `TRUSTED_PROXIES` empty, unless the server genuinely sits behind a proxy
- [ ] `APP_URL` set to the production address
- [ ] `MICROPAYMENT_ACCESS_KEY` and `MICROPAYMENT_PROJECT` set — otherwise customers get the preview page
- [ ] Notification URL in the control center pointing at production
- [ ] `php artisan config:cache` after changing the env

### One notification URL per payment method

The notification URL is stored **once**, at the payment method. It cannot point
at staging and production simultaneously. Testing against a tunnel while real
payments run means real notifications go to the tunnel.

If you need both at once, ask Micropayment for a **second project** and keep the
credentials in the staging env.

### `tunnel.sh` is a development tool

It writes to `.env`. It has no business on a server. It is intentionally not
referenced by any application code.

### PayPal is disabled for boosts

PayPal was commented out — not deleted — because the integration does not
currently work. Online bank transfer is the only way to pay for a boost. To
restore it, uncomment in all four places:

- `routes/seller.php` — `payment.process`, `payment.success`
- `app/Http/Controllers/Seller/PaymentController.php` — `paymentProcess()`, `success()`
- `resources/views/auth/seller/pages/payment.blade.php` — the button
- `resources/views/admin/auth/seller/pages/payment.blade.php` — dead copy, kept in sync

---

## 11. Known sharp edges

**`Shop::tax()` returns 0 for totals of 50 € and above.**

```php
if (setting('finance.vat') && $total < 50) { … } else { $vat = 0; }
```

`BoostController` uses it to fill `boosts.tax`, so a 123 € package is stored with
zero VAT while the corresponding `payments.tax` is correct at 23.37 €. The boost
payment page therefore derives its VAT breakdown from the **payment** record,
which is also the amount actually charged. Two tests lock this in. If you build
anything else on `boosts.tax`, be aware it is unreliable above 50 €.

**Amounts do not include shipping.** `MicropaymentOrderSubject::amountInCents()`
uses `order.total` with the discount already deducted and shipping not added,
matching what the existing prepayment page shows. If shipping ever becomes part
of the charge, change it here and the amount check will follow automatically.

**`APP_KEY` derives the order token.** Rotating it invalidates the tokens of all
in-flight payments; their notifications will be rejected with
`Ungültiges Merkmal`. Rotate only when no payment is in flight.

**`MICROPAYMENT_ENABLED=false` also rejects notifications.** Switching the
payment method off answers `status=error`, which makes Micropayment redeliver —
fine for a short outage, because the notification lands once the method is back
on. Turning it off permanently while payments are in flight loses them: the
money arrives, the order stays unpaid. Either wait out the in-flight payments or
accept notifications a while longer by leaving the flag on and removing the
payment method from the control center instead.

---

## 12. References

- Integration guide: <https://controlcenter.micropayment.de/help/integration_web/>
  (requires a control-center login)
- Example package used as the blueprint: `php_integration-web_example-advanced`
  — `tools.php` for URL sealing, `notification/index.php` for the endpoint
- Service client, **not** used here: `mcp-serviceclient_1_26`
