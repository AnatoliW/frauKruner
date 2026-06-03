 <x-front_app>
    
     @section('title', 'FrauKruner – Alles was deine Lust begehrt.')
     @section('description',
         'Getragene Unterwäsche, benutzte Slips, duftende Socken und vieles mehr versandkostenfrei kaufen oder kostenfrei verkaufen. Frau Kruner ist 100% echt und immer für dich da.')
         @push('css')
             <style>
                 @media only screen and (min-width: 768px) {
                     .img-1 {
                         height: 550px;
                         object-fit: cover;
                     }
                 }
                 
                 .heart-icon {
                     position: absolute !important;
                     top: 10px;
                     right: 10px;
                     z-index: 3 !important;
                     background: rgba(255, 255, 255, 0.9);
                     border-radius: 50%;
                     width: 35px;
                     height: 35px;
                     display: flex;
                     align-items: center;
                     justify-content: center;
                     border: none;
                     cursor: pointer;
                     transition: all 0.3s ease;
                 }
                 
                 .heart-icon:hover {
                     background: rgba(255, 255, 255, 1);
                     transform: scale(1.1);
                 }
                 
                 .image-overlay {
                     z-index: 1 !important;
                     position: absolute !important;
                     top: 0 !important;
                     left: 0 !important;
                     right: 0 !important;
                     bottom: 0 !important;
                 }
                 
                 .slider-angebote-main-categorys__slide__image-field,
                 .new-product-image-slide {
                     position: relative !important;
                 }
             </style>
         @endpush

         <main>


             <section class="bg-secondary">
                 <div class="main-slider-front">
                     <img src="{{ asset('assets/img/front-page/hoeschen-kruner.png') }}" alt="Slip Bild" class="content-before-slide">
                     <div class="carousel-front-left">

                         <div class="carousel-front-left__cell"
                             data-background-image-url="{{ asset('assets/img/front-page/frau-kruner-main-photo.jpg') }}">
                         </div>
                     </div>


                     <div class="carousel-front-right">


                         <div class="carousel-front-right__cell">
                             <div class="carousel-front-right__content">
                                 <h1 class="h2">Alles was deine <span class="unterline-primary">Lust</span> begehrt.</h1>
                                 <p>Egal ob dezenter Duft, feuchtes Höschen oder der Geruch von Schweiß - hier bekommst du
                                     alles!</p>
                                 <a href="{{ route('shop') }}" class="btn btn-primary">Jetzt shoppen</a>
                             </div>
                         </div>

                     </div>

                 </div>
             </section>


             <section class="container-xxl">
                 <div class="carousel-testimonials-home">

                     <div class="carousel-testimonials-home__slide">
                         <img data-flickity-lazyload="{{ asset('assets/img/icons/100-prozent-echt.svg') }}"
                             class="carousel-testimonials-home__slide__image" alt="100% Echt und Ehrlich">
                         <p>100% echt und ehrlich</p>
                     </div>

                     <div class="carousel-testimonials-home__slide">
                         <img data-flickity-lazyload="{{ asset('assets/img/icons/diskrete-bezahlung.svg') }}"
                             class="carousel-testimonials-home__slide__image" alt="Diskretes überweisen">
                         <p>diskretes überweisen</p>
                     </div>

                     <div class="carousel-testimonials-home__slide">
                         <img data-flickity-lazyload="{{ asset('assets/img/icons/diskrete-verpackung.svg') }}"
                             class="carousel-testimonials-home__slide__image"
                             alt="Unauffällige Verpackung ohne Werbeaufdruck">
                         <p>Unauffällige Verpackung ohne<br> Werbeaufdruck</p>
                     </div>

                     <div class="carousel-testimonials-home__slide">
                         <img data-flickity-lazyload="{{ asset('assets/img/icons/keine-gebuehr.svg') }}"
                             class="carousel-testimonials-home__slide__image"
                             alt="Keine Ameldegebühren oder versteckte Kosten">
                         <p>Keine Ameldegebühren oder<br> versteckte Kosten</p>
                     </div>

                 </div>
             </section>

             <section class="container-xxl front-highline-section">
                 <p>Wenn du gerne an getragener <span class="unterline-secondary">Unterwäsche</span> schnupperst, bist du
                     hier genau richtig. Frau Kruner ist 100% echt und ehrlich und immer für dich da.</p>
                 <a href="/about" class="btn btn-primary-outline">Frau Kruner kennenlernen</a>
             </section>

            @foreach ($categories as $category)
                @if (!is_object($category))
                    @continue
                @endif
                @php
                    $categoryColor = is_object($category) ? ($category->color ?? '#f8e7ec') : '#f8e7ec';
                    $categoryFont = is_object($category) ? ($category->font ?? '#1f1f1f') : '#1f1f1f';
                    $categoryName = is_object($category) ? ($category->name ?? '') : (string) $category;
                    $categoryTitle = is_object($category) ? ($category->title ?? $categoryName) : (string) $category;
                    $categoryDescription = is_object($category) ? ($category->description ?? '') : '';
                    $categoryImage = is_object($category) ? ($category->image ?? null) : null;
                    $categoryProducts = data_get($category, 'products', collect());
                @endphp
                <section style="background-color:{{ $categoryColor }};color:{{ $categoryFont }}">
                     <div class="container-xxl">
                         <img data-src="{{ media_url($categoryImage) }}" class="img-100-width lazy img-1"
                             alt="{{ $categoryTitle }} Bild">
                         <div class="row">
                             <div class="col-12 col-md-5 col-main-page-sections__first">
                                 <h2 class="h4 text-primary">{{ $categoryName }}</h2>
                                 <h3 class="h2">{{ $categoryTitle }}</h3>
                                 <p>{{ $categoryDescription }}</p>
                                 @if ($categoryName !== '')
                                     <a href="{{ route('shop', ['category' => $categoryName]) }}"
                                         class="btn btn-primary">{{ $categoryName }} shoppen</a>
                                 @endif
                             </div>

                             <div class="col-12 col-md-7 col-main-page-sections__second">
                                 <div class="slider-angebote-main-categorys">
                                      @forelse ($categoryProducts as $product)
                                         <div class="slider-angebote-main-categorys__slide">
                                             <div class="slider-angebote-main-categorys__slide__image-field"
                                                 style="position: relative"
                                                 data-background-image-url="{{ media_url($product->image) }}">

                                                 <a href="{{ $product->path() }}" class="image-overlay"></a>

                                                 @auth
                                                     @if (auth()->user()->role_id == 2)
                                                         @if (isset(auth()->user()->favorites) &&
                                                                 auth()->user()->favorites->contains('favoritable_id', $product->id))
                                                             @php
                                                                 $favorite = auth()
                                                                     ->user()
                                                                     ->favorites->where(
                                                                         'favoritable_id',
                                                                         $product->id,
                                                                     )
                                                                     ->first();

                                                             @endphp
                                                             <form class="deleteFavoriteForm" method="POST">
                                                                 @csrf

                                                                 <input type="hidden" name="product"
                                                                     value="{{ $product->id }}">
                                                                 <input type="hidden" name="create"
                                                                     class="deleteFormfavCreate">
                                                                 <input type="hidden" name="favorite_id"
                                                                     value="{{ $favorite->id }}">
                                                                 <input type="hidden" name="model_type"
                                                                     value="{{ get_class($product) }}">

                                                                 <button type="submit" class="heart-icon">
                                                                     <svg xmlns="http://www.w3.org/2000/svg"
                                                                         viewBox="0 0 24 24" fill="currentColor"
                                                                         class="size-6">
                                                                         <path
                                                                             d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.18 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
                                                                     </svg>

                                                                 </button>
                                                             </form>
                                                             @else
                                                                 <form action="{{ route('favorite.store', $product->id) }}"
                                                                     method="post" class="favoriteForm">
                                                                     @csrf
                                                                     <input type="hidden" name="model_type"
                                                                         value="{{ get_class($product) }}">
                                                                     <input type="hidden" name="favorite_id"
                                                                         class="favoriteId">
                                                                     <button type="submit"
                                                                         class="heart-icon">
                                                                         <svg xmlns="http://www.w3.org/2000/svg"
                                                                             fill="none" viewBox="0 0 24 24"
                                                                             stroke-width="1.5" stroke="currentColor"
                                                                             class="size-6">
                                                                             <path stroke-linecap="round"
                                                                                 stroke-linejoin="round"
                                                                                 d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                                                         </svg>
                                                                     </button>
                                                                 </form>
                                                             @endif
                                                         @endif
                                                     @else
                                                         <a href="javascript:void()" class="heart-icon d-flex align-items-center justify-content-center" data-bs-toggle="modal"
                                                             data-bs-target="#loginModal">
                                                             <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                 class="size-6">
                                                                 <path stroke-linecap="round" stroke-linejoin="round"
                                                                     d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                                             </svg>
                                                         </a>
                                                     @endauth

                                             </div>
                                             <a href="{{ $product->path() }}">
                                                 <div class="slider-angebote-main-categorys__slide__content">
                                                     <p class="h6 mb-0">{{ $categoryName }}</p>
                                                     <p>{{ Str::limit($product->name, 20, '...') }}</p>
                                                     <span
                                                         class="slider-angebote-main-categorys__slide__content-price">{{ Shop::price($product->total_price) }}</span>
                                                 </div>
                                             </a>
                                         </div>
                                     @empty
                                         <p class="">Keine Einträge</p>
                                     @endforelse
                                 </div>

                                  @if ($categoryProducts->count() > 1)
                                     <div class="slider-angebote-main-categorys-pfeil-container">
                                         <img data-src="{{ asset('assets/img/icons/icon-pfeil-links.svg') }}"
                                             class="slider-angebote-main-categorys-pfeil-links lazy"
                                             alt="Pfeil nach links" height="26px" width="43px">
                                         <img data-src="{{ asset('assets/img/icons/icon-pfeil-rechts.svg') }}"
                                             class="slider-angebote-main-categorys-pfeil-rechts lazy"
                                             alt="Pfeil nach rechts" height="26px" width="43px">
                                     </div>
                                 @endif
                             </div>
                         </div>
                     </div>
                 </section>
             @endforeach

             <!-- New category on the website-->
             <section class="bg-lightgrey">
                 <div class="container-xxl">
                     <img data-src="{{ media_url(setting('site.new_product_thumbnail')) }}"
                         class="img-100-width lazy" alt="Image new Categorys">
                     <div class="row">
                         <div class="col-12 col-md-5 col-main-page-sections__first">
                             <h4 class="text-primary">Neu</h4>
                             <h2>{{ setting('site.new_product_title') }} </h2>
                             <p>{{ setting('site.new_product_para') }}</p>
                         </div>

                         <div class="col-12 col-md-7 col-main-page-sections__second">
                             <div class="slider-angebote-main-categorys-new">
                                 @php
                                     $productItems = is_iterable($products) ? $products : [];
                                 @endphp
                                 @foreach ($productItems as $product)
                                     @if (!is_object($product))
                                         @continue
                                     @endif
                                     @php
                                         $productCategoryName = data_get($product, 'category.name', '');
                                     @endphp
                                     <div class="slider-angebote-main-categorys__slide">
                                         <div class="new-product-image-slide">
                                             <img class="lazy" data-src="{{ media_url($product->image) }}"
                                                 alt="Produkt {{ $productCategoryName }}" style="position: relative">

                                             <a href="{{ $product->path() }}" class="image-overlay"></a>

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

                                                             <input type="hidden" name="product"
                                                                 value="{{ $product->id }}">
                                                             <input type="hidden" name="create"
                                                                 class="deleteFormfavCreate">
                                                             <input type="hidden" name="favorite_id"
                                                                 value="{{ $favorite->id }}">
                                                             <input type="hidden" name="model_type"
                                                                 value="{{ get_class($product) }}">

                                                             <button type="submit" class="heart-icon">
                                                                 <svg xmlns="http://www.w3.org/2000/svg"
                                                                     viewBox="0 0 24 24" fill="currentColor"
                                                                     class="size-6">
                                                                     <path
                                                                         d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
                                                                 </svg>

                                                             </button>
                                                         </form>
                                                         @else
                                                             <form action="{{ route('favorite.store', $product->id) }}"
                                                                 method="post" class="favoriteForm">
                                                                 @csrf
                                                                 <input type="hidden" name="model_type"
                                                                     value="{{ get_class($product) }}">
                                                                 <input type="hidden" name="favorite_id"
                                                                     class="favoriteId">
                                                                 <button type="submit"
                                                                     class="heart-icon">
                                                                     <svg xmlns="http://www.w3.org/2000/svg"
                                                                         fill="none" viewBox="0 0 24 24"
                                                                         stroke-width="1.5" stroke="currentColor"
                                                                         class="size-6">
                                                                         <path stroke-linecap="round"
                                                                             stroke-linejoin="round"
                                                                             d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                                                     </svg>
                                                                 </button>
                                                             </form>
                                                         @endif
                                                     @endif
                                                 @else
                                                     <a href="javascript:void()" class="heart-icon d-flex align-items-center justify-content-center" data-bs-toggle="modal"
                                                         data-bs-target="#loginModal">
                                                         <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                             class="size-6">
                                                             <path stroke-linecap="round" stroke-linejoin="round"
                                                                 d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                                         </svg>
                                                     </a>
                                                 @endauth

                                         </div>

                                         <a href="{{ $product->path() }}">
                                             <div class="slider-angebote-main-categorys__slide__content">
                                                 <p class="h6 mb-0">{{ $productCategoryName }}</p>
                                                 <p>{{ Str::limit($product->name, $limit = 20, $end = '...') }} </p>
                                                 <span
                                                     class="slider-angebote-main-categorys__slide__content-price">{{ Shop::price($product->total_price) }}</span>
                                             </div>
                                         </a>
                                     </div>
                                 @endforeach




                             </div>

                             <div class="slider-angebote-main-categorys-new-pfeil-container">
                                 <img data-src="{{ asset('assets/img/icons/icon-pfeil-secondary-rechts-links.svg') }}"
                                     class="slider-angebote-main-categorys-new-pfeil-links lazy" alt="Pfeil nach links"
                                     height="26px" width="43px">
                                 <img data-src="{{ asset('assets/img/icons/icon-pfeil-rechts.svg') }}"
                                     class="slider-angebote-main-categorys-new-pfeil-rechts lazy" alt="Pfeil nach rechts"
                                     height="26px" width="43px">
                             </div>
                         </div>

                     </div>

                 </div>
             </section>
             <!-- New category on the website-->
             <!-- Promotion-->
             <section class="container-xxl bg-secondary mb-5">
                 <div class="row">
                     <div class="col-12 col-sm-6 pl-0-pr-0-xl d-flex  align-items-center justify-content-start">
                         <img data-src="{{ asset('assets/img/front-page/promotion.jpg') }}"
                             alt="Du möchtest Bilder und Videos solo?" class="lazy img-100-width">
                     </div>

                     <div class="col-12 col-sm-6 d-flex align-items-center">
                         <div class="content-aktion">
                             <p class="h6">NEU</p>
                             <h3>Du möchtest<br> Bilder solo?</h3>
                             <p>Kein Problem!</p>

                             <a href="https://fraukruner.de/shop?category=foto" class="btn btn-primary">Jetzt stöbern</a>
                         </div>
                     </div>
                 </div>
             </section>
             <!-- Promotion-->

             <!-- Testimonials-->


             {{-- <section class="container-xxl mt-mb-11">
             <h3>Unsere befriedigten Kunden schreiben.</h3>

             <div class="slider-testimonials-main">
                 @foreach ($reviews as $review)
                     <div class="slider-testimonials-main__slide">
                         <h5>{{ substr($review->user->profile ? $review->user->profile->username : $review->user->name, 0, 1) . "." }} zu <a
                                 href="{{ route('user.profile', $review->vendor_id) }}">
                                 @if ($review->vendor)
                                  @if ($review->vendor->profile)
                                      {{ $review->vendor->profile->username }}
                                @elseif ($review->vendor->username)
                                {{ $review->vendor->username  }}
                                @else
                                {{ $review->vendor->last_name }}
                                  @endif

                                 @endif
                                </a>
                         </h5>
                         <p>{{ $review->review }}</p>

                         <a href="{{ route('user.profile', $review->vendor_id) }}">Zum Shop</a>
                     </div>
                 @endforeach

             </div>

         </section>
         --}}
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
                             <p>Dann lasse dir jetzt persönliche Videos kreieren.</p>

                             <a href="https://fraukruner.de/shop?category=video" class="btn btn-white">Jetzt stöbern</a>
                         </div>
                     </div>
                 </div>
             </section>
             <!-- Get seller-->



             <section class="container-xxl">

                 <div class="main-container mb-5">

                     <h2 class="small">Getragenes Höschen und benutzter Schlüpfer – jetzt wird's heikel</h2>

                     <div class="only-so-big">
                         <p>Der Markt ist da und diese Nische spricht Kunden an, die getragene Sachen kaufen. Doch in diesem Fall reden wir von ungewaschenen Kleidungsstücken, die verschickt werden. Ein getragenes Höschen und ein benutzter Schlüpfer sind hier schon fast Alltagsprodukte, die ungewaschen von unterschiedlichen Verkäuferinnen angeboten werden. Diess kann verschiedenste Gründe haben, sei es die persönlichen Vorlieben, als Fetisch oder als reines Sammlerstück. Unser Sortiment umfasst eine breite Palette an Fetischartikeln. Jeder benutzte Schlüpfer ist einzigartig und hat seine eigene Geschichte, was jeden Kauf zu etwas ganz Besonderem macht. Dreckige Schlüpfer werden vor dem Versand nach strengen Hygienestandards behandelt und diskret verpackt, um sicherzustellen, dass sie in einem einwandfreien Zustand ankommen. Klingt nach Doppelmoral? Ist es aber nicht. Die Hygiene, von der wir sprechen, betrifft den Gesundheitszustand unserer Shops und die richtige Herstellung von diesen einzigartigen duftenden Artikeln. Wir arbeiten ausschließlich mit Verkäufern zusammen, die unsere hohen Standards für Qualität und Ethik erfüllen, damit du deinen getragenen Schlüpfer genießen kannst.  </p>
                         <h3 class="small">Getragene Höschen aus Berlin</h3>
                         <p>
                             Das 2021 in Deutschland gegründete Label FrauKruner mit Sitz in Berlin bietet ein verführerisches Erlebnis, wann immer du möchtest. Wir handeln nach Deutschem Gesetz und versprechen dir Anonymität und Diskretion auf unserem Erotik Marktplatz. Einfach, unkompliziert, bedenkenlos. Wir bieten dir hier die Möglichkeit, anderen Menschen sehr nahe zu kommen, ohne Verpflichtungen zu haben. Näher geht es nicht. Erlebe den Reiz und tauche ein, in eine andere Welt. Die Idee für getragene Höschen aus Berlin stammt von der Gründerin von FrauKruner.de. Sie hat selbst jahrelang getragene Unterwäsche kreiert und weiß, wie es geht. Sie hat mit diesem Nebenjob vom Sofa aus Geld verdient. Allerdings ist es um die Seiten, auf denen man seine Artikel anbieten kann, nicht einfach. Gebühren für Shops auf denen nichts verkauft wird, Abofallen, unübersichtliches Layout, unseriöse Anbieter, kompliziertes Kaufverfahren. Und dann ist da immer noch der Datentausch mit einem Kunden auf eigene Verantwortung. Zudem muss das Thema getragene Höschen kaufen raus aus der Schmuddelecke und salonfähig gemacht werden. Denn dieser Fetisch ist kein Tabuthema mehr. Auf unserer Seite kannst du gebührenfrei getragene Höschen kaufen und verkaufen. Wir haben keine Lieferkosten und keine Shopgebühren.
                         </p>
                         <h3 class="small">Ein Dufthöschen kaufen - dieser Artikel ist der Hauptakteur</h3>
                         <p>
                         Woher kommt das eigentlich, ein Dufthöschen kaufen? Jährlich werden weltweit mehrere Milliarden Unterhosen produziert. Das ist genug, um die Erde viele Male zu umwickeln. Höschen und Unterhosen haben eine lange Geschichte und sind bereits seit dem 15. Jahrhundert bekannt. Sicher gibt es sie noch länger. Ein Dufthöschen ist der Überbegriff für getragene Unterwäsche im Allgemeinen. Dies können verschiedenste Schnitte und Artikel sein. Dass sie zu einem Fetischprodukt wurden, hat sich im Laufe der Zeit entwickelt. Bekannt ist dies schon einige Jahrzehnte, nur wird es in den letzten Jahren immer mehr populär. Da es an der intimsten Stelle unseres Körpers liegt und allerhand Natur aufnimmt, ist es nahezu selbsterklärend, dass hieraus ein umgangssprachliches Dufthöschen geworden ist. Düfte, Hormone und Pheromone sind Botschafter unserer Biografie und unserer Lust und es gibt sie, seitdem es uns gibt. 
                         </p>
                         <h3 class="small">Was haben getragene Wäsche und getragene Socken gemeinsam? </h3>
                         <p>
                         Galten sie vor vielen Jahren noch als unsauber und verpönt, so ist es heute ein Leichtes, sich diese besondere Ware zu beschaffen. Dreckige Schlüpfer, getragene Schlüpfer, benutzte Höschen und eben auch getragene Socken. Der Begriff Schlüpfer war übrigens total schick. Früher war dies ein ganz normaler Ausdruck für die Unterhosen, hauptsächlich in Deutschland und Österreich. In den 50 er Jahren galt es sogar als moderner Begriff für bequeme und praktische Damenunterwäsche. Heute wird das Wort doch eher belächelt und klingt oft abwertend oder altmodisch. Auch die Schnitte und Formen haben sich im Laufe der Zeit weiterentwickelt.<br>
                          Ein klassischer Schlüpfer ist ein sehr breites Modell, im Vergleich zu Strings und Tangas, die heute angesagt sind. Vielleicht waren getragene Schlüpfer nicht immer bequem, aber viel Feuchtigkeit haben sie aufgenommen. Liebhaber, die etwas von Düften verstehen, schwören auf diese Produkte. Wenn sie Damen Schlüpfer gebraucht kaufen, dann achten sie oft explizit auf die Baumwolle. Dieser Natürliche Stoff verfälscht das Trageergebnis nicht, wie manche synthetischen Verarbeitungen. Und nun die Frage, was haben getragene Wäsche und getragene Socken gemeinsam? Der 2. Hauptakteur sind nämlich die Socken. Sie dienen ebenfalls als Fetischartikel, auch wenn sie an den Füßen getragen werden. Der Fußschweiß und der Geruch sind hier von Vorteil, aber manchmal geht es auch nur um ein Spiel aus Unterwerfung und Dominanz. Dieser Artikel ist ebenfalls ein Verkaufsschlager und gehört mit zu dem Überbegriff getragene Wäsche. 
                         </p>
                     </div>

                 </div>
             </section>



         </main>



     </x-front_app>

