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
        'FK' => MicropaymentOrderSubject::class,
        'BP' => MicropaymentBoostSubject::class,
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
        if (! $reference || ! preg_match('/^([A-Z]{2})\d{4}-(\d+)$/', trim($reference), $matches)) {
            return null;
        }

        $class = self::KINDS[$matches[1]] ?? null;

        if (! $class) {
            return null;
        }

        return $class::findByKey((int) $matches[2]);
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
        return substr(hash_hmac('sha256', 'micropayment:'.$this->reference(), (string) config('app.key')), 0, 32);
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
