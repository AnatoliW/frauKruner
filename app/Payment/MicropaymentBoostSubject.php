<?php

namespace App\Payment;

use App\Models\Boost;
use App\Models\Payment;

/**
 * Ein bezahlter Boost ("Profil pushen" oder Produkt hervorheben) als
 * Bezahlobjekt der Online-Ueberweisung.
 *
 * Bezahlt wird der Payment-Datensatz, den BoostController::boostStore() zusammen
 * mit dem Boost anlegt. Freigeschaltet wird der Boost erst beim Zahlungseingang,
 * genau wie beim bisherigen PayPal-Weg in Seller\PaymentController::success().
 */
class MicropaymentBoostSubject extends MicropaymentSubject
{
    public function __construct(public readonly Payment $payment) {}

    public static function findByKey(int $key): ?static
    {
        $payment = Payment::find($key);

        return $payment ? new static($payment) : null;
    }

    /**
     * Verwendungszweck der Hervorhebung - dieselbe Zeichenkette, die als
     * Rechnungsnummer auf dem Beleg steht (Boost::invoice_number).
     *
     * Sie haengt am Boost, nicht an der Zahlung: Der Beleg gehoert zur
     * Hervorhebung, und die Kundin soll Kontoauszug und Rechnung ohne
     * Umrechnung zusammenbringen.
     *
     * Laesst sich der Boost nicht ermitteln, gilt weiter das fruehere Format.
     * Lieber ein Verwendungszweck aus der alten Welt als gar keiner - dieser
     * wird ueber `BP` naemlich weiterhin aufgeloest.
     */
    public function reference(): string
    {
        $boost = $this->payment->payable;

        if ($boost instanceof Boost && $boost->getKey() && $boost->created_at) {
            return 'FKP-'.$boost->created_at->format('Y').'-'.$boost->getKey();
        }

        return static::legacyReference($this->payment);
    }

    /**
     * Das Format vor der Umstellung, gebildet aus der Zahlung.
     *
     * Wird noch an zwei Stellen gebraucht: als Rueckfalloption oben und in
     * tokenMatches(), damit eine Zahlung, die mit dem alten Verwendungszweck
     * losgeschickt wurde, ihr altes Merkmal noch vorweisen darf.
     */
    public static function legacyReference(Payment $payment): string
    {
        return 'BP'.($payment->created_at?->year ?? now()->year).'-'.$payment->getKey();
    }

    /**
     * Aufloesung ueber die Boost-Nummer, fuer Referenzen im Format `FKP-...`.
     *
     * Bezahlt wird weiterhin der Payment-Datensatz; die Referenz nennt nur den
     * Boost. Boosts bis Nummer 314 tragen aus der PayPal-Zeit teils mehrere
     * Zahlungen (ein Bezahlversuch rechnete damals jedes Mal neu ab), ab 315
     * ist es genau eine. Fuer alles, was je eine Micropayment-Benachrichtigung
     * bekommen kann, ist die Auswahl damit eindeutig; fuer die Altfaelle wird
     * sie hier bewusst festgelegt statt dem Zufall der Datenbank ueberlassen.
     */
    public static function findByBoostKey(int $key): ?static
    {
        $boost = Boost::find($key);

        if (! $boost) {
            return null;
        }

        $payment = $boost->payments()->orderByDesc('id')->first();

        return $payment ? new static($payment) : null;
    }

    /**
     * Nimmt zusaetzlich das Merkmal aus dem frueheren Verwendungszweck an.
     *
     * Eine Zahlung, die vor der Umstellung ins Zahlungsfenster ging, traegt das
     * Merkmal aus `BP...`. Ihre Benachrichtigung kommt erst danach. Ohne diese
     * Ausnahme wuerde sie mit `Ungueltiges Merkmal` abgelehnt - das Geld waere
     * eingegangen, die Hervorhebung nie freigeschaltet.
     */
    public function tokenMatches(?string $token): bool
    {
        if (parent::tokenMatches($token)) {
            return true;
        }

        return hash_equals(static::tokenFor(static::legacyReference($this->payment)), (string) $token);
    }

    /**
     * Payment::amount liefert Euro (der Wert liegt in Cent in der Datenbank).
     * Es ist derselbe Betrag, den die Bezahlseite als Gesamtsumme anzeigt.
     */
    public function amountInCents(): int
    {
        return (int) round(((float) $this->payment->amount) * 100);
    }

    public function paytext(): string
    {
        $package = $this->payment->payable?->package?->name;

        return filled($package)
            ? 'Hervorhebung '.$package.' ('.$this->reference().')'
            : 'Hervorhebung '.$this->reference();
    }

    public function firstName(): string
    {
        return (string) ($this->userInfo()->f_name ?? $this->payment->payable?->user?->name ?? '');
    }

    public function lastName(): string
    {
        return (string) ($this->userInfo()->l_name ?? $this->payment->payable?->user?->last_name ?? '');
    }

    public function email(): string
    {
        return (string) ($this->userInfo()->email ?? $this->payment->payable?->user?->email ?? '');
    }

    public function isPaid(): bool
    {
        return $this->payment->status === 'PAID';
    }

    /**
     * Nur die gewaehlte Zahlart festhalten - der Status bleibt PENDING, bis das
     * Geld da ist. Eine bereits bezahlte Hervorhebung wird nicht angefasst.
     */
    public function markRedirected(): void
    {
        if ($this->isPaid()) {
            return;
        }

        $this->payment->update(['payment_method' => MicropaymentGateway::GATEWAY]);
    }

    /**
     * Bei einem Boost gibt es nichts vorzumerken: Freigeschaltet wird erst beim
     * Zahlungseingang, vorher bleibt der Payment auf PENDING stehen.
     */
    public function markInitiated(string $transactionId): void
    {
        if ($this->isPaid() || blank($transactionId)) {
            return;
        }

        $this->payment->update(['payment_trnx_id' => $transactionId]);
    }

    /**
     * Zahlungseingang buchen und den Boost freischalten.
     *
     * Der Status wird atomar beansprucht: Nur der Aufruf, der ihn tatsaechlich
     * von PENDING auf PAID dreht, schaltet auch frei. Micropayment stellt eine
     * Benachrichtigung notfalls erneut zu, und zwei Zustellungen koennen sich
     * ueberschneiden - ein blosses `if ($this->isPaid())` liesse dann beide
     * durch und process() verschoebe das Enddatum der Hervorhebung nach hinten.
     */
    public function markPaid(string $transactionId): void
    {
        $changes = ['status' => 'PAID', 'payment_method' => MicropaymentGateway::GATEWAY];

        if (filled($transactionId)) {
            $changes['payment_trnx_id'] = $transactionId;
        }

        $claimed = Payment::query()
            ->whereKey($this->payment->getKey())
            ->where(fn ($query) => $query->where('status', '!=', 'PAID')->orWhereNull('status'))
            ->update($changes);

        if (! $claimed) {
            return;
        }

        $this->payment->refresh();

        $this->payment->payable?->process();
    }

    public function backUrl(): string
    {
        return route('seller.payment', $this->payment);
    }

    public function logContext(): array
    {
        return [
            'payment_id' => $this->payment->id,
            'email' => $this->email(),
            'admin_id' => null,
            'user_id' => $this->payment->payable?->user_id,
        ];
    }

    /**
     * Die beim Anlegen des Boosts eingefrorenen Kundendaten.
     */
    private function userInfo(): object
    {
        $info = $this->payment->payable?->user_info;

        return is_object($info) ? $info : (object) [];
    }
}
