@php
    use App\Support\Settings;

    $message = session('message');
    $formattedAmount = number_format((float) ($payment->amount ?? 0), 2, '.', '');
@endphp

<x-filament-panels::layout>

    <style>
        .payment-checkout-shell {
            min-height: 500px;
            border-radius: 16px;
            border: 1px solid rgb(226, 232, 240);
            background: linear-gradient(180deg, rgb(255, 255, 255), rgb(248, 250, 252));
            padding: 22px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 16px;
        }

        .payment-checkout-header {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .payment-kicker {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: rgb(239, 246, 255);
            color: rgb(30, 64, 175);
            border: 1px solid rgb(191, 219, 254);
            padding: 5px 10px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .payment-checkout-title {
            margin: 10px 0 8px;
            font-size: clamp(22px, 2.6vw, 30px);
            line-height: 1.2;
            font-weight: 600;
            color: rgb(15, 23, 42);
        }

        .payment-checkout-copy {
            margin: 0;
            max-width: 520px;
            color: rgb(71, 85, 105);
            font-size: 14px;
            line-height: 1.55;
        }

        .payment-amount-card {
            min-width: 170px;
            border-radius: 12px;
            border: 1px solid rgb(226, 232, 240);
            background: rgb(255, 255, 255);
            color: rgb(15, 23, 42);
            padding: 14px 16px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }

        .payment-amount-label {
            margin: 0 0 6px;
            color: rgb(100, 116, 139);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.11em;
            text-transform: uppercase;
        }

        .payment-amount-value {
            margin: 0;
            font-size: 26px;
            line-height: 1;
            font-weight: 600;
        }

        .payment-provider-frame {
            flex: 1;
            min-height: 310px;
            border-radius: 12px;
            border: 1px solid rgb(226, 232, 240);
            background: rgb(255, 255, 255);
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
        }

        #paypal-button-container {
            width: 100%;
            max-width: 460px;
            margin: 0 auto;
        }

        .payment-provider-label {
            margin: 0 0 12px;
            color: rgb(100, 116, 139);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .payment-note {
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 12px;
            border: 1px solid rgb(226, 232, 240);
            background: rgb(248, 250, 252);
            padding: 12px 14px;
            color: rgb(71, 85, 105);
            font-size: 13px;
            line-height: 1.5;
        }

        .payment-note-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(180deg, rgb(34, 197, 94), rgb(22, 163, 74));
            flex: none;
        }

        @media (max-width: 768px) {
            .payment-checkout-shell {
                min-height: 500px;
                padding: 16px;
            }

            .payment-checkout-title {
                font-size: 22px;
            }

            .payment-provider-frame {
                min-height: 260px;
                padding: 14px;
            }
        }
    </style>

    <div class="mx-auto w-full max-w-2xl px-4 py-6">
        @if ($message)
            <x-filament::section>
                <div class="text-sm text-primary-700">
                    {{ $message }}
                </div>
            </x-filament::section>
        @endif

        <x-filament::section>
            <form action="{{ route('admin.payment.process', $payment) }}" id="payment" method="post" class="space-y-4">
                @csrf
                <input type="hidden" name="payment_id" id="payment_id">
                <input type="hidden" name="order_id" value="{{ $payment->id }}">

                <div class="payment-checkout-shell">
                    <div class="payment-checkout-header">
                        <div>
                            <span class="payment-kicker">Secure Checkout</span>
                            <h2 class="payment-checkout-title">PayPal oder Debit- und Kreditkarte</h2>
                            <p class="payment-checkout-copy">
                                Waehle PayPal oder nutze die integrierte Kartenzahlung im selben sicheren Checkout-Bereich.
                            </p>
                        </div>

                        <div class="payment-amount-card">
                            <p class="payment-amount-label">Zu zahlen</p>
                            <p class="payment-amount-value">{{ $formattedAmount }} EUR</p>
                        </div>
                    </div>

                    <div class="payment-provider-frame">
                        <p class="payment-provider-label">PayPal Checkout</p>
                        <div id="paypal-button-container"></div>
                    </div>

                    <div class="payment-note">
                        <span class="payment-note-dot"></span>
                        <span>PayPal und Debit or Credit Card werden in einem Schritt verarbeitet.</span>
                    </div>
                </div>
            </form>

            <div class="mt-4" style="margin-top: 30px;">
                <form action="{{ route('admin.payment.process', [$payment, 'method' => 'free']) }}" id="payment-free" method="post">
                    @csrf
                    <input type="hidden" name="method" value="free">
                    <x-filament::button type="submit" size="lg" color="primary" class="w-full">
                        Kostenlos
                    </x-filament::button>
                </form>
            </div>
        </x-filament::section>
    </div>

    @push('scripts')
        <script src="https://www.paypal.com/sdk/js?client-id={{ Settings::paypalClientId() }}&currency=EUR"></script>
        <script>
            paypal.Buttons({
                createOrder: function (data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: "{{ $payment->amount }}"
                            }
                        }]
                    });
                },
                onApprove: function (data, actions) {
                    return actions.order.capture().then(function (details) {
                        var form = document.getElementById('payment');

                        if (details.status === 'COMPLETED') {
                            document.getElementById('payment_id').value = details.id;
                            form.submit();
                        }
                    });
                },
            }).render('#paypal-button-container');
        </script>
    @endpush
</x-filament-panels::layout>
