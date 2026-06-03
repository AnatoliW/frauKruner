<x-front_app>
    @section('title', 'Kasse')
    @section('description', 'FrauKruner – Kasse')
    <main>
        <!-- error message
    <section class="container-fluid d-flex justify-content-center align-items-center text-center" style="color:#fff;font-size:1rem;min-height:3rem;padding: 10px;background-color:red;">
        <span>Die PayPal-Zahlung ist vorübergehend aufgrund technischer Schwierigkeiten nicht verfügbar.<br> Wir arbeiten mit Hochdruck daran, sie so schnell wie möglich wieder für Sie bereitzustellen.<br> Bitte entschuldigen Sie die Unannehmlichkeiten!</span>
    </section> -->
        <section class="container-cart mt-5 mb-3">

            <h1 class="small">Kasse</h1>
            <hr>
        </section>
        <section class="container-cart">
            @if (Cart::getTotalQuantity() > 0)
                @foreach (Cart::getContent() as $product)
                    <div class="card-fields-shopping-cart">
                        <div class="card-item">
                            <div class="card-item__main-info">
                                <div class="col-prod-image">
                                    <img data-src="{{ $product->model->thumbnail }}" class="lazy img-fluid"
                                        alt="">
                                </div>
                                <div class="col-prod-text">
                                    <div class="col-prod-text__prod-summary">
                                        <h6 class="text-primary">
                                            {{ $product->model->category ? $product->model->category->name : '' }}
                                        </h6>
                                        <p>{{ Str::limit($product->model->details, 60) }}</p>
                                    </div>
                                </div>
                                <div class="col-prod-price">
                                    <a href="{{ url('/cart-destroy/' . $product->id) }}"><img
                                            data-src="{{ asset('assets/img/icons/warenkorb-close.svg') }}"
                                            alt="Produtk löschen" class="lazy col-prod-price__erase"></a>
                                </div>
                            </div>
                            <div class="col-prod-addons">
                                <div class="col-prod-addons__placeholder"></div>
                                <!-- List of Addons to the Product-->
                                <div class="col-prod-addons__addons-list">
                                    <!-- Single Addon-->
                                    @if (isset($product->attributes['Tragedauer']))
                                        @foreach ($product->attributes['Tragedauer'] as $key => $attribute)
                                            <div class="col-prod-addons__addons-list__single-addon">
                                                <div class="col-prod-addons__addons-list__single-addon__details">
                                                    <h6 class="text-primary">
                                                        <font style="vertical-align: inherit;">Tragedauer
                                                        </font>
                                                    </h6>
                                                    <p>
                                                        <font style="vertical-align: inherit;">
                                                            {{ explode('-', $attribute)[0] }}
                                                        </font>
                                                    </p>
                                                </div>
                                                <div class="col-prod-addons__addons-list__single-addon__pricing">
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    @if (isset($product->attributes['veredelungen']))
                                        @foreach ($product->attributes['veredelungen'] as $key => $attribute)
                                            <div class="col-prod-addons__addons-list__single-addon">
                                                <div class="col-prod-addons__addons-list__single-addon__details">
                                                    <h6 class="text-primary">
                                                        <font style="vertical-align: inherit;">veredelungen
                                                        </font>
                                                    </h6>
                                                    <p>
                                                        <font style="vertical-align: inherit;">
                                                            {{ explode('-', $attribute)[0] }}
                                                        </font>
                                                    </p>
                                                </div>
                                                <div class="col-prod-addons__addons-list__single-addon__pricing">
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    @if (isset($product->attributes['Zusatzoptionen']))
                                        @foreach ($product->attributes['Zusatzoptionen'] as $key => $attribute)
                                            <div class="col-prod-addons__addons-list__single-addon">
                                                <div class="col-prod-addons__addons-list__single-addon__details">
                                                    <h6 class="text-primary">
                                                        <font style="vertical-align: inherit;">Zusatzoptionen
                                                        </font>
                                                    </h6>
                                                    <p>
                                                        <font style="vertical-align: inherit;">
                                                            {{ explode('-', $attribute)[0] }}
                                                        </font>
                                                    </p>
                                                </div>
                                                <div class="col-prod-addons__addons-list__single-addon__pricing">
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            @php
                                $wearingTimeValue = data_get($product->attributes, 'Tragedauer.0');
                                $wearingTimeName = $wearingTimeValue ? explode('-', $wearingTimeValue)[0] : null;
                                $time = $wearingTimeName ? (App\WearingTime::where('name', $wearingTimeName)->value('days') ?? 1) : 1;
                                setlocale(LC_TIME, 'German');

                            @endphp

                            <!-- <div class="delivery-date-container">
                                <h3 class="small">Voraussichtlicher Liefertermin</h3>
                                <p>{{ Carbon\Carbon::now()->locale('de')->addDay($time + 2)->translatedFormat('D. d. M.') }} -
                                    {{ Carbon\Carbon::now()->locale('de')->addDay($time + 4)->translatedFormat('D. d. M.') }}</p>

                            </div> -->
                        </div>
                    </div>
                @endforeach
            @endif

            @php
                $data = auth()->user()->address ?? new App\Models\Address();
            @endphp
            <h3 class="small mt-4">Nutzerinformation</h3>
            <form id="payment" action="{{ route('checkout.store') }}" method="POST">
                @csrf
                <div class="row mt-4">
                    <h5 class="small d-flex">
                        <details data-popover="up">
                            <summary>?</summary>
                            <div class="popoverBody">
                                Gebe hier deine Lieferadresse ein. Die Lieferung ist nur innerhalb Deutschlands möglich.
                                Weitere Infos findest du in unseren <a
                                    href="/page/agb-widerrufsbelehrung-muster-widerruf" target="_blank">Allgemeinen
                                    Geschäftsbedingungen für die Verkäufer.</a>
                            </div>
                        </details>Adressdaten
                    </h5>


                    <div class="col-12 col-md-6 mt-4">
                        <input type="text" id="first_name" name="first_name"
                            value="{{ Auth::check() ? auth()->user()->name : old('first_name') }}"
                            placeholder="Vorname" required>
                        <span class="text-danger" id="first_name_error"></span>
                        @error('first_name')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6 mt-4">
                        <input type="text" id="last_name" name="last_name"
                            value="{{ auth::check() ? auth()->user()->last_name : old('last_name') }}"
                            placeholder="Nachname">
                        <span class="text-danger" id="last_name_error"></span>
                        @error('last_name')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="col-12 col-md-12 mt-4">
                        <input type="email" id="email" name="email"
                            value="{{ auth::check() ? auth()->user()->email : old('email') }}" placeholder="E-Mail">
                        <span class="text-danger" id="email_error"></span>
                        @error('email')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <input type="text" id="additional" name="additional"
                            value="{{ old('additional') ?? $data->additional }}" placeholder="Zusatz">
                        <span class="text-danger" id="additional_error"></span>
                        @error('additional')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="col-8 mt-4">
                        <input type="text" id="street" name="street" placeholder="Straße"
                            value="{{ old('street') ?? $data->street }}">
                        <span class="text-danger" id="street_error"></span>
                        @error('street')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="col-4 mt-4">
                        <input type="text" id="house_no" name="house_no"
                            value="{{ old('house_no') ?? $data->house_no }}" placeholder="Nr">
                        <span class="text-danger" id="house_no_error"></span>
                        @error('house_no')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="col-4 mt-4">
                        <input type="number" id="plz" value="{{ old('zip') ?? $data->zip }}" name="zip"
                            placeholder="PLZ">
                        <span class="text-danger" id="plz_error"></span>
                        @error('zip')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="col-8 mt-4">
                        <input type="text" id="bundesland" name="federal_state"
                            value="{{ old('federal_state') ?? $data->federal_state }}" placeholder="Ort">
                        <span class="text-danger" id="bundesland_error"></span>
                        @error('federal_state')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                    {{--
                    <div class="col-12 mt-4">
                        <input type="text" id="postfach" name="po_box" placeholder="Postfach"
                            value="{{ old('po_box') ?? $data->po_box }}">
                        <span class="text-danger" id="postfach_error"></span>
                        @error('po_box')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                    --}}
                    <div class="col-12 col-sm-6 position-relative">
                        <input type="checkbox" id="datenschutz" name="datenschutz" required>
                        <label for="datenschutz" class="visible">Hiermit bestätige ich, dass ich Ihre <a href="/page/datenschutz" target="_blank">Datenschutzerklärung</a> und <a href="/page/agb-widerrufsbelehrung-muster-widerruf" target="_blank">Nutzungsbedingungen</a> zur Kenntnis genommen habe und diese akzeptiere.</label>
                        <!-- Display error message if there's a validation error for 'datenschutz' -->
                        @error('datenschutz')
                        <span class="text-danger">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>

                    <div class="col-12 col-sm-6 position-relative pt-4">
                        <input type="checkbox" id="verbindlicherKauf" name="verbindlicherKauf" required>
                        <label for="verbindlicherKauf" class="visible">Dieser Kauf ist verbindlich und kann nicht storniert werden.</label>
                        <!-- Add error handling for 'verbindlicherKauf' -->
                        @error('verbindlicherKauf')
                        <span class="text-danger">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                    @if(env('CAPTCHA')==true)
                    <div class="form-group mt-2">
                        <div class="cf-turnstile" data-sitekey="{{ env('TURNSTILE_SITE_KEY') }}"></div>
                        @error('cf-turnstile-response')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    @endif


                </div>
                <div class="message-to-the-vendor">
                    <h5 class="small mt-4 mb-4 d-flex">
                        <details data-popover="up">
                            <summary>?</summary>
                            <div class="popoverBody">
                                Du möchtest es genauer? Dann ist hier dein Platz für Anregungen, Foto-und Videodetails
                                oder deine Vorlieben. Dies ist ein Hinweis aber kein Muss. Datentausch verstößt gegen
                                die <a href="/page/agb-widerrufsbelehrung-muster-widerruf"
                                    target="_blank" title="Richtlinien">Richtlinien</a>.
                            </div>
                        </details>Mitteilung an den Shop
                    </h5>
                    <textarea id="mitteilungAnDenVendor" name="message" style="height: 100px;">{{ old('message') }}</textarea>
                </div>



        </section>
        <section class="bg-lightgrey mt-5">
            <div class="container-cart-summary">
                @if (session()->has('discount'))
                    <div class="delivery-cost-cart">
                        <span class="delivery-cost-cart__heading">Rabatt <a href="{{ route('coupon.destroy') }}"> (
                                Löschen )</a></span>
                        <span class="delivery-cost-cart__price">{{ session()->get('discount') }} €</span>
                    </div>
                @endif
                @php
                    $total = Cart::getSubTotal() - session()->get('discount');
                @endphp
                <div class="fullsumm-cost-cart">
                    <span class="fullsumm-cost-cart__heading">Gesamtsumme</span>

                    <span class="fullsumm-cost-cart__price">{{ Shop::price($total) }}</span>
                </div>

                <div class="buttons-to-checkout-kasse">
                    <button type="submit" id="complete-order" class="btn btn-primary-to-sec"
                        style="display: none;">Jetzt kaufen</button>
                </div>

                <input type="hidden" name="payment_id" id="payment_id" value="">

                <button class="btn btn-primary" type="submit">zur Zahlung</button>
                </form>

            </div>
        </section>



    </main>

</x-front_app>
