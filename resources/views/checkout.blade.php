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

                // Das Geburtsdatum wird bei der Verifizierung dauerhaft gespeichert.
                // Liegt es vor, die Auswahl vorbelegen, damit es niemand erneut eintippen muss.
                $storedDob = null;
                $dobRaw = auth()->user()?->verification?->date_of_birth;

                if (filled($dobRaw)) {
                    try {
                        $storedDob = \Carbon\Carbon::parse($dobRaw);
                    } catch (\Throwable $e) {
                        $storedDob = null;
                    }
                }

                // Die Selects werden nicht abgeschickt (reine Altersprüfung im Browser),
                // deshalb gibt es hier kein old() – nur den gespeicherten Wert.
                $birthDayValue = $storedDob?->day;
                $birthMonthValue = $storedDob?->month;
                $birthYearValue = $storedDob?->year;
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
                    <h5 class="small mt-5 d-flex">
                        <details data-popover="up">
                            <summary>?</summary>
                            <div class="popoverBody">
                                Der Versanddienstleister kann bei der Zustellung eine Altersprüfung wünschen.
                            </div>
                        </details>Geburtsdatum
                    </h5>

                    <div class="col-sm-4 mt-4 mt-sm-0">
                        <label for="birthDay" class="visually-hidden">Tag</label>
                        <select id="birthDay" autocomplete="bday-day">
                            <option value="">Tag</option>
                            @for ($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}" @selected((int) $birthDayValue === $d)>
                                    {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-sm-4 mt-4 mt-sm-0">
                        <label for="birthMonth" class="visually-hidden">Monat</label>
                        <select id="birthMonth" autocomplete="bday-month">
                            <option value="">Monat</option>
                            @foreach (['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'] as $i => $monthName)
                                <option value="{{ $i + 1 }}" @selected((int) $birthMonthValue === $i + 1)>
                                    {{ $monthName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-4 mt-4 mt-sm-0">
                        <label for="birthYear" class="visually-hidden">Jahr</label>
                        <select id="birthYear" autocomplete="bday-year">
                            <option value="">Jahr</option>
                            @for ($y = (int) date('Y'); $y >= (int) date('Y') - 100; $y--)
                                <option value="{{ $y }}" @selected((int) $birthYearValue === $y)>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <p class="text-muted small">Der Versanddienstleister kann bei der Zustellung eine Altersprüfung wünschen.</p>
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
                    <textarea id="mitteilungAnDenVendor" name="message" maxlength="400"
                        style="height: 100px;">{{ old('message') }}</textarea>
                    <span class="small text-muted" id="mitteilungAnDenVendorCounter">0/400 Zeichen</span>
                    @error('message')
                        <span class="text-danger">
                            {{ $message }}
                        </span>
                    @enderror
                </div>



        </section>
        <section class="bg-lightgrey mt-5">
            <div class="container-cart-summary">
                @if (Shop::discount() > 0)
                    <div class="delivery-cost-cart">
                        <span class="delivery-cost-cart__heading">Rabatt
                            <x-coupon-remove-button /></span>
                        <span class="delivery-cost-cart__price">- {{ Shop::price(Shop::discount()) }}</span>
                    </div>
                @endif
                @php
                    $total = Cart::getTotal();
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

                <p class="text-danger mt-3 mb-0 d-none" id="date_of_birth_error" role="alert"></p>
                </form>

            </div>
        </section>



    </main>

    <script>
        (function() {
            var box = document.getElementById('mitteilungAnDenVendor');
            var counter = document.getElementById('mitteilungAnDenVendorCounter');
            if (!box || !counter) return;

            var max = parseInt(box.getAttribute('maxlength'), 10) || 400;

            function update() {
                // [...str] zaehlt wie mb_strlen in PHP, damit Zaehler und
                // Server-Validierung (max:400) dasselbe Ergebnis liefern.
                counter.textContent = [...box.value].length + '/' + max + ' Zeichen';
            }

            box.addEventListener('input', update);
            update();
        })();
    </script>

    <script>
        (function() {
            var form = document.getElementById('payment');
            if (!form) return;

            var daySel = document.getElementById('birthDay');
            var monthSel = document.getElementById('birthMonth');
            var yearSel = document.getElementById('birthYear');
            var errorEl = document.getElementById('date_of_birth_error');

            function calcAge(day, month, year) {
                var birth = new Date(year, month - 1, day);
                // Ungültige Daten wie 31.02. abfangen
                if (birth.getFullYear() !== year || birth.getMonth() !== month - 1 || birth.getDate() !== day) return null;
                var now = new Date();
                if (birth > now) return null;
                var age = now.getFullYear() - birth.getFullYear();
                var m = now.getMonth() - birth.getMonth();
                if (m < 0 || (m === 0 && now.getDate() < birth.getDate())) age--;
                return age;
            }

            function showError(msg) {
                if (!errorEl) return;
                errorEl.textContent = msg;
                errorEl.classList.remove('d-none');
                errorEl.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            function clearError() {
                if (errorEl) errorEl.classList.add('d-none');
            }

            [daySel, monthSel, yearSel].forEach(function(sel) {
                if (sel) sel.addEventListener('change', clearError);
            });

            form.addEventListener('submit', function(e) {
                var day = parseInt(daySel && daySel.value, 10);
                var month = parseInt(monthSel && monthSel.value, 10);
                var year = parseInt(yearSel && yearSel.value, 10);

                if (isNaN(day) || isNaN(month) || isNaN(year)) {
                    e.preventDefault();
                    showError('Bitte gib dein vollständiges Geburtsdatum an.');
                    return;
                }

                var age = calcAge(day, month, year);
                if (age === null) {
                    e.preventDefault();
                    showError('Dieses Datum gibt es leider nicht. Bitte prüfe deine Eingabe.');
                    return;
                }

                if (age < 18) {
                    e.preventDefault();
                    showError('Sie müssen mindestens 18 Jahre alt sein, um bei Frau Kruner zu shoppen.');
                    return;
                }

                clearError();
            });
        })();
    </script>

</x-front_app>
