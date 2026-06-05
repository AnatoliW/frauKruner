@php
    use App\Support\Settings;

    $message = session('message');
    $formattedAmount = number_format((float) ($payment->amount ?? 0), 2, '.', '');
@endphp

<x-filament-panels::layout>
    <style>
        .checkout-page {
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
            padding: 1.5rem 1rem;
        }

        .checkout-stack {
            display: grid;
            gap: 1rem;
        }

        .checkout-card {
            overflow: hidden;
            border-radius: 1.25rem;
            border: 1px solid rgb(var(--gray-200));
            background: rgb(var(--gray-50));
        }

        .dark .checkout-card {
            border-color: rgba(var(--gray-700), 0.8);
            background: rgba(var(--gray-900), 0.78);
        }

        .checkout-header {
            padding: 1.25rem;
            border-bottom: 1px solid rgb(var(--gray-200));
            background:
                radial-gradient(circle at top right, rgba(var(--primary-500), 0.13), transparent 34%),
                rgb(var(--gray-50));
        }

        .dark .checkout-header {
            border-bottom-color: rgba(var(--gray-700), 0.8);
            background:
                radial-gradient(circle at top right, rgba(var(--primary-500), 0.18), transparent 34%),
                rgba(var(--gray-900), 0.9);
        }

        .checkout-header-inner {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 185px;
            gap: 1rem;
            align-items: start;
        }

        .checkout-kicker {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            border: 1px solid rgba(var(--primary-500), 0.24);
            background: rgba(var(--primary-500), 0.09);
            color: rgb(var(--primary-700));
            padding: 0.42rem 0.75rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .dark .checkout-kicker {
            border-color: rgba(var(--primary-500), 0.32);
            background: rgba(var(--primary-500), 0.15);
            color: rgb(var(--primary-300));
        }

        .checkout-title {
            margin: 0.85rem 0 0;
            font-size: clamp(1.45rem, 2.4vw, 2.05rem);
            font-weight: 850;
            line-height: 1.12;
            letter-spacing: -0.035em;
            color: rgb(var(--gray-950));
        }

        .dark .checkout-title {
            color: rgb(var(--gray-50));
        }

        .checkout-copy {
            margin: 0.6rem 0 0;
            max-width: 520px;
            font-size: 0.9rem;
            line-height: 1.6;
            color: rgb(var(--gray-500));
        }

        .dark .checkout-copy {
            color: rgb(var(--gray-400));
        }

        .checkout-amount-card {
            border-radius: 1rem;
            border: 1px solid rgb(var(--gray-200));
            background: rgba(255, 255, 255, 0.82);
            padding: 1rem;
            text-align: right;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
        }

        .dark .checkout-amount-card {
            /* border-color: rgba(var(--gray-700), 0.8); */
            background: rgba(var(--gray-950), 0.45);
            /* box-shadow: 0 16px 36px rgba(0, 0, 0, 0.28); */
        }

        .checkout-amount-label {
            margin: 0 0 0.45rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgb(var(--gray-500));
        }

        .dark .checkout-amount-label {
            color: rgb(var(--gray-400));
        }

        .checkout-amount-value {
            margin: 0;
            font-size: 1.9rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.05em;
            color: rgb(var(--gray-950));
        }

        .dark .checkout-amount-value {
            color: rgb(var(--gray-50));
        }

        .checkout-amount-currency {
            margin-top: 0.35rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: rgb(var(--gray-500));
        }

        .dark .checkout-amount-currency {
            color: rgb(var(--gray-400));
        }

        .checkout-body {
            padding: 1.25rem;
        }

        .payment-method-card {
            border-radius: 1.15rem;
            border: 1px solid rgb(var(--gray-200));
            background: rgb(var(--gray-100));
            padding: 1rem;
        }

        .dark .payment-method-card {
            border-color: rgba(var(--gray-700), 0.8);
            background: rgba(var(--gray-800), 0.72);
        }

        .payment-method-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .payment-method-title {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 850;
            line-height: 1.3;
            color: rgb(var(--gray-950));
        }

        .dark .payment-method-title {
            color: rgb(var(--gray-50));
        }

        .payment-method-copy {
            margin: 0.3rem 0 0;
            font-size: 0.82rem;
            line-height: 1.5;
            color: rgb(var(--gray-500));
        }

        .dark .payment-method-copy {
            color: rgb(var(--gray-400));
        }

        /*
        |--------------------------------------------------------------------------
        | PayPal Section - Fixed Height 300px
        |--------------------------------------------------------------------------
        */
        .payment-provider-box {
            height: 300px !important;
            min-height: 300px !important;
            max-height: 300px !important;
            overflow: hidden;
            border-radius: 1rem;
            border: 1px solid rgb(var(--gray-200));
            background:
                linear-gradient(180deg, rgb(var(--gray-50)), rgb(var(--gray-100)));
            padding: 1rem;
            display: flex;
            flex-direction: column;
        }

        .dark .payment-provider-box {
            border-color: rgba(var(--gray-700), 0.85);
            background:
                linear-gradient(180deg, rgba(var(--gray-900), 0.75), rgba(var(--gray-950), 0.45));
        }

        .payment-provider-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.9rem;
            flex-shrink: 0;
        }

        .payment-provider-label {
            margin: 0;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgb(var(--gray-500));
        }

        .dark .payment-provider-label {
            color: rgb(var(--gray-400));
        }

        .payment-provider-inner {
            flex: 1;
            min-height: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.85rem;
            border: 1px dashed rgb(var(--gray-300));
            background: rgba(255, 255, 255, 0.64);
            padding: 1rem;
        }

        .dark .payment-provider-inner {
            border-color: rgba(var(--gray-700), 0.9);
            background: rgba(var(--gray-900), 0.58);
        }

        #paypal-button-container {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
        }

        .checkout-benefits {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .checkout-benefit {
            display: flex;
            align-items: flex-start;
            gap: 0.55rem;
            border-radius: 0.9rem;
            border: 1px solid rgb(var(--gray-200));
            background: rgb(var(--gray-50));
            padding: 0.75rem;
        }

        .dark .checkout-benefit {
            border-color: rgba(var(--gray-700), 0.8);
            background: rgba(var(--gray-900), 0.55);
        }

        .checkout-benefit-icon {
            display: inline-flex;
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(var(--success-500), 0.12);
            color: rgb(var(--success-600));
            font-size: 0.75rem;
            font-weight: 900;
        }

        .dark .checkout-benefit-icon {
            background: rgba(var(--success-500), 0.18);
            color: rgb(var(--success-400));
        }

        .checkout-benefit-text {
            margin: 0;
            font-size: 0.78rem;
            line-height: 1.45;
            color: rgb(var(--gray-600));
        }

        .dark .checkout-benefit-text {
            color: rgb(var(--gray-300));
        }

        .free-card {
            border-radius: 1.15rem;
            border: 1px solid rgb(var(--gray-200));
            background: rgb(var(--gray-50));
            padding: 1rem;
        }

        .dark .free-card {
            border-color: rgba(var(--gray-700), 0.8);
            background: rgba(var(--gray-900), 0.78);
        }

        .free-card-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .free-title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 850;
            color: rgb(var(--gray-950));
        }

        .dark .free-title {
            color: rgb(var(--gray-50));
        }

        .free-copy {
            margin: 0.25rem 0 0;
            font-size: 0.82rem;
            line-height: 1.5;
            color: rgb(var(--gray-500));
        }

        .dark .free-copy {
            color: rgb(var(--gray-400));
        }

        .checkout-message {
            margin-bottom: 1rem;
        }

        #buttons-container .paypal-button-container {
            /* min-width: 300px; */
            max-width: 500px;
            height: 201px !important;
        }

        @media (max-width: 768px) {
            .checkout-page {
                padding: 1rem;
            }

            .checkout-header,
            .checkout-body {
                padding: 1rem;
            }

            .checkout-header-inner {
                grid-template-columns: 1fr;
            }

            .checkout-amount-card {
                text-align: left;
            }

            .payment-provider-box {
                height: 300px;
                min-height: 300px;
                max-height: 300px;
            }

            .checkout-benefits {
                grid-template-columns: 1fr;
            }

            .free-card-inner {
                align-items: stretch;
                flex-direction: column;
            }

            .free-card .fi-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media only screen and (min-width: 300px) {
            .paypal-button-container {
                /* min-width: 300px; */
                max-width: 500px;
                height: 201px !important;
            }
        }
    </style>

    <div class="checkout-page">
        @if ($message)
            <div class="checkout-message">
                <x-filament::section>
                    <div class="text-sm font-medium text-primary-700 dark:text-primary-300">
                        {{ $message }}
                    </div>
                </x-filament::section>
            </div>
        @endif

        <div class="checkout-stack">
            <x-filament::section>
                <form action="{{ route('admin.payment.process', $payment) }}" id="payment" method="post">
                    @csrf

                    <input type="hidden" name="payment_id" id="payment_id">
                    <input type="hidden" name="order_id" value="{{ $payment->id }}">

                    <div class="checkout-card">
                        <div class="checkout-header">
                            <div class="checkout-header-inner">
                                <div>
                                    <div class="checkout-kicker">
                                        Sicherer Checkout
                                    </div>

                                    <h2 class="checkout-title">
                                        Zahlung abschließen
                                    </h2>

                                    <p class="checkout-copy">
                                        Wähle PayPal, Debitkarte oder Kreditkarte. Deine Zahlung wird sicher
                                        verarbeitet.
                                    </p>
                                </div>

                                <div class="checkout-amount-card">
                                    <p class="checkout-amount-label">
                                        Gesamtbetrag
                                    </p>

                                    <p class="checkout-amount-value">
                                        {{ $formattedAmount }} €
                                    </p>

                                    <div class="checkout-amount-currency">
                                        EUR
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-body">
                            <div class="payment-method-card">
                                <div class="payment-method-header">
                                    <div>
                                        <p class="payment-method-title">
                                            Zahlungsmethode wählen
                                        </p>

                                        <p class="payment-method-copy">
                                            Bezahle direkt über PayPal oder nutze Debit- und Kreditkarte.
                                        </p>
                                    </div>

                                    <x-filament::badge color="primary">
                                        PayPal
                                    </x-filament::badge>
                                </div>

                                <div class="payment-provider-box">
                                    <div class="payment-provider-label-row">
                                        <p class="payment-provider-label">
                                            Checkout
                                        </p>

                                        <x-filament::badge color="gray">
                                            EUR
                                        </x-filament::badge>
                                    </div>

                                    <div class="payment-provider-inner">
                                        <div id="paypal-button-container"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="checkout-benefits">
                                <div class="checkout-benefit">
                                    <span class="checkout-benefit-icon">✓</span>

                                    <p class="checkout-benefit-text">
                                        Sichere Zahlung über PayPal
                                    </p>
                                </div>

                                <div class="checkout-benefit">
                                    <span class="checkout-benefit-icon">✓</span>

                                    <p class="checkout-benefit-text">
                                        PayPal, Debit- oder Kreditkarte
                                    </p>
                                </div>

                                <div class="checkout-benefit">
                                    <span class="checkout-benefit-icon">✓</span>

                                    <p class="checkout-benefit-text">
                                        Automatische Weiterleitung
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </x-filament::section>

            <div class="free-card">
                <div class="free-card-inner">
                    <div>
                        <p class="free-title">
                            Kostenlos fortfahren
                        </p>

                        <p class="free-copy">
                            Nur verwenden, wenn für diese Zahlung kein Betrag berechnet werden soll.
                        </p>
                    </div>

                    <form action="{{ route('admin.payment.process', [$payment, 'method' => 'free']) }}"
                        id="payment-free" method="post">
                        @csrf

                        <input type="hidden" name="method" value="free">

                        <x-filament::button type="submit" size="lg" color="gray">
                            Kostenlos
                        </x-filament::button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://www.paypal.com/sdk/js?client-id={{ Settings::paypalClientId() }}&currency=EUR"></script>

        <script>
            paypal.Buttons({
                style: {
                    layout: 'vertical',
                    color: 'gold',
                    shape: 'rect',
                    label: 'paypal',
                    height: 45,
                },

                createOrder: function(data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: "{{ $formattedAmount }}"
                            }
                        }]
                    });
                },

                onApprove: function(data, actions) {
                    return actions.order.capture().then(function(details) {
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
