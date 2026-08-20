<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Payment;
use App\Order;
use App\Payment\MicropaymentBoostSubject;
use App\Payment\MicropaymentGateway;
use App\Payment\MicropaymentOrderSubject;
use App\Payment\MicropaymentSubject;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

/**
 * Online-Ueberweisung ueber das Micropayment-Zahlungsfenster.
 *
 * Vier Einstiegspunkte:
 *
 *  - redirectOrder()  Bestellung im Shop bezahlen.
 *  - redirectBoost()  Hervorhebung bezahlen ("Profil pushen").
 *  - notify()         Micropayment meldet den Zahlungsstatus serverseitig zurueck.
 *  - result()         Ergebnisseite, auf die der Kunde am Ende geleitet wird.
 *
 * Beide Weiterleitungen muenden in denselben Ablauf; was sich unterscheidet,
 * steckt im MicropaymentSubject. Nur notify() darf den Zahlungsstatus
 * veraendern - die Rueckkehr im Browser kommt vom Kunden und ist damit
 * manipulierbar.
 */
class MicropaymentController extends Controller
{
    /**
     * Leitet fuer eine Bestellung auf das Zahlungsfenster weiter.
     *
     * Aufgerufen aus CheckoutController::processPayment().
     */
    public function redirectOrder(Request $request, Order $order)
    {
        return $this->redirectTo($request, new MicropaymentOrderSubject($order));
    }

    /**
     * Leitet fuer eine Hervorhebung auf das Zahlungsfenster weiter.
     *
     * Aufgerufen von der Bezahlseite im Verkaeuferbereich.
     */
    public function redirectBoost(Request $request, Payment $payment)
    {
        return $this->redirectTo($request, new MicropaymentBoostSubject($payment));
    }

    /**
     * Nimmt die Benachrichtigung von Micropayment entgegen.
     *
     * Kommt vom Micropayment-Server, nicht vom Browser der Kundin: keine
     * Session, kein CSRF-Token, keine Anmeldung. Die Antwort ist reiner Text
     * und teilt Micropayment mit, ob wir die Meldung verarbeiten konnten und
     * wohin die Kundin anschliessend geleitet werden soll.
     *
     * Aufbau nach notification/index.php des offiziellen Beispielpakets.
     */
    public function notify(Request $request): Response
    {
        $state = 'fallback';
        $subject = null;

        try {
            $this->guardNotification($request);

            // Der „Benachrichtigungs-Test“ aus dem Control-Center schickt
            // Beispieldaten statt einer echten Meldung - als Referenz etwa den
            // Projektnamen. Geprueft wird allein, ob der Endpunkt erreichbar
            // ist und mit `status=ok` antwortet. Behandelten wir die
            // Beispieldaten wie eine echte Meldung, meldete das Werkzeug
            // dauerhaft einen Fehler, obwohl die Einrichtung stimmt.
            //
            // Es wird bewusst nichts gebucht und nichts freigeschaltet. Der
            // Zweig ist nur erreichbar, solange das Werkzeug ueberhaupt
            // freigeschaltet ist - siehe guardNotification().
            if ($request->boolean('apicheck')) {
                logger()->info('Micropayment: Benachrichtigungs-Test beantwortet', [
                    'parameters' => $this->loggableQuery($request),
                ]);

                return $this->notificationResponse('ok', 'fallback', null);
            }

            $event = $this->queryString($request, 'function');
            $reference = $this->queryString($request, 'title');
            $subject = MicropaymentSubject::fromReference($reference);

            if (! $subject) {
                throw new \RuntimeException(sprintf('Unbekannte Referenz `%s`', $reference), 404);
            }

            if (! $subject->tokenMatches($this->queryString($request, 'orderToken'))) {
                throw new \RuntimeException(sprintf('Ungültiges Merkmal für `%s`', $subject->reference()), 403);
            }

            $state = $this->handleEvent($event, $subject, $request);
        } catch (\Throwable $e) {
            logger()->warning('Micropayment-Benachrichtigung abgelehnt', [
                'message' => $e->getMessage(),
                'parameters' => $this->loggableQuery($request),
            ]);

            // `status=error` signalisiert Micropayment, dass die Meldung nicht
            // verarbeitet wurde. Sie wird dann erneut zugestellt.
            return $this->notificationResponse('error', 'fallback', null, $e->getMessage());
        }

        return $this->notificationResponse('ok', $state, $subject);
    }

