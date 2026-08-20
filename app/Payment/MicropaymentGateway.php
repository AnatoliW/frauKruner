<?php

namespace App\Payment;

/**
 * Baut die Zahlungsfenster-URL fuer Micropayment und prueft die Rueckmeldungen.
 *
 * Die Online-Ueberweisung laeuft nicht ueber den NVP-Service-Client, sondern
 * ueber ein gehostetes Zahlungsfenster: Der Kunde wird mit signierten Parametern
 * dorthin geleitet, bezahlt bei seiner Bank und Micropayment meldet den Status
 * anschliessend serverseitig an unseren Notification-Endpunkt zurueck.
 *
 * Vorbild ist das offizielle Beispielpaket `php_integration-web_example-advanced`
 * (tools.php: sealUrl/generatePaymentWindowUrl, notification/index.php).
 *
 * @see https://controlcenter.micropayment.de/help/integration_web/
 */
class MicropaymentGateway
{
    /**
     * Kennzeichen in orders.payment_gateway bzw. payments.payment_method.
     */
    public const GATEWAY = 'online_transfer';

    /**
     * Wird die Zahlart im Checkout angeboten?
     */
    public static function isEnabled(): bool
    {
        return (bool) config('micropayment.enabled');
    }

    /**
     * Liegen die Zugangsdaten vor?
     *
     * Fehlt etwas davon, wuerde Micropayment den Aufruf ohnehin ablehnen: ohne
     * AccessKey ist die Signatur wertlos, ohne Projektkuerzel laesst sich die
     * Buchung keinem Projekt zuordnen. Statt den Kunden auf die Fehlerseite
     * eines fremden Servers zu schicken, zeigt der Controller dann eine
     * Vorschau der erzeugten URL. Produktiv ist beides gesetzt, dort greift der
     * Vorschaumodus also nie.
     */
    public static function isConfigured(): bool
    {
        return static::missingConfiguration() === [];
    }

    /**
     * Welche Einstellungen fehlen noch?
     *
     * Gibt die Namen der Eintraege aus der .env zurueck, damit die Vorschauseite
     * benennen kann, woran es liegt - "AccessKey fehlt" waere irrefuehrend,
     * wenn in Wahrheit das Projektkuerzel fehlt.
     *
     * @return array<string, string> Name des .env-Eintrags => Bedeutung
     */
    public static function missingConfiguration(): array
    {
        $required = [
            'MICROPAYMENT_ACCESS_KEY' => ['micropayment.access_key', 'AccessKey aus „Meine Konfiguration“'],
            'MICROPAYMENT_PROJECT' => ['micropayment.project', 'Projektkürzel aus den Projekteinstellungen'],
        ];

        $missing = [];

        foreach ($required as $variable => [$key, $description]) {
            if (blank(config($key))) {
                $missing[$variable] = $description;
            }
        }

        return $missing;
    }

    /**
     * Vollstaendige, signierte Zahlungsfenster-URL.
     */
    public static function paymentUrl(MicropaymentSubject $subject): string
    {
        return static::buildUrl(
            (string) config('micropayment.online_transfer_url'),
            static::securedParameters($subject),
            static::unsecuredParameters()
        );
    }

    /**
     * Die signierten Parameter. Alles, was die Buchung ausmacht - Projekt,
     * Referenz, Betrag, Waehrung, Testmodus - gehoert hier hinein, damit es
     * unterwegs nicht veraendert werden kann.
     *
     * @return array<string, string|int>
     */
    public static function securedParameters(MicropaymentSubject $subject): array
    {
        $parameters = [
            'project' => (string) config('micropayment.project'),
            'title' => $subject->reference(),
            'paytext' => $subject->paytext(),
            'amount' => $subject->amountInCents(),
            'currency' => 'EUR',
            'testmode' => config('micropayment.testmode') ? 1 : 0,

            // Darstellung und Buchungsart, siehe config/micropayment.php.
            'theme' => (string) config('micropayment.window.theme'),
            'gfx' => (string) config('micropayment.window.gfx'),
            'bgcolor' => (string) config('micropayment.window.bgcolor'),
            'producttype' => (string) config('micropayment.window.producttype'),

            'mp_user_firstname' => $subject->firstName(),
            'mp_user_surname' => $subject->lastName(),
            'mp_user_email' => $subject->email(),
            'mp_user_id' => sha1($subject->email()),

            'orderToken' => $subject->token(),
        ];

        // Leere Werte gar nicht erst senden, damit Micropayment auf die
        // Voreinstellung des Projekts zurueckfaellt. Bewusst ohne array_filter:
        // `testmode` und `amount` duerfen den Wert 0 tragen und muessen bleiben.
        return array_filter($parameters, fn ($value) => $value !== '' && $value !== null);
    }

    /**
     * Nicht signierte Parameter. Nur Dinge ohne Einfluss auf die Buchung, damit
     * sie sich spaeter ohne neue Signatur aendern lassen.
     *
     * @return array<string, string>
     */
    public static function unsecuredParameters(): array
    {
        return [
            'lang' => (string) config('micropayment.language'),
        ];
    }

    /**
     * Signatur ueber die zu schuetzenden Parameter.
     *
     * Verfahren wie in tools.php des Beispielpakets: MD5 ueber den dekodierten
     * Query-String der signierten Parameter, gefolgt vom AccessKey. Das Verfahren
     * gibt Micropayment vor, es ist nicht frei waehlbar.
     */
    public static function seal(array $securedParameters): string
    {
        return md5(urldecode(static::query($securedParameters)).(string) config('micropayment.access_key'));
    }

    /**
     * Setzt die URL zusammen: signierte Parameter, dann `seal`, dann der Rest.
     *
     * Die Reihenfolge ist Teil des Verfahrens - alles hinter `seal` gilt als
     * ungeschuetzt.
     */
    public static function buildUrl(string $baseUrl, array $securedParameters, array $unsecuredParameters = []): string
    {
        $url = rtrim($baseUrl, '?')
            .'?'.static::query($securedParameters)
            .'&seal='.static::seal($securedParameters);

        if ($unsecuredParameters !== []) {
            $url .= '&'.static::query($unsecuredParameters);
        }

        return $url;
    }

    /**
     * @param  array<string, string|int>  $parameters
     */
    private static function query(array $parameters): string
    {
        return http_build_query($parameters, '', '&');
    }
}
