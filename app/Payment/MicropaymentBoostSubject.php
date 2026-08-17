<?php

namespace App\Payment;

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

    public function reference(): string
    {
        return 'BP'.$this->payment->created_at->year.'-'.$this->payment->id;
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