    /**
     * Ergebnisseite nach Abschluss des Zahlungsvorgangs.
     *
     * Reine Anzeige. Der Zustand steht in der URL und wird deshalb nur benutzt,
     * um den passenden Text auszuwaehlen.
     */
    public function result(Request $request, string $state)
    {
        // Angaben zum Vorgang nur mit passendem Merkmal. Sonst liessen sich ueber
        // eine fortlaufende Nummer Referenz und Betrag fremder Vorgaenge auslesen.
        // Ohne Merkmal bleibt die Seite eine reine Statusmeldung.
        $subject = MicropaymentSubject::fromReference($this->queryString($request, 'ref'));

        if ($subject && ! hash_equals($subject->token(), $this->queryString($request, 'token'))) {
            $subject = null;
        }

        $known = ['completed', 'pending', 'failed', 'mismatch', 'fallback'];

        return view('micropayment.result', [
            'state' => in_array($state, $known, true) ? $state : 'fallback',
            'subject' => $subject,
        ]);
    }

    /**
     * Gemeinsamer Ablauf beider Weiterleitungen.
     *
     * Der Aufruf muss signiert sein: Die Zahlungsfenster-URL enthaelt Name und
     * E-Mail-Adresse, und die Nummern sind fortlaufend. Ohne Signatur liessen
     * sich fremde Daten ueber eine geratene Nummer abrufen. Die Signatur stellt
     * aus, wer die Weiterleitung anstoesst.
     */
    private function redirectTo(Request $request, MicropaymentSubject $subject)
    {
        if (! $request->hasValidSignature()) {
            return redirect()->to($subject->backUrl())
                ->withErrors('Der Bezahllink ist abgelaufen. Bitte wähle die Zahlungsart erneut.');
        }

        if (! MicropaymentGateway::isEnabled()) {
            return redirect()->to($subject->backUrl())
                ->withErrors('Die Online-Überweisung steht zurzeit nicht zur Verfügung.');
        }

        if ($subject->isPaid()) {
            return redirect()->to($this->resultUrl('completed', $subject));
        }

        $subject->markRedirected();

        $paymentUrl = MicropaymentGateway::paymentUrl($subject);

        // Fehlen Zugangsdaten, wuerde Micropayment die Anfrage mit einer eigenen
        // Fehlerseite abweisen. Statt die Kundin dorthin zu schicken, zeigen wir
        // die erzeugte URL - so laesst sich der Ablauf schon vor dem Eintragen
        // der Zugangsdaten pruefen.
        if (! MicropaymentGateway::isConfigured()) {
            return response()->view('micropayment.preview', [
                'subject' => $subject,
                'paymentUrl' => $paymentUrl,
                'securedParameters' => MicropaymentGateway::securedParameters($subject),
                'unsecuredParameters' => MicropaymentGateway::unsecuredParameters(),
                'notificationUrl' => route('payment.micropayment.notify'),
                'missing' => MicropaymentGateway::missingConfiguration(),
            ]);
        }

        return redirect()->away($paymentUrl, 303);
    }

    /**
     * Ein Parameter aus der Abfragezeichenkette, immer als Zeichenkette.
     *
     * `?ref[]=x` liefert sonst ein Array, und das laesst sich weder mit
     * hash_equals() vergleichen noch an eine string-Signatur uebergeben - die
     * Ergebnisseite antwortete mit einem Fehler 500 statt mit einer Statusmeldung.
     */
    private function queryString(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : '';
    }

    /**
     * Die Abfragezeichenkette, so wie sie ins Protokoll darf.
     *
     * Das Geheimfeld ist die einzige Absicherung des Endpunkts und steht als
     * gewoehnlicher Parameter in der URL. Unveraendert protokolliert landete es
     * in der Tabelle `logs` - die im Adminbereich einsehbar ist - und in
     * storage/logs/laravel.log. Wer eines von beiden lesen kann, koennte damit
     * fremde Bestellungen als bezahlt melden. Fuer `orderToken` gilt dasselbe.
     *
     * @return array<string, mixed>
     */
    private function loggableQuery(Request $request): array
    {
        return Arr::except($request->query(), [
            (string) config('micropayment.notification.secret_field_name'),
            'orderToken',
        ]);
    }

