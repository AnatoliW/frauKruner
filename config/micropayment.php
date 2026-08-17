<?php

return [

    /*
    |---------------------------------------------------------------------------
    | Zahlungsfenster (Payment Window)
    |---------------------------------------------------------------------------
    |
    | Anbindung an Micropayment nach dem Muster des offiziellen Beispielpakets
    | `php_integration-web_example-advanced`. Es gibt fuer die Online-Ueberweisung
    | keine Server-zu-Server-API: Der Kunde wird auf ein gehostetes Zahlungsfenster
    | geleitet, der Zahlungsstatus kommt anschliessend serverseitig per
    | Benachrichtigung (Notification) zurueck.
    |
    | @see https://controlcenter.micropayment.de/help/integration_web/
    |
    */

    'enabled' => (bool) env('MICROPAYMENT_ENABLED', true),

    /*
     * AccessKey, Projektkuerzel und Kontonummer stehen im Micropayment
     * Control-Center. Ohne AccessKey laeuft die Integration im Vorschaumodus,
     * siehe MicropaymentGateway::isConfigured().
     */
    'access_key' => env('MICROPAYMENT_ACCESS_KEY', ''),
    'project' => env('MICROPAYMENT_PROJECT', ''),
    'account' => env('MICROPAYMENT_ACCOUNT', ''),

    /*
     * Solange true, laufen alle Buchungen im Testmodus und es fliesst kein Geld.
     * Vor dem Livegang auf false stellen.
     *
     * Die Voreinstellung ist bewusst false: Ein vergessener Wert soll dazu
     * fuehren, dass eine Zahlung fehlschlaegt, nicht dazu, dass Ware ohne
     * Gegenwert versandt wird. Zum Testen ausdruecklich MICROPAYMENT_TESTMODE=true
     * setzen.
     */
    'testmode' => (bool) env('MICROPAYMENT_TESTMODE', false),

    /*
     * Basis-URL des Zahlungsfensters fuer die Online-Ueberweisung.
     *
     * Aufbau laut Micropayment:
     *
     *     https://<paymentdomain>.micropayment.de/<payment>/<service>/
     *
     * Hier also Domain `directbanking`, Zahlart `sofort`, Service `event`.
     * Dass Domain und Pfad auseinanderlaufen, ist historisch gewachsen und laut
     * Micropayment so vorgesehen - der Wert stammt aus dem Control-Center und
     * ist gegen das Zahlungsfenster geprueft. Nicht auf `sofort/sofort/event`
     * "korrigieren", das ist nicht die ausgegebene Adresse.
     *
     * Die Zahlart wurde 2024 von Klarna/Sofort auf Tink (Open Banking,
     * "Pay by Bank") umgestellt, die technischen Bezeichner heissen aber
     * weiterhin `sofort`. Die verbindliche Liste steht im Control-Center unter
     * Hilfe -> Integration Web -> URL. Deshalb steht die URL hier als
     * Konfigurationswert und nicht fest im Code.
     */
    'online_transfer_url' => env(
        'MICROPAYMENT_ONLINE_TRANSFER_URL',
        'https://directbanking.micropayment.de/sofort/event/'
    ),

    /*
     * Sprache des Zahlungsfensters. Wird bewusst nicht mitsigniert, damit sie
     * sich ohne neue Signatur aendern laesst.
     */
    'language' => env('MICROPAYMENT_LANGUAGE', 'de'),

    /*
    |---------------------------------------------------------------------------
    | Darstellung des Zahlungsfensters
    |---------------------------------------------------------------------------
    |
    | Diese Werte stellt das Control-Center zusammen mit der Bezahlfenster-URL
    | bereit. Sie steuern Aussehen und Buchungsart und werden mitsigniert.
    |
    | Was leer bleibt, wird gar nicht erst uebertragen - Micropayment faellt dann
    | auf die Voreinstellung des Projekts zurueck.
    |
    */

    'window' => [
        // Design des Zahlungsfensters, z. B. `x1`
        'theme' => env('MICROPAYMENT_THEME', ''),

        // Kennung der hinterlegten Grafik, z. B. `39780-B4A9848AB9`
        'gfx' => env('MICROPAYMENT_GFX', ''),

        // Hintergrundfarbe ohne Raute, z. B. `ffffff`
        'bgcolor' => env('MICROPAYMENT_BGCOLOR', ''),

        // Buchungsart, z. B. `product`
        'producttype' => env('MICROPAYMENT_PRODUCTTYPE', ''),
    ],

    /*
    |---------------------------------------------------------------------------
    | Benachrichtigung (Notification)
    |---------------------------------------------------------------------------
    |
    | Der Endpunkt, den Micropayment nach jeder Statusaenderung serverseitig
    | aufruft. Er ist die einzige verlaessliche Quelle fuer "bezahlt" - die
    | Rueckkehr im Browser darf den Zahlungsstatus nie setzen.
    |
    */

    'notification' => [

        /*
         * Zusaetzliches Geheimfeld. Im Control-Center bei der Zahlart als
         * zusaetzlicher Parameter hinterlegen, dann hier denselben Wert setzen.
         *
         * Es ist die einzige Absicherung des Benachrichtigungs-Endpunkts: Ohne
         * Geheimfeld genuegen Bestellnummer und Betrag, um eine Bestellung als
         * bezahlt zu melden. Solange es leer ist, lehnt
         * MicropaymentController::guardNotification() deshalb jede Meldung ab -
         * es sei denn, `allow_unauthenticated` ist fuer die Einrichtung
         * ausdruecklich gesetzt und die Anwendung laeuft nicht produktiv.
         *
         * Der Wert darf nicht ins Protokoll: siehe
         * MicropaymentController::loggableQuery().
         */
        'secret_field_name' => env('MICROPAYMENT_SECRET_FIELD_NAME', 'secretfield'),
        'secret_field_value' => env('MICROPAYMENT_SECRET_FIELD_VALUE', ''),

        /*
         * Meldungen ohne Geheimfeld annehmen - ausschliesslich fuer die
         * Einrichtung, solange im Control-Center noch nichts hinterlegt ist.
         *
         * Damit steht der Endpunkt jedem offen, der eine Bestellnummer und den
         * Betrag kennt. Die Freischaltung muss deshalb ausdruecklich erfolgen
         * und wirkt produktiv gar nicht: dort lehnt
         * MicropaymentController::guardNotification() ohne Geheimfeld jede
         * Meldung ab, unabhaengig von diesem Wert.
         */
        'allow_unauthenticated' => (bool) env('MICROPAYMENT_ALLOW_UNAUTHENTICATED_NOTIFICATION', false),

        /*
         * Benachrichtigungen aus dem Testmodus annehmen. Nach dem Livegang auf
         * false setzen, sonst laesst sich eine Bestellung mit einer
         * Testbuchung als bezahlt markieren.
         *
         * Voreinstellung false aus demselben Grund wie bei `testmode`: Eine
         * vergessene Einstellung soll eine Zahlung scheitern lassen, nicht
         * Ware freigeben.
         */
        'allow_testmode' => (bool) env('MICROPAYMENT_ALLOW_TESTMODE_NOTIFICATION', false),

        /*
         * Aufrufe des Notification-Testwerkzeugs aus dem Control-Center
         * annehmen. Nur voruebergehend zum Testen einschalten.
         */
        'allow_apicheck' => (bool) env('MICROPAYMENT_ALLOW_APICHECK', false),

        /*
         * Gemeldeten Betrag gegen die Bestellsumme pruefen. Ohne diese Pruefung
         * waere der Betrag ueber die Zahlungsfenster-URL manipulierbar.
         */
        'check_amount' => (bool) env('MICROPAYMENT_CHECK_AMOUNT', true),

        /*
         * Nach der Verarbeitung den Kunden auf die Ergebnisseite weiterleiten.
         */
        'forward' => (bool) env('MICROPAYMENT_NOTIFICATION_FORWARD', true),
        'target' => env('MICROPAYMENT_NOTIFICATION_TARGET', '_self'),
    ],

];
