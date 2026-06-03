<x-front_app>
    <main class="profile-content panel container">
        <form action="{{ route('payment.process') }}" id="payment" method="post">
            @csrf
            <input type="hidden" name="payment_id" id="payment_id">
            <input type="hidden" name="stripe_token" id="stripeToken">
            <input type="hidden" name="order_id" value="{{ $order->id }}">


            <span id="profile-content-scroll-point"></span>
            <ul class="breadcrumb-userprofile">
                <li><a href="{{ route('shop') }}">Shop</a></li>
                <li><a href="{{ route('cart') }}">Warenkorb</a></li>
                <li><a href="{{ route('checkout') }}">Checkout</a></li>

            </ul>
            <div class="profile-content__the-content">
                <h3 class="small mt-4">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody" style="z-index:999">
                            Sicher mit PayPal(SEPA, giropay, SOFORT) oder ganz bequem mit der Kreditkarte zahlen.
                            Akzeptierte Kreditkartenanbieter: Visa, MasterCard, American Express,
                            Discover.
                        </div>
                    </details>Zahlungsmethode
                </h3>

                <p class="small mt-2" id="payment_method">
                   {{-- <input value="stripe" name="payment_type" onclick="paymentMethod('stripe')" id="payment_option1"
                        type="radio" class="options radio" />
                    <label for="payment_option1">
                        Mit Kreditkarte zahlen
                    </label> <br>--}}

                    <input value="paypal" name="payment_type" onclick="paymentMethod('paypal')" id="payment_option2"
                        type="radio" class="options radio" />
                    <label for="payment_option2" class="mt-3">
                        Mit PayPal, SOFORT, SEPA Lastschrift oder giropay zahlen
                    </label>

                </p>

                @if ($order->payment_gateway !== 'pre_payment')
                    <p class="small mt-2" id="payment_method">
                        <input value="pre_payment" name="payment_type" onclick="paymentMethod('pre_payment')"
                            id="payment_option3" type="radio" class="options radio" />
                        <label for="payment_option3">
                            Vorkasse (Beta)
                        </label> <br>
                    </p>
                @endif

                <div class="border rounded p-3 mb-3" id="stripe" style="display:none">
                    <div class="">
                        <div class="border rounded px-3 mt-3">
                            <div id="card-element" class="mt-3 mb-3">
                                <!-- Elements will create input elements here -->
                            </div>
                            <!-- We'll put the error messages in this element -->
                            <div id="card-errors" role="alert"></div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-4">
                        <div class="form-group">
                            <button class="btn btn-primary" id="complete-order" type="submit">Bezahlen</button>
                        </div>
                    </div>
                </div>



                <div id="paypal-button-container" style="display: none;z-index:1;"></div>


            </div>
            <div id="pre_payment_container" style="display: none">
                <button id="prePaymentBtn" class="btn btn-primary">Bezahlen</button>
            </div>
            </div>
        </form>
    </main>

    @push('scripts')
        <script src="https://www.paypal.com/sdk/js?client-id={{ setting('site.client_id') }}&currency=EUR"></script>
        <script>
            paypal.Buttons({

                createOrder: function(data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                currency_code: 'EUR',
                                value: "{{ Shop::round_num($order->total - $order->discount) }}"
                            }
                        }]
                    });
                },
                onApprove: function(data, actions) {
                    // This function captures the funds from the transaction.
                    return actions.order.capture().then(function(details) {
                        // This function shows a transaction success message to your buyer.


                        var form = document.getElementById('payment');

                        console.log(details.id);
                        var paymentId = document.getElementById('payment_id').value = details.id;
                        console.log(paymentId);
                        form.submit();


                    });
                },

            }).render('#paypal-button-container');
        </script>

        <script src="https://js.stripe.com/v3/"></script>
        <script>
            (function() {
                // Create a Stripe client.
                var stripe = Stripe("{{ setting('site.publish_key') }}");
                // Create an instance of Elements.
                var elements = stripe.elements();
                // Custom styling can be passed to options when creating an Element.
                // (Note that this demo uses a wider set of styles than the guide below.)
                var style = {
                    base: {
                        color: '#32325d',
                        margin: '10px',
                        fontFamily: '"Montserrat", Helvetica, sans-serif',
                        fontSmoothing: 'antialiased',
                        fontSize: '16px',
                        '::placeholder': {
                            color: '#aab7c4'
                        }

                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a'
                    }
                };
                // Create an instance of the card Element.
                var card = elements.create('card', {
                    style: style,
                    hidePostalCode: true
                });
                // Add an instance of the card Element into the `card-element` <div>.
                card.mount('#card-element');
                // Handle real-time validation errors from the card Element.
                card.addEventListener('change', function(event) {
                    var displayError = document.getElementById('card-errors');
                    if (event.error) {
                        displayError.textContent = event.error.message;
                    } else {
                        displayError.textContent = '';
                    }
                });
                // Handle form submission.
                var form = document.getElementById('payment');
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    // Disable the submit button to prevent repeated clicks
                    document.getElementById('complete-order').disabled = true;
                    stripe.createToken(card).then(function(result) {
                        if (result.error) {
                            // Inform the user if there was an error.
                            var errorElement = document.getElementById('card-errors');
                            errorElement.textContent = result.error.message;
                            // Enable the submit button
                            document.getElementById('complete-order').disabled = false;
                        } else {
                            // Send the token to your server.
                            stripeTokenHandler(result.token);
                            console.log('hello');
                        }
                    });
                });
                // Submit the form with the token ID.
                function stripeTokenHandler(token) {
                    // Insert the token ID into the form so it gets submitted to the server
                    document.getElementById('payment_id').value = token.id
                    console.log(token.id)
                    // Submit the form
                    form.submit();
                }
            })();
        </script>
        <script>
            function paymentMethod(e) {
                if (e == 'stripe') {
                    document.getElementById('stripe').style.display = "block";
                    document.getElementById('paypal-button-container').style.display = "none";
                    document.getElementById('pre_payment_container').style.display = "none";
                    document.getElementById('complete-order').style.display = "block";
                } else if (e == 'paypal') {
                    document.getElementById('stripe').style.display = "none";
                    document.getElementById('paypal-button-container').style.display = "block";
                    document.getElementById('pre_payment_container').style.display = "none";
                    document.getElementById('complete-order').style.display = "none";
                } else if (e == 'pre_payment') {
                    document.getElementById('stripe').style.display = "none";
                    document.getElementById('paypal-button-container').style.display = "none";
                    document.getElementById('pre_payment_container').style.display = "block";
                    document.getElementById('complete-order').disabled = true;
                    document.getElementById('complete-order').style.display = "none";
                }
            }
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const prePaymentBtn = document.getElementById('prePaymentBtn');
                const form = document.getElementById('payment');
                prePaymentBtn.addEventListener('click', function(event) {
                    event.preventDefault();
                    form.submit();
                });
            });
        </script>
    @endpush

</x-front_app>
