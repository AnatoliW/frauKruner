<?php

namespace App\Payment;

use App\Order;

/**
 * Eine Shop-Bestellung als Bezahlobjekt der Online-Ueberweisung.
 */
class MicropaymentOrderSubject extends MicropaymentSubject
{
    public function __construct(public readonly Order $order) {}

    public static function findByKey(int $key): ?static
    {
        $order = Order::find($key);

        return $order ? new static($order) : null;
    }

    /**
     * Bewusst dasselbe Format wie bei Vorkasse (siehe pre_thank.blade.php),
     * damit Kundin, Kontoauszug und Bestellung dieselbe Kennung tragen.
     */
    public function reference(): string
    {
        return 'FK'.$this->order->created_at->year.'-'.$this->order->id;
    }

    /**
     * `total` ist der Betrag, den die Kundin im Warenkorb gesehen hat: Rabatt
     * bereits abgezogen, Versand nicht aufgeschlagen. Genau diesen Betrag nennt
     * auch die Vorkasse-Seite. Die Zahlart darf davon nicht abweichen.
     */
    public function amountInCents(): int
    {
        return (int) round(((float) $this->order->total) * 100);
    }

    /**
     * Nur die Kennung, z. B. `FK2026-12088`. Bewusst ohne Zusatztext: So steht
     * auf dem Kontoauszug genau das, wonach die Bestellung auch gesucht wird.
     */
    public function paytext(): string
    {
        return $this->reference();
    }

    public function firstName(): string
    {
        return (string) $this->order->first_name;
    }

    public function lastName(): string
    {
        return (string) $this->order->last_name;
    }

    public function email(): string
    {
        return (string) $this->order->email;
    }

    public function isPaid(): bool
    {
        return (int) $this->order->payment_status === 1;
    }

    public function markRedirected(): void
    {
        $this->order->update(['payment_gateway' => MicropaymentGateway::GATEWAY]);
    }

    /**
     * Zahlung angestossen, aber noch nicht bestaetigt.
     *
     * Die Bestellung ist damit verbindlich - der Gutschein gilt als genutzt, so
     * wie bei Vorkasse auch. Bestand und Mails bleiben aussen vor, bis das Geld
     * da ist.
     */
    public function markInitiated(string $transactionId): void
    {
        $this->stampGateway($transactionId);

        $this->order->claimConfirmation();
        $this->order->redeemCoupon();
    }

    /**
     * Die eigentliche Arbeit macht Order::markAsPaid() je Unterbestellung:
     * Status setzen, Bestand abbuchen, Kaeufer- und Verkaeufer-Mail. Die Methode
     * gibt false zurueck, wenn die Unterbestellung schon bezahlt war - eine
     * zweite Benachrichtigung loest deshalb weder eine zweite Abbuchung noch
     * eine zweite Mail aus.
     */
    public function markPaid(string $transactionId): void
    {
        $this->stampGateway($transactionId);

        // Setzt confirmed_at beim ersten Mal. Das Ergebnis wird nicht als
        // Abbruchbedingung benutzt: Haette die Kundin die Bestellung vorher
        // schon einmal bestaetigt, duerfte der Zahlungseingang trotzdem nicht
        // verloren gehen. Die Einmaligkeit sichern markAsPaid() und
        // redeemCoupon() jeweils selbst ab.
        $this->order->claimConfirmation();

        foreach ($this->order->childrens as $child) {
            $child->update([
                'payment_gateway' => MicropaymentGateway::GATEWAY,
                'payment_id' => filled($transactionId) ? $transactionId : $child->payment_id,
            ]);

            $child->markAsPaid();
        }

        $this->order->update(['payment_status' => 1, 'status' => 1]);

        $this->order->redeemCoupon();
    }

    public function backUrl(): string
    {
        return route('payment', $this->order);
    }

    public function logContext(): array
    {
        return [
            'order_id' => $this->order->id,
            'email' => $this->order->email,
            'admin_id' => $this->order->vendor_id,
            'user_id' => $this->order->user_id,
        ];
    }

    private function stampGateway(string $transactionId): void
    {
        $this->order->update([
            'payment_gateway' => MicropaymentGateway::GATEWAY,
            'payment_id' => filled($transactionId) ? $transactionId : $this->order->payment_id,
        ]);
    }
}
