<x-filament-panels::page>
    @php
        $payment = $this->getRecord();
        $paypalClientId = $this->getPaypalClientId();
    @endphp

    <div class="mx-auto w-full max-w-xl">
        <div class="rounded-xl border p-5 shadow-sm">
            <form action="{{ route('admin.payment.process', $payment) }}" id="paypal-payment-form" method="post">
                @csrf
                <input type="hidden" name="payment_id" id="payment_id">
                <input type="hidden" name="order_id" value="{{ $payment->id }}">
                <div id="paypal-button-container"></div>
            </form>
        </div>

        <div class="mt-4 rounded-xl border p-5 shadow-sm">
            <form action="{{ route('admin.payment.process', [$payment, 'method' => 'free']) }}" method="post">
                @csrf
                <input type="hidden" name="method" value="free">
                <button class="fi-btn fi-btn-size-lg fi-btn-color-primary w-full" type="submit">
                    Kostenlos
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=EUR"></script>
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
                        var form = document.getElementById('paypal-payment-form');

                        if (details.status === 'COMPLETED') {
                            document.getElementById('payment_id').value = details.id;
                            form.submit();
                        }
                    });
                },
            }).render('#paypal-button-container');
        </script>
    @endpush
</x-filament-panels::page>