    /**
     * Sicherheitsprüfungen, bevor irgendetwas gebucht wird.
     */
    private function guardNotification(Request $request): void
    {
        if (! MicropaymentGateway::isEnabled()) {
            throw new \RuntimeException('Die Zahlart ist abgeschaltet.', 503);
        }

        if (! filled($request->query('function'))) {
            throw new \RuntimeException('Parameter `function` fehlt.', 400);
        }

        if ($request->boolean('testmode')) {
            if (! config('micropayment.notification.allow_testmode')) {
                throw new \RuntimeException('Benachrichtigungen aus dem Testmodus sind nicht erlaubt.', 503);
            }

            // Freischaltung bleibt bestehen - fuer eine Probebuchung nach dem
            // Livegang. Sie ist aber der Zustand, in dem eine Testbuchung echte
            // Ware ausloest, und soll deshalb nicht unbemerkt laufen.
            if (app()->isProduction()) {
                logger()->warning('Micropayment: Testbuchung wird produktiv als Zahlung angenommen - MICROPAYMENT_ALLOW_TESTMODE_NOTIFICATION=false setzen.');
            }
        }

        if ($request->boolean('apicheck') && ! config('micropayment.notification.allow_apicheck')) {
            throw new \RuntimeException('Das Benachrichtigungs-Testwerkzeug ist nicht freigeschaltet.', 403);
        }

        // Das Geheimfeld ist die eigentliche Absicherung des Endpunkts. Es wird
        // im Control-Center bei der Zahlart hinterlegt und muss hier
        // uebereinstimmen.
        $expected = (string) config('micropayment.notification.secret_field_value');

        if (blank($expected)) {
            // Ohne Geheimfeld steht der Endpunkt offen: Wer eine Bestellnummer
            // und den Betrag kennt - bei der eigenen Bestellung also jeder -,
            // koennte sie als bezahlt melden und damit Ware ausloesen.
            //
            // Waehrend der Einrichtung laesst sich das freischalten, damit der
            // Ablauf ohne Control-Center durchspielbar bleibt. Es muss aber
            // ausdruecklich geschehen: Frueher genuegte APP_ENV != production,
            // und da .env.example seit jeher APP_ENV=local mitbringt, stand der
            // Endpunkt auf jedem Server offen, der die Vorlage uebernommen hat -
            // erreichbar aus dem Netz, tunnel.sh veroeffentlicht ihn sogar.
            // Jetzt ist das Fehlen des Geheimfelds allein kein Freibrief mehr,
            // und produktiv gibt es die Freischaltung ueberhaupt nicht.
            if (app()->isProduction() || ! config('micropayment.notification.allow_unauthenticated')) {
                throw new \RuntimeException('Ohne konfiguriertes Geheimfeld werden keine Benachrichtigungen angenommen (MICROPAYMENT_SECRET_FIELD_VALUE).', 503);
            }

            logger()->warning('Micropayment: Benachrichtigung ohne Geheimfeld angenommen, weil MICROPAYMENT_ALLOW_UNAUTHENTICATED_NOTIFICATION=true gesetzt ist. Vor dem Livegang MICROPAYMENT_SECRET_FIELD_VALUE setzen und die Freischaltung entfernen.');

            return;
        }

        $provided = $request->query((string) config('micropayment.notification.secret_field_name'), '');

        if (! is_string($provided) || ! hash_equals($expected, $provided)) {
            throw new \RuntimeException('Geheimfeld fehlt oder ist ungültig.', 403);
        }
    }

