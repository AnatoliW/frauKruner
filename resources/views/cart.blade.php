<x-front_app>
    @section('title','FrauKruner – Warenkorb')
    @section('description','Werenkorb der Träume. Waren werden neutral verpackt sowie vakuumverpackt versendet.')
    <main>
<!--Error message
    <section class="container-fluid d-flex justify-content-center align-items-center text-center" style="color:#fff;font-size:1rem;min-height:3rem;padding: 10px; background-color:red;">
        <span>Die PayPal-Zahlung ist vorübergehend aufgrund technischer Schwierigkeiten nicht verfügbar.<br> Wir arbeiten mit Hochdruck daran, sie so schnell wie möglich wieder für Sie bereitzustellen.<br> Bitte entschuldigen Sie die Unannehmlichkeiten!</span>
    </section>-->
        <section class="container-cart mt-3 mb-3">
            <h1 class="small">Warenkorb</h1>
            <p>Deine Ware wird vakuumiert und diskret verschickt.
            <br><br>
            Die Absenderadresse ist:<br>
            Kruner<br>
            Schönhauser Allee 163<br>
            10435 Berlin</p><br>
            <p>Mit der Absendung des Warenkorbs bestätigst du, dass du volljährig bist.</p>
            <p>Fotos und Videos müssen spätestens nach 4 Wochen ab dem Versand gesichert werden. Nach Ablauf dieser Frist werden die Dateien gelöscht.</p>
            <hr>
        </section>



        @if (Cart::getTotalQuantity() > 0)
            <section class="container-cart">
                @foreach (Cart::getContent() as $product)
                    <div class="card-fields-shopping-cart">

                        <div class="card-item">
                            <div class="card-item__main-info">
                            <div class="col-prod-image">
                                @if(!empty($product->model->thumbnail))
                                    <img data-src="{{ $product->model->thumbnail }}" class="lazy img-fluid" alt="">
                                @else
                                    <img data-src="{{ asset('assets/img/user.png') }}" class="lazy img-fluid" alt="">
                                @endif
                            </div>

                                <div class="col-prod-text">
                                    <div class="col-prod-text__prod-summary">
                                        <h6 class="text-primary">
                                            {{ $product->model && $product->model->category ? $product->model->category->name : '' }}</h6>
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
                                                            {{ explode('-', $attribute)[0] }} </font>
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
                                                            {{ explode('-', $attribute)[0] }} </font>
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
                                                            {{ explode('-', $attribute)[0] }} </font>
                                                    </p>
                                                </div>
                                                <div class="col-prod-addons__addons-list__single-addon__pricing">
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif



                                    <!-- Single Addon-->


                                </div>

                                <!-- List of Addons to the Product-->
                            </div>


                            @php
                        $time = $product->attributes->Tragedauer ? App\WearingTime::where('name', explode('-', $product->attributes->Tragedauer[0]))->first('days')->days : 1;
                        setlocale(LC_TIME, 'German');

                    @endphp

                    <!-- <div class="delivery-date-container">
                        <h3 class="small">Voraussichtlicher Liefertermin</h3>
                        <p>{{ Carbon\Carbon::now()->addDay($time + 2)->formatLocalized('%a. %d.  %b.') }} -
                            {{ Carbon\Carbon::now()->addDay($time + 4)->formatLocalized('%a. %d. %b ') }}</p>

                    </div> -->
                    </div>
                        </div>

                @endforeach

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

                    <div class="buttons-to-checkout">
                        <a href="{{ route('shop') }}" class="btn btn-primary-outline-white-to-sec">weiter Einkaufen</a>
                        <a href="{{ route('checkout') }}" class="btn btn-primary-to-sec">Zur Kasse</a>
                    </div>
                    @if (!session()->has('discount'))
                        <div class="col-md-4">
                            <form action="{{ route('coupon') }}" method="post">
                                @csrf
                                <input type="text" id="coupon_code" name="coupon_code" placeholder="Gutscheincode">
                                <button type="submit" class="btn btn-secondary">Gutschein hinzufügen
                                    (optional)
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </section>
        @else
            <div class="container-cart-summary">
                <a href="{{ route('shop') }}" class="btn btn-primary-outline-white-to-sec">weiter Einkaufen</a>
            </div>
        @endif

    </main>

</x-front_app>
