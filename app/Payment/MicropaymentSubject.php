<?php

namespace App\Payment;

/**
 * Das, was gerade bezahlt wird.
 *
 * Die Online-Ueberweisung wird an zwei Stellen benutzt: fuer eine Bestellung im
 * Shop und fuer einen bezahlten Boost ("Profil pushen"). Ablauf, Signatur und
 * Benachrichtigung sind identisch, nur Referenz, Betrag und das, was beim
 * Zahlungseingang passieren muss, unterscheiden sich.
 *
 * Diese Klasse buendelt genau diese Unterschiede. Dadurch gibt es weiterhin nur
 * einen Benachrichtigungs-Endpunkt und einen Signaturweg - die beiden Abläufe
 * koennen also nicht auseinanderlaufen.
 */
abstract class MicropaymentSubject
{
    /**
     * Praefix der Referenz => zustaendige Klasse.
     *
     * Micropayment liefert die Referenz in `title` zurueck. Am Praefix erkennen
     * wir, worum es sich handelt; die Praefixe muessen deshalb eindeutig bleiben.
     *
     * @var array<string, class-string<MicropaymentSubject>>
     */
    private const KINDS = [
        // Bestellung im Shop, Schluessel ist die Bestellnummer.
        'FK' => [MicropaymentOrderSubject::class, 'findByKey'],

        // Hervorhebung, Schluessel ist die Boost-Nummer. Dieses Format traegt
        // dieselbe Zeichenkette wie die Rechnungsnummer (Boost::invoice_number),
        // damit Beleg und Kontoauszug zusammenpassen.
        'FKP' => [MicropaymentBoostSubject::class, 'findByBoostKey'],

        // Frueheres Format der Hervorhebung, Schluessel ist die Zahlungsnummer.
        // Bleibt dauerhaft bestehen: Zahlungen, die vor der Umstellung
        // losgeschickt wurden, melden sich noch damit zurueck - und eine
        // Meldung, die wir nicht aufloesen, ist verlorenes Geld.
        'BP' => [MicropaymentBoostSubject::class, 'findByKey'],
    ];

    /**
     * Verwendungszweck bzw. Referenz, z. B. `FK2026-4711`.
     */
    abstract public function reference(): string;

    /**
     * Zu zahlender Betrag in ganzen Cent.
     */
    abstract public function amountInCents(): int;

    /**
     * Text, den die Kundin im Zahlungsfenster sieht.
     */
    abstract public function paytext(): string;

    abstract public function firstName(): string;

    abstract public function lastName(): string;

    abstract public function email(): string;

    /**
     * Ist schon bezahlt? Verhindert eine zweite Weiterleitung.
     */
    abstract public function isPaid(): bool;

    /**
     * Haelt fest, dass zur Online-Ueberweisung weitergeleitet wurde.
     *
     * Bricht die Kundin im Zahlungsfenster ab, ist damit trotzdem
     * nachvollziehbar, worauf sie geleitet wurde.
     */
    abstract public function markRedirected(): void;

    /**
     * Zahlung angestossen, Geld aber noch nicht da.
     */
    abstract public function markInitiated(string $transactionId): void;

    /**
     * Zahlungseingang buchen. Muss bei mehrfachem Aufruf dasselbe Ergebnis
     * liefern - Micropayment stellt eine Benachrichtigung notfalls erneut zu.
     */
    abstract public function markPaid(string $transactionId): void;

    /**
     * Wohin es zurueckgeht, wenn die Zahlung abgebrochen wurde.
     */
    abstract public function backUrl(): string;

    /**
     * Zusatzangaben fuers Protokoll.
     *
     * @return array<string, mixed>
     */
    abstract public function logContext(): array;

    /**
     * Sucht das Bezahlobjekt anhand seiner Referenz.
     */
    abstract public static function findByKey(int $key): ?static;

    /**
     * Loest eine Referenz aus der Benachrichtigung auf.
     *
     * `title` ist einer der dokumentierten Standardplatzhalter und damit die
     * verlaesslichste Zuordnung.
     */
    public static function fromReference(?string $reference): ?self
    {
        // Zwei Schreibweisen: `FK2026-4711` (Praefix direkt am Jahr) und
        // `FKP-2026-1787` (Praefix mit Bindestrich). Der gierige Quantor nimmt
        // erst das laengste Praefix, deshalb kann `FKP-...` nie als `FK`
        // durchgehen: Nach `FK` stuende ein `P`, wo Jahr oder Bindestrich
        // hingehoeren. Eine Hervorhebung kann so nicht auf einer Bestellung
        // gebucht werden.
        if (! $reference || ! preg_match('/^([A-Z]{2,3})-?(\d{4})-(\d+)$/', trim($reference), $matches)) {
            return null;
        }

        [$class, $resolve] = self::KINDS[$matches[1]] ?? [null, null];

        if (! $class) {
            return null;
        }

        return $class::$resolve((int) $matches[3]);
    }

    /**
     * Zusaetzliches, nur uns bekanntes Merkmal.
     *
     * Aus der Referenz und dem App-Key abgeleitet, damit nichts gespeichert
     * werden muss. Wird als eigener Parameter mitsigniert; ob Micropayment ihn
     * an die Benachrichtigung durchreicht, haengt an der Konfiguration der
     * Zahlart im Control-Center - deshalb ist die Pruefung eine zusaetzliche
     * Huerde und nicht die eigentliche Absicherung. Die uebernehmen Geheimfeld,
     * Betragspruefung und die Zugriffsbeschraenkung auf dem Webserver.
     *
     * Weil die Referenz das Praefix enthaelt, koennen Bestellung 12 und Boost 12
     * nicht dasselbe Merkmal tragen.
     */
    public function token(): string
    {
        return static::tokenFor($this->reference());
    }

    /**
     * Merkmal zu einer beliebigen Referenz.
     *
     * Ausgelagert, weil eine Zahlung ihr Merkmal aus der Referenz traegt, mit
     * der sie losgeschickt wurde. Aendert sich das Referenzformat, muss eine
     * bereits unterwegs befindliche Zahlung ihr altes Merkmal weiterhin
     * vorweisen duerfen - sonst kaeme ihre Benachrichtigung als `Ungueltiges
     * Merkmal` zurueck, obwohl bezahlt wurde. Siehe
     * MicropaymentBoostSubject::tokenMatches().
     */
    protected static function tokenFor(string $reference): string
    {
        return substr(hash_hmac('sha256', 'micropayment:'.$reference, (string) config('app.key')), 0, 32);
    }

    /**
     * Prueft das Merkmal aus der Benachrichtigung, sofern eines mitgeliefert wurde.
     */
    public function tokenMatches(?string $token): bool
    {
        if (! filled($token)) {
            return true;
        }

        return hash_equals($this->token(), $token);
    }

    /**
     * Betrag als Text fuer die Ergebnisseite.
     */
    public function amountForHumans(): string
    {
        return number_format($this->amountInCents() / 100, 2, ',', '.').' €';
    }
}