    /**
     * Ordnet das gemeldete Ereignis einer Aktion zu.
     *
     * @return string Zustand fuer die Ergebnisseite
     */
    private function handleEvent(string $event, MicropaymentSubject $subject, Request $request): string
    {
        $transactionId = (string) $request->query('auth', '');

        switch ($event) {
            // Geld ist eingegangen. Erst hier wird abgebucht bzw. freigeschaltet.
            case 'billing':
            case 'rebill':
                if ($mismatch = $this->amountMismatch($subject, $request)) {
                    logger()->warning('Micropayment: Betrag weicht ab', $mismatch);
                    $this->log($subject, ['event' => $event, 'mismatch' => $mismatch] + $this->loggableQuery($request));

                    return 'mismatch';
                }

                $subject->markPaid($transactionId);
                $this->log($subject, ['event' => $event] + $this->loggableQuery($request));

                return 'completed';

                // Zahlung angestossen, aber noch nicht bestaetigt. Bei Open
                // Banking ein normaler Zwischenschritt. Es wird bewusst nichts
                // abgebucht und nichts versendet - offen ist nicht bezahlt.
            case 'pending':
                if ($mismatch = $this->amountMismatch($subject, $request)) {
                    logger()->warning('Micropayment: Betrag weicht ab', $mismatch);

                    return 'mismatch';
                }

                $subject->markInitiated($transactionId);
                $this->log($subject, ['event' => $event] + $this->loggableQuery($request));

                return 'pending';

                // Vorgang wurde eroeffnet. Nur festhalten.
            case 'init':
                $subject->markInitiated($transactionId);

                return 'pending';

            case 'error':
            case 'info':
                $this->log($subject, ['event' => $event] + $this->loggableQuery($request));

                return 'failed';

                // Storno und Rueckbuchung werden vorerst nur protokolliert. Eine
                // automatische Ruecknahme waere ein eigener Vorgang und wird
                // bewusst von Hand entschieden.
            case 'pause':
            case 'quit':
            case 'expire':
            case 'payin':
            case 'change':
            case 'storno':
            case 'refund':
            case 'backpay':
                logger()->info('Micropayment: Ereignis ohne automatische Verarbeitung', [
                    'event' => $event,
                    'reference' => $subject->reference(),
                ]);
                $this->log($subject, ['event' => $event] + $this->loggableQuery($request));

                return 'fallback';

            default:
                throw new \RuntimeException(sprintf('Ereignis `%s` wird nicht unterstützt.', $event), 400);
        }
    }

    /**
     * Prueft den gemeldeten Betrag gegen den erwarteten.
     *
     * Ohne diese Pruefung liesse sich der Betrag in der Zahlungsfenster-URL
     * veraendern und der Vorgang fuer weniger Geld abschliessen.
     *
     * @return array<string, mixed>|null Abweichung, sonst null
     */
    private function amountMismatch(MicropaymentSubject $subject, Request $request): ?array
    {
        if (! config('micropayment.notification.check_amount')) {
            return null;
        }

        $expected = $subject->amountInCents();
        $reported = (int) $request->query('amount', 0);
        $currency = strtoupper((string) $request->query('currency', 'EUR'));

        if ($reported === $expected && $currency === 'EUR') {
            return null;
        }

        return [
            'reference' => $subject->reference(),
            'expected_amount' => $expected,
            'reported_amount' => $reported,
            'currency' => $currency,
        ];
    }

    /**
     * Schreibt den Vorgang in das Protokoll, wie es der PayPal-Zweig in
     * CheckoutController::processPayment() auch tut.
     *
     * @param  array<string, mixed>  $details
     */
    private function log(MicropaymentSubject $subject, array $details): void
    {
        try {
            $context = $subject->logContext();

            Log::create([
                'details' => json_encode($details + ['reference' => $subject->reference()]),
                'email' => $context['email'] ?? null,
                'admin_id' => $context['admin_id'] ?? null,
                'user_id' => $context['user_id'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // Das Protokoll darf eine Zahlung nie scheitern lassen.
            logger()->warning('Micropayment: Protokolleintrag fehlgeschlagen', [
                'reference' => $subject->reference(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Adresse der Ergebnisseite inklusive Merkmal des Vorgangs.
     */
    private function resultUrl(string $state, ?MicropaymentSubject $subject): string
    {
        return route('payment.micropayment.result', array_filter([
            'state' => $state,
            'ref' => $subject?->reference(),
            'token' => $subject?->token(),
        ]));
    }

    /**
     * Antwort an Micropayment.
     *
     * Reiner Text, ein Feld pro Zeile. `url` bestimmt, wohin die Kundin nach
     * dem Bezahlen geleitet wird.
     *
     * @see https://controlcenter.micropayment.de/help/integration_web/#integration_web-notification-response
     */
    private function notificationResponse(string $status, string $state, ?MicropaymentSubject $subject, ?string $message = null): Response
    {
        $body = '';

        if (filled($message)) {
            $body .= 'errormessage='.str_replace(["\r", "\n"], ' ', $message)."\n";
        }

        $body .= 'status='.$status."\n";
        $body .= 'url='.$this->resultUrl($state, $subject)."\n";
        $body .= 'target='.config('micropayment.notification.target')."\n";
        $body .= 'forward='.(config('micropayment.notification.forward') ? 1 : 0)."\n";

        return response($body, 200)->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
