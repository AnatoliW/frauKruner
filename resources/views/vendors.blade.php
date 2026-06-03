<x-front_app>
    @section('canonical', request()->fullUrlWithoutQuery(['page']))
    @section('title', 'FrauKruner – Sicherer Marktplatz für getragene Damenunterwäsche seriösen Verkäuferinnen')
     @section('description',
         'Erlebe exklusive getragene Damenunterwäsche, diskret, seriös und sicher. Mit echten Verkäuferinnen und erstklassigem Service bei FrauKruner.de.')
    @push('css')
        <style>
            /* .product-list-shop__product{
                                                            width: 100%;
                                                        } */
            .disabled-link {
                pointer-events: none;
                color: gray;
                text-decoration: none;
                cursor: not-allowed;
            }
        </style>
    @endpush
    <!--Heading Shop-->
    <section class="container-xxl">
        <div class="shop-heading-section">

            <div class="shop-section-right">
                <form role="search" method="get" class="search-form-shop" id="searchFormShop" action="">
                    <label>
                        <input type="search" class="search-field-shop" placeholder="Suche…" value="" name="search"
                            title="Suchen" />
                    </label>
                    <input type="submit" class="search-submit-shop" value="Suchen" />
                </form>
            </div>


            <div class="shop-heading-section-center-left align-items-center">

                <form role="search" method="get" class="search-form-shop" id="searchFormShop" action="">
                    <label>
                        <input type="search" class="search-field-shop" placeholder="Suche…" value=""
                            name="search" title="Suchen" />
                    </label>
                    <input type="submit" class="search-submit-shop" value="Suchen" />
                </form>
                <!--
                <form action="/" class="sortierung-main-shop ">
                    <label for="sortierung" class="d-none" style="width:100%">Sortiere nach</label>

                    <select name="sortierung" onchange="filterSecond(this.value,'short')" class="sortierung "
                        id="sortierung">

                        <option value="standard">Standard</option>
                        {{-- <option value="preis" >Preis</option> --}}
                        <option value="neuste" {{ request()->short == 'neuste' ? 'selected' : '' }}>Neuste</option>
                        <option value="verkaeufe" {{ request()->short == 'verkaeufe' ? 'selected' : '' }}>Verkaeufe</option>

                    </select>
                </form> -->

            </div>

        </div>
    </section>
    <!--Heading Shop-->


    <section class="container-xxl">
        <div class="shop-heading-section">
            <!-- Vendor-->
            <div class="shop-section-center-left">


                <h1>Verkäufer*innen</h1>
                <div class="product-list-shop">
                    <!-- Single Vendor Item-->
                    @foreach ($vendors as $vendor)
                        @php

                            $avg_rating = round($vendor->ratings()->avg('rating'));
                        @endphp

                        <div class="product-list-shop__product {{ $vendor->boosted ? 'boosted' : '' }} {{ !$vendor->status ? 'paused' : '' }}"
                            style="position: relative">
                            <a href="{{ route('user.profile', $vendor->id) }}">
                                <div class="product-list-shop__product__image vendor">

                                    <img data-src="{{ $vendor->profileImage() ?: 'https://www.fraukruner.de/assets/img/user.png' }}"
                                        class="lazy" alt="{{ $vendor->username }}">
                                    @auth
                                        @if (auth()->user()->role_id == 2)
                                            @if (isset(auth()->user()->favorites) &&
                                                    auth()->user()->favorites->contains('favoritable_id', $vendor->id))
                                                @php
                                                    $favorite = auth()
                                                        ->user()
                                                        ->favorites->where('favoritable_id', $vendor->id)
                                                        ->first();

                                                @endphp
                                                <form class="deleteFavoriteForm" method="POST">
                                                    @csrf

                                                    <input type="hidden" name="product" value="{{ $vendor->id }}">
                                                    <input type="hidden" name="create" class="deleteFormfavCreate">
                                                    <input type="hidden" name="favorite_id" value="{{ $favorite->id }}">
                                                    <input type="hidden" name="model_type"
                                                        value="{{ get_class($vendor) }}">

                                                    <button type="submit" class="heart-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                            fill="currentColor" class="size-6">
                                                            <path
                                                                d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
                                                        </svg>

                                                    </button>
                                                </form>
                                                @else
                                                    <form action="{{ route('favorite.store', $vendor->id) }}"
                                                        method="post" class="favoriteForm">
                                                        @csrf
                                                        <input type="hidden" name="model_type"
                                                            value="{{ get_class($vendor) }}">
                                                        <input type="hidden" name="favorite_id" class="favoriteId">
                                                        <button type="submit" class="heart-icon favoriteButton">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-6">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                            @endif
                                        @endif
                                    @else
                                        <a href="javascript:void()" class="heart-icon d-flex align-items-center justify-content-center" data-bs-toggle="modal"
                                            data-bs-target="#loginModal">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                            </svg>
                                        </a>
                                    @endauth


                                    <span class="boosted-badge badge">Gepusht</span>
                                    <span class="profil-pausiert-badge badge">Pausiert</span>
                                </div>
                                <div class="product-list-shop__product__content">
                                    <a href="javascript:void(0)"
                                        class="star-rating-container mb-1 mt-1{{ $vendor->ratings->count() > 0 ? '' : 'disabled-link' }}"
                                        onclick="reviews({{ $vendor->id }})">
                                        <div class="star-rating">

                                            <input name="rating" type="number" value="{{ $avg_rating }}"
                                                class="rating published_rating" data-size="xs">

                                        </div>
                                        <p class="mt-0">

                                            {{ $vendor->ratings->count() }}
                                           
                                        </p>

                                    </a>
                                    <b>{{ $vendor->username }} </b>
                                    {{-- <p>{{$vendor->point->points}}</p> --}}
                                    @if ($vendor->status == false)
                                        <p>Hey, ich bin leider gerade nicht aktiv.</p>
                                    @else
                                        <p>{{ Illuminate\Support\Str::limit(html_entity_decode(strip_tags($vendor->profile ? $vendor->profile->description : '')), 50) }}
                                        </p>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach

                    <!-- Single Vendor Item-->

                </div>
                {{ $vendors->onEachSide(0)->links() }}



            </div>
            <!-- Vendor-->
        </div>



    </section>

    <section class="container-xxl">

    <div class="main-container mb-5">

        <h2 class="small">FrauKruner.de und ihre seriöse Unterwäsche Verkäuferin</h2>

        <div class="only-so-big">
            <p>Anders als auf anderen Plattformen und Marktplätzen, arbeiten wir ausschließlich nur mit seriösen, altersgeprüften Unterwäsche Verkäuferinnen zusammen, die ihre Echtheit zweifelsfrei belegt haben. Was die Aufnahme neuer Unterwäsche Verkäuferinnen angeht, haben wir strenge Richtlinien. Warum? Weil unser getragene Unterwäsche Shop stets exzellente Ware produziert und dafür kommt nicht jede in Frage. Wir haben einen echten und schnellen Kundenservice, anders, als es heute üblich ist. Somit grenzen wir uns stark von anderen Anbietern ab. Unsere Unterwäsche Verkäuferinnen und auch unsere Käufer können sich zu 100% fallen lassen und sich in Sicherheit wiegen. Wir schützen nicht nur deinen Namen und deine Bankdaten, nein auch dein Leben, damit du deinen Nebenverdienst und deinen Kink sicher und ehrlich ausleben kannst. Die Anonymität deiner Vorlieben bleibt bei uns gewahrt und ein gut gehütetes Geheimnis. Vertrauen wächst nicht einfach aus dem Boden. Poppen denn mal schwarze Schafe irgendwo auf, ist der Schaden schnell größer als der Reiz. Was bleibt ist die Enttäuschung. Darum setzen wir mit unseren ausgewählten seriösen Unterwäsche Verkäuferinnen auf Qualität, nicht auf Quantität. Und vor allem auf dich.</p>
            <h3 class="small">Die Unterwäsche Damen lässt keine Wünsche offen</h3>
            <p>
            Ausgefallene oder klassische Designs, Schnitte, Stoffe und die Möglichkeiten sind so vielfältig, wie wir. Die Hersteller und Designer übertreffen sich regelmäßig mit neuen Ideen und Visionen. Die getragene Unterwäsche Damen kaufen ist dabei unser Hauptakteur. Sie lässt keine Wünsche mehr offen. Die getragene Damenunterwäsche ist ein Konzept, welches zu einem erotischen Highlight wird. Hier bedeutet die frische Produktion etwas anderes, als es normalerweise üblich ist. Bei uns machen erst die Gebrauchsspuren und die Veredelungen die Ware vollständig. Wir machen daraus ein Erlebnis. Zudem ist es unser Anspruch, dass du getragene Unterwäsche Damen sicher kaufen kannst.  
            </p>
            <h3 class="small">Frauen in Unterwäsche</h3>
            <p>
            Frauen in Unterwäsche ist zu sehen ist in Social Media Zeiten und dem Online sein, einfacher denn je. Es braucht nur einige Klicks und schnell ist ein doppeldeutiger Post zu sehen, der nicht nur Lust auf mehr macht, sondern auch mehr bietet. Die Nacktheit ist auf ein neues Level gehoben und dabei ist die Qualität und die Kunst der Handyfotografie ein eigener Markt geworden. Früher hat man Frauen in Unterwäsche vielleicht mal flüchtig in der Familie oder am Strand gesehen, heute gibt es sie überall. Bilder werden so inszeniert, dass sie Geschichten erzählen und ästhetisch aussehen. Dabei kommt es nicht darauf an, die perfekte Pussy zu haben, nein, jeder Körper ist schön. Bei Frauen in Unterwäsche geht es um das ganze Bild. Hier gibt es keine Grenzen bezüglich des Alters oder des Körpers.
            </p>
            <h3 class="small">Pussy Fotos machen will gelernt sein</h3>
            <p>
            Fotos schießen ist heute zwar schneller und einfacher, aber ist auch die Qualität und die Art der Fotos mächtig gestiegen. Was früher Fotostudios und Fotografen übernommen haben, ist heute eine Amateur- und Hobbyarbeit. Einfach nur die Beine spreizen und irgendwie ein Pussy Foto machen, gilt heute nicht mehr. Sowas hat nichts mit der Ästhetik und der Hochwertigkeit zu tun, die heute Normalität ist. Gute Fotos machen braucht ein bisschen Talent, ein Gespür für Designs und Stimmungen, Posing, ein Verständnis von Licht und Schatten und zu guter Letzt erst die gute Kamera oder das Handy. Das Objekt, welches fotografiert wird, ist eher nebensächlich, denn dieses kann durch die Kunst des Fotografierens immer perfekt in Szene gesetzt werden. Vorausgesetzt man weiß, was man tut. Hauptsächlich Pussy Fotos machen will gelernt sein, denn oft werden diese ohne eine weitere Person, die zur Hilfe steht, erstellt. Ein bisschen den Körper drehen und wenden und dabei noch alles ins Bild zu bekommen und dann noch einen entspannten Gesichtsausdruck auflegen, ist ein kleines Meisterwerk. Wer erfolgreich Pussy Fotos verkaufen möchte, sollte all dies beherrschen und genau wissen, welche Posen am besten gelingen. 
            </p>
        </div>

        </div>


    </section>
    @push('scripts')
        <script>
            function filterSecond(e, peram) {
                var currentUrl = window.location.href;
                var url = new URL(currentUrl);

                url.searchParams.set(peram, e);

                //price
                var newUrl = url.href;
                window.location = newUrl;
            }
        </script>
    @endpush
</x-front_app>
