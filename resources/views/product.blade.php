<x-front_app>

    @if ($product->status== 0)
    @section('head')
       <meta name="robots" content="noindex, nofollow">
    @endsection
    @endif
@section('title', $product->name . ' - für dich getragen online kaufen auf fraukruner.de')
@section('description', $product->description)

    @push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/choice.min.css') }}">
        <style>

            .rating-disabled .rating-input,
            .rating-disabled .rating-stars {

                margin-left: -8px !important;
            }

            .choices__inner{
            border: none !important;
            padding: 0px !important;
            background-color: transparent !important;
        }
        .choices__list--multiple .choices__item{
            background-color: #122253 !important;
            border:none;
        }
        .choices[data-type*=select-multiple] .choices__button, .choices[data-type*=text] .choices__button{
            border-left: 1px solid #fff !important;
        }
        .choices__input{
            background-color: transparent !important;
        }
        .choices__list--dropdown .choices__item--selectable.is-highlighted{
            background-color: transparent !important;
        }

        .rating-disabled .rating-input, .rating-disabled .rating-stars {
            cursor: pointer!important;
            }
        </style>

    @endpush

    <aside class="container-xxl" oncontextmenu="return false">
        <ul class="breadcrumb-shop d-md-flex d-none">
            <li><a href="{{ route('shop') }}">Shop</a></li>
            <li><a
                    href="{{ route('shop', ['category' => @$product->category->name]) }}">{{ @$product->category->name }}</a>
            </li>
            <li><a href="#">{{ $product->name }}</a></li>
        </ul>
    </aside>
    <main class=" position-relative container-xxl {{$product->status ==false ? 'paused-profile-or-product' :''}}"  oncontextmenu="return false">
        <!-- Main Section-->

        <!-- class for pause: paused-profile-or-product -->
        <section class="product-single-main-information">


        <div class="position-relative" >
            <span class="profil-pausiert-badge badge">Pausiert</span>

                <div class="product-image-slider">
               
               <div class="product-image-slider__slide">
                   <img data-flickity-lazyload="{{ media_url($product->image) }}"
                       class="product-image-slider__slide__image" alt="{{ $product->name }} Produktbild">
               </div>
               @foreach ($product->images as $image)
                   @if ($image->nsfw == 0)
                       <div class="product-image-slider__slide">

                           <img data-flickity-lazyload="{{ media_url($image->image) }}"
                               class="product-image-slider__slide__image" alt="{{ $product->name }} Produktbild">
                       </div>
                   @else
                       <div class="product-image-slider__slide">

                          @if (Auth()->user())
                          <a href="/login" class="{{Auth()->user()->status ? '' :'eighteenPlus'}}" target="_blank">
                               <img data-flickity-lazyload="{{ media_url($image->image) }}"
                                   class="product-image-slider__slide__image" alt="{{ $product->name }} Produktbild">
                           </a>
                           @else
                           <a href="/login" class="eighteenPlus" target="_blank">
                               <img data-flickity-lazyload="{{ media_url($image->image) }}"
                                   class="product-image-slider__slide__image" alt="{{ $product->name }} Produktbild">
                           </a>
                          @endif
                       </div>
                   @endif
               @endforeach
           </div>

        </div>


            <div class="product-description">
                @auth
                    @if (auth()->user()->role_id == 2)
                        @if (isset(auth()->user()->favorites) &&
                                auth()->user()->favorites->contains('favoritable_id', $product->id))
                            @php
                                $favorite = auth()
                                    ->user()
                                    ->favorites->where('favoritable_id', $product->id)
                                    ->first();

                            @endphp
                            <form class="deleteFavoriteForm" method="POST">
                                @csrf

                                <input type="hidden" name="product" value="{{ $product->id }}">
                                <input type="hidden" name="create" class="deleteFormfavCreate">
                                <input type="hidden" name="favorite_id" value="{{ $favorite->id }}">
                                <input type="hidden" name="model_type" value="{{ get_class($product) }}">

                                <button type="submit" class="heart-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="size-6">
                                        <path
                                            d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
                                    </svg>

                                </button>
                            </form>
                            @else
                                <form action="{{ route('favorite.store', $product->id) }}" method="post" class="favoriteForm">
                                    @csrf
                                    <input type="hidden" name="model_type" value="{{ get_class($product) }}">
                                    <input type="hidden" name="favorite_id" class="favoriteId">
                                    <button type="submit" class="heart-icon favoriteButton">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                        </svg>
                                    </button>
                                </form>
                        @endif
                    @endif
                @else
                <div class=" icon-self">
                <a href="javascript:void()" class="heart-icon d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                    </a>
                </div>

                @endauth
                <p class="text-primary h6">{{ @$product->category->name }}</p>


                <h1>{{ $product->name }}</h1>
                <h2 class="small text-secondary">{{ Str::limit($product->details, 350) }}</h2>
                <span class="price-descr">
                    {{-- <span class="tragefotos">inklusive Tragefotos (nur für registrierte und verifizierte Nutzer)</span> --}}
                    <span class="price" id="totalPrice">{{ Shop::price($product->total_price) }}*</span>
                    <span class="mwst-versand">inkl. Versand</span>
                </span>
                <div class="star-rating-container" data-bs-toggle="modal" data-bs-target="#bewertungModal">
                    <div class="star-rating">
                        <input name="rating" type="number" value="{{ round($product->user->ratings()->avg('rating')) }}"
                            class="rating published_rating" data-size="xs">
                    </div>
                    <p class="mt-0">
                        {{ $product->user->ratings->count() }}
                    </p>

                </div>
                <form action="{{ route('cart.store') }}" method="post">
                    @csrf
                   @if($product->wearing_time)
                    <div class="sorting-list-collapsing-single-product">
                        <button class="sidebar-collapse-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseTragedauer" aria-expanded="true"
                            aria-controls="collapseTragedauer">
                            Tragedauer <span class="arrow">
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                        <div class="collapse collapse__options show " id="collapseTragedauer">
                            <ul>
                                @foreach ($product->wearing_time as $key => $price)

                                    <li>
                                        <input value="{{ $key }}" name="wearing_time"
                                            id="{{ $key }}" type="radio" class="options radio"
                                            data-price="{{ $price }}" /><label
                                            for="{{ $key }}">{{ $key }}
                                            <span>({{ Shop::calculated_total($price,$product) }} €)</span></label>

                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if ($product->finishings)
                        <div class="sorting-list-collapsing-single-product">
                            <button class="sidebar-collapse-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseVeredelung" aria-expanded="false"
                                aria-controls="collapseVeredelung">
                                Wähle deine Veredelung <span class="arrow">
                                    <span></span>
                                    <span></span>
                                </span>
                            </button>

                            <div class="collapse collapse__options " id="collapseVeredelung">
                                <ul>
                                    @foreach ($product->finishings as $key => $price)
                                        <li><input value="{{ $key }}" name="finishings[]"
                                                id="{{ $key }}" type="checkbox" class="options radio"
                                                data-price="{{ $price }}" /><label
                                                for="{{ $key }}">{{ $key }}
                                                <span>({{ Shop::calculated_total($price) }} €)</span></label></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    @if ($product->addition)
                        <div class="sorting-list-collapsing-single-product">

                            <button class="sidebar-collapse-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseZusatzoptionen" aria-expanded="false"
                                aria-controls="collapseZusatzoptionen">
                                Zusatzoptionen

                                @guest
                                    (Nur für registrierte und verifizierte Nutzer)
                                @endguest

                                @if (auth()->check() && !auth()->user()->status)
                                    (Nur für verifizierte Nutzer)
                                @endif
                                <span class="arrow">
                                    <span></span>
                                    <span></span>
                                </span>
                            </button>

                            <div class="collapse collapse__options pb-3" id="collapseZusatzoptionen">
                            <ul>
                                @foreach ($product->addition as $key => $price)
                                    @if (auth()->check() && auth()->user()->status)
                                        @if ($product->category->name == 'Video' && $key == 'Video')
                                            <li><input required value="{{ $key }}" name="additions[]"
                                                    id="{{ $key }}" type="checkbox" class="options radio"
                                                    data-price="{{ $price }}" checked/><label
                                                    for="{{ $key }}">{{ $key }}
                                                    <span>({{ Shop::calculated_total($price) }} €)</span></label></li>
                                        @elseif ($product->category->name == 'Foto' && $key == 'Foto')
                                            <li><input required value="{{ $key }}" name="additions[]"
                                                    id="{{ $key }}" type="checkbox" class="options radio"
                                                    data-price="{{ $price }}" checked/><label
                                                    for="{{ $key }}">{{ $key }}
                                                    <span>({{ Shop::calculated_total($price) }} €)</span></label></li>
                                        @else
                                            <li><input value="{{ $key }}" name="additions[]"
                                                    id="{{ $key }}" type="checkbox" class="options radio"
                                                    data-price="{{ $price }}" /><label
                                                    for="{{ $key }}">{{ $key }}
                                                    <span>({{ Shop::calculated_total($price) }} €)</span></label></li>
                                        @endif
                                    @else
                                        <li><input type="checkbox" disabled readonly /><label
                                                for="{{ $key }}">{{ $key }}
                                                <span>({{ Shop::calculated_total($price) }} €)</span></label></li>
                                    @endif
                                @endforeach
                            </ul>

                                @if (auth()->check() && !auth()->user()->status)
                                   <a href="/buyer/dashboard/data/verification">Jetzt verifizieren</a>
                                @endif

                                @guest
                                    <a href="/login">Jetzt Registrieren</a>
                                @endguest

                            </div>
                        </div>
                    @endif
                    <input type="hidden" class="form-control qty" value="1" min="1" name="quantity">
                    <input type="hidden" name="product_id" value="{{ $product->id }}" />
                    <!-- <input id="price" type="hidden" name="total_price" value="" /> -->
                    <!-- <input id="sub_price" type="hidden" name="sub_total_price" value="" />
                    <input id="vat" type="hidden" name="vat" value="" />
                    <input id="commission" type="hidden" name="commission" value="" /> -->

                    <!-- disable when paused-->
                    @if ($product->category->name == 'Video' || $product->category->name == 'Foto')
                        @if (auth()->check() && auth()->user()->status)
                            <button type="submit" class="btn btn-primary">In den Warenkorb</button>
                        @elseif (auth()->check() && !auth()->user()->status)
                            <a href="/buyer/dashboard/data/verification" target="_blank" class="btn btn-primary">Jetzt verifizieren um zu genießen</a>
                        @else
                            <a href="/login"  target="_blank" class="btn btn-primary">Jetzt registrieren um zu genießen</a>
                        @endif
                    @else
                        <button type="submit" class="btn btn-primary {{$product->status ==false ? 'paused-profile-or-product disabled' :''}}">In den Warenkorb</button>
                    @endif

                </form>
            </div>
            <div class="product-information">

            @if($product->status == false)
                <p class="h3 small">Nicht aktiv</p>
                <p>Hey, das Produkt ist leider gerade nicht aktiv.<br>

            @elseif($product->user?->status==false)
                <p class="h3 small">Urlaub</p>
                <p>Hey, ich bin leider gerade nicht aktiv.<br>
                <a href="/shop" title="zum Shop">Hier</a> kannst du weiter stöbern.</p>
            @else
                <p class="h3 small">Produktinformation</p>
                <p>{{ $product->description }}</p>
             @endif

            </div>

            @if($product->status == false)
            @else
            <div class="product-details">
                <p class="h3 small">Details</p>
                <p>{{ $product->details }}</p>
            </div>
            @endif
            @if ($product->user->ratings->count() > 0)
                <div class="modal fade" id="bewertungModal" tabindex="-1" aria-labelledby="bewertungModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <p class="h5 modal-title" id="bewertungModalLabel">Meine Bewertungen</p>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Schließen"></button>
                            </div>
                            <div class="modal-body">
                                <div class="container-review">
                                    @foreach ($product->user->ratings as $review)
                                        <div class="single-review"
                                            style="  border-bottom: 1px solid #B2B2B2;padding:  0; ">

                                            <div class="star-rating" style="flex-direction:column;align-items:start">

                                                <input name="rating" type="number" value="{{ $review->rating }}"
                                                    class="rating published_rating" data-size="xs">
                                                <b class="pt-1">{{ $review->user->username[0] }}.</b>
                                                <p>{{ $review->review }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Schließen</button>
                            </div>
                        </div>
                    </div>
                </div>

            @endif
        </section>

        <!-- classes added on pause here: paused-profile-or-product paused-->
        <section class="border-top border-bottom mt-11 {{$product->user?->status==false ? 'paused-profile-or-product paused' :''}}">
			<a href="{{ route('user.profile', $product->user->id) }}" target="_blank" class="text-secondary">
			<div class="d-flex align-items-start">
                @if ($product->user?->profile?->profile_img)
				<img data-src="{{ media_url( $product->user?->profile?->profile_img) }}" width="84px" class="lazy mt-4 {{$product->user?->status==false ? ' paused' :''}}" alt="{{ $product->user->username ? $product->user->username : $product->user->name }}">
                @else
				<img data-src="{{ asset('assets/img/user.png') }}" width="84px" class="lazy mt-4" alt="{{ $product->user->username ? $product->user->username : $product->user->name }}">
                @endif

				<div class="p-4">
					<b>Profil von {{ $product->user->username ? $product->user->username : $product->user->name }}</b>
                    <div class="star-rating-container">
                    <div class="star-rating">
                        <input name="rating" type="number" value="{{ round($product->user->ratings()->avg('rating')) }}"
                            class="rating published_rating" data-size="xs">
                    </div>

                </div>
                    @if($product->user->status == false)
                        <p class="paused">Hey, ich bin leider gerade nicht aktiv.</p>
                    @else
                        <div class="mt-2">{!! $product->user?->profile?->description !!}</div>
                    @endif
				</div>
			</div>
			</a>
		</section>
        @php
            $rating = $product->user->ratings->where('user_id', auth()->id());
            $baught = App\Order::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->where('status', 1)
                ->count();
            $avg_rating = round($product->user->ratings()->avg('rating'));
        @endphp
        <section>
            @auth
                @if ($baught >= 1 && $rating->count() < 1)
                    <!-- <div class="col-md-12 mt-4" id="rating">
                        <h4>Ihre Bewertung</h4>
                    </div>
                    <form action="{{ url('/rating', ['product_id' => $product->id]) }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12 mt-3">
                                <input name="rating" type="number" value="1" class="rating product_rating"
                                    min="1" max="5" step=".5" data-size="xs">
                            </div>
                            <div class="col-md-6">
                                <div class="">
                                    <label for="comment">Bewertungstext</label>
                                    <textarea name="comment" style="height:100px" class="form-control @error('comment') is-invalid @enderror"
                                        id="comment" required></textarea>
                                    @error('comment')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-secondary">Senden</button>
                            </div>
                        </div>
                    </form> -->
                @endif
            @endauth


        </section>


        <!-- Main Section-->

        <!-- Simillary Products-->
        <section class="mt-mb-5">

            <div class="aehnliche-produkte">
                <!-- Products from other categorys (max-3)-->
                <h3>Ähnliche Produkte</h3>

                <div class="product-list-shop ">

                    @foreach ($related_products as $data)
                        <x-product-card :product="$data" />
                    @endforeach

                </div>
            </div>

        </section>
        <!-- Simillary Products-->

        <!-- Tags-->
        <section>

            <p class="h3 small">Schlagwörter</p>
            <div class="sorting-list-tags-cloud">

                @if (isset($product->tags) && $product->tags != 'null')
                    @foreach (json_decode($product->tags) as $tag)
                        <a href="{{ route('shop', ['tag' => $tag]) }}">{{ $tag }}
                            {{ $loop->last == 1 ? '' : ',' }}</a>
                    @endforeach
                @else
                    <p>Keine Schlagwörter gefunden</p>
                @endif

            </div>
            @if (setting('finance.vat') <= 0)
                <p class="pt-5 pb-2 mt-5">*Gemäß §19 UStG wird keine Umsatzsteuer berechnet.</p>
            @else
                <p class="pt-5 pb-2 mt-5">*Umsatzsteuer wird gemäß § 25a UStG nicht ausgewiesen.</p>
            @endif
        </section>
        <!-- Tags-->
        <!-- Get seller-->
        <section class="container-xxl bg-primary mt-mb-5">
            <div class="row">
                <div class="col-12 col-sm-7 pl-0-pr-0-xl d-flex  align-items-center justify-content-center">
                    <img data-src="{{ asset('assets/img/front-page/suche-verkaeferinnen.jpg') }}"
                        alt="Du hast Lust auf Visuelles?" class="lazy img-100-width">
                </div>

                <div class="col-12 col-sm-5 d-flex align-items-center">
                    <div class="content-aktion">
                        <p class="h6">NEU</p>
                        <p class="h3">Du hast Lust auf Visuelles?</p>
                        <p>Dann lasse dir jetzt persönliche Fotos und Videos kreieren.</p>

                        <a href="https://fraukruner.de/shop?additions=Foto%2CVideo" class="btn btn-white">Jetzt stöbern</a>
                    </div>
                </div>
            </div>
        </section>
        <!-- Get seller-->
        <!--
        <section class="mt-5">
            <p>GETRAGENE UNTERWÄSCHE BEI FxxK<br>
                Getragene Slips, benutzte Höschen, gebrauchte Wäsche und vieles mehr von echten Studentinnen. Für
                Liebhaber getragener Dessous, Strumpfmode und vielem mehr bieten wir Dir auf studentslip.eu eine große
                Auswahl an getragenen Slips von echten Studentinnen. Auf der Suche nach Deinem ganz persönlichem
                Höhepunkt erfüllen Dir unsere Mädels fast jeden Wunsch – im Mittelpunkt stehst Du und Deine Vorlieben,
                sodass Du voll und ganz auf Deine Kosten kommst. Egal welche Wünsche und Vorstellung Du hast, unsere
                Mädels tragen Deinen Duftslip ganz nach Deinen Wünschen.<br><br>

                Du hast die Wahl, wie viele Tage unsere Mädels Deinen Duftslip tragen sollen und bestimmst die
                Intensität des Aromas. Für die ganz speziellen Wünsche gibt es auch die Möglichkeit, getragene Slips
                zusätzlich veredeln zu lassen. Wenn Du auch schon über eine stattliche Kollektion an Duftwäsche verfügst
                und Dir gerne etwas Besonderes gönnen möchtest, dann sind die Veredelungen genau das richtige für Dich
                und Deine Liebhaber-Nase.<br><br>

                Mit getragenen Höschen kommst Du den Damen sehr nahe und kannst Dir dennoch Deine Distanz wahren. Ihr
                beide erlebt zusammen ein geheimes Spiel, dessen Reiz gerade darin liegt, dass ihr euch nicht kennt.
                Entdecke dein ganz eigenes Duftparadies mit den getragenen Höschen Deiner Lieblingsdamen. Diese genießen
                ihr heimliches Hobby, mit dem Sie Deine intimsten Wünsche und Vorstellungen erfüllen können. Und das
                wiederum erfüllt sie selbst mit Befriedigung.</p>
        </section>-->
    </main>
    @push('scripts')
        <script>





            const total = (price, vat, commission) => {
                const priceField = document.getElementById('price');
                const totalPrice = document.getElementById('totalPrice');
                const vatField = document.getElementById('vat');
                const commissionField = document.getElementById('commission');
                totalPrice.innerText = price + ' €';
                // console.log(commissionField)
            }
            // console.log(vat);
            const calculate = () => {
                var inputCommission="{{ $product->user->commission }}";
                if(inputCommission){

                    var commission = inputCommission;
                }else{
                    var commission = "{{ setting('finance.commission') }}";
                }

            console.log(commission);



                const percentage = "{{ setting('finance.commission_type') }}";



                let vat = "{{ setting('finance.vat') }}";

                let base = parseInt("{{ $product->price() }}");

                let sum = 0;
                $('.options:checked').map((index, el) => {
                    base += parseInt($(el).data('price'));
                })
                sum = sum + base //Add VAT
                if (commission > 0) {
                    commission = parseInt(commission)
                    switch (percentage) {
                        case 'percentage':
                            commission = sum * (commission / 100);
                            break;

                        default:
                            commission = commission;
                    }
                } else {
                    commission = 0;
                }
                if (vat > 0) {
                    vat = parseInt(vat);
                    vat = commission * (vat / 100);
                }
                const sumFinal = sum + commission + vat;
                // console.log(parseFloat(sumFinal).toFixed(2));
                total(parseFloat(sumFinal).toFixed(2), vat, commission);
            }
            $('.options').change(calculate);
            $(document).ready()
        </script>
        <script src="{{ asset('js/custom/star-rating.js') }}" type="text/javascript"></script>
        <script type="text/javascript">
            $("#product_rating").rating({
                showCaption: true
            });
            $(".published_rating").rating({
                showCaption: false,
                readonly: true,
            });
        </script>
        <!-- <script>
            test=101.1;
            console.log(test.toFixed(2));
        </script> -->
    @endpush
</x-front_app>


