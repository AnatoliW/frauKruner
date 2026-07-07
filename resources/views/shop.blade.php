<x-front_app>
    @php
        if (request('category')) {
            $category = App\Category::where('slug', request('category'))->first();
            $title = $category->title ?? 'FrauKruner – Shop der Träume!';
            $description = $category->description ?? 'Echte gefühle. Getragene Unterwäsche, Socken und vieles mehr von echten und verifizierten Frauen kaufen.';
        } else {
            $title = 'FrauKruner – Shop der Träume!';
            $description = 'Echte gefühle. Getragene Slips, Getragene Unterwäsche, Socken und vieles mehr von echten und verifizierten Frauen kaufen. Ohne Anmeldung, diskret und ehrlich bei Frau Kruner.';
        }
        if (request('category') == 'slip') {
            $title = 'FrauKruner – Jeder getragene Slip ist ein Unikat';
            $description = 'Getragene Slips sind ein exklusives Produkt, die nicht in herkömmlichen Geschäften erhältlich sind. Jeder Slip wird individuell nach den Wünschen des Kunden gefertigt, wodurch jedes Stück zu einem Unikat wird.';
        }
        if (request('category') == 'string') {
            $title = 'FrauKruner - Getragene Strings und Tangas bieten sinnliche Erlebnisse';
            $description = 'Strings und Tangas sind nicht das gleiche. Getragene Strings bestehen aus noch viel weniger Stoff, als Tangas. Manchmal sogar nur aus dünnen Fäden. Der Zwickel bei einem getragenen Tanga ist wesentlich größer und kann mehr Veredelungen aufnehmen. ';
        }
        if (request('category') == 'Socken und Strümpfe') {
            $title = 'FrauKruner – Getragene Socken kaufen und die Welt liegt dir zu Füßen';
            $description = 'Der diskrete Marktplatz für Fußliebhaber. Getragene Socken kaufen, ganz anonym und ohne Vorurteile. Entdecke eine vielfältige Auswahl und erfülle deine Wünsche in einem sicheren Umfeld. ';
        }

        if (request('category') == 'panties') {
            $title = 'FrauKruner – Getragene Panties sind Alleskönner ';
            $description = 'Getragene Panties vereinen Erotik und Entspannung in einem Kleidungsstück. Sie sind sinnlich, einzigartig und unisex tragbar.';
        }
        if (request('category') == 'dessous') {
            $title = 'FrauKruner - Sinnlichkeit und Komfort in einem Kleidungsstück, getragene Dessous';
            $description = 'Dessous ist die Verschmelzung von Stil und Verführung. Im Alltag oft nicht sichtbar, lässt sie den Träger in eine andere Rolle schlüpfen und überbringt ein schönes Körper- und Tragegefühl.';
        }
        if (request('category') == 'schuhe') {
            $title = 'FrauKruner - Getragene Schuhe für spezielle Vorlieben';
            $description = 'Entdecke getragene Schuhe mit individueller Geschichte. Perfekt für den Alltag oder als spezielles Sammlerstück. Bequeme Sneaker, eingelaufene Highheels oder edle Lederware. Jedes Produkt erzählt eine Geschichte.';
        }
        if (request('category') == 'exkret') {
            $title = 'FrauKruner – Natursekt kaufen und grenzenlos genießen';
            $description = 'Eine bunte Palette an Exkreten bietet dir eine Erfahrung, die so echt und intensiv ist, wie keine zweite. Vom Natursekt bis zum Kaviar.';
        }
        if (request('category') == 'bh') {
            $title = 'FrauKruner - Getragene BHs zu verkaufen';
            $description = 'Hier wird das Untendrunter zum Hauptdarsteller. Ein besonderes Kleidungsstück mit Charakter, authentisch und echt. Getragene BHs für Liebhaber.';
        }
        if (request('category') == 'video') {
            $title = 'FrauKruner - Tabulose und unzensierte Wunschvideos kaufen';
            $description = 'Ein Video nach deinen Vorstellungen. Schlecht synchronisierte Filmchen auf einer Videokassette wurden abgelöst durch Crush Videos, SB Videos, jede Menge Wunschvideos und vieles mehr, in dem Amateure auch nur genau die Inhalte aufnehmen, die du sehen möchtest. ';
        }
        if (request('category') == 'foto') {
            $title = 'FrauKruner - Exklusive Nacktfotos kaufen';
            $description = 'Wunschfotos und andere einzigartige Kreationen für ein verführerisches Erlebnis. Es ist viel mehr, als nur ein Bild. ';
        }
        if (request('category') == 'sonstiges') {
            $title = 'Authentischer geht es nicht. Natürliche Spuren und echte Erlebnisse warten darauf, entdeckt zu werden. Tauche ein in eine Welt einzigartiger Nischenprodukte, die mehr bieten, als du erwartest. Benutzte Windel, getragene Bettwäsche und vieles mehr. ';
        }
        if (request('category') == 'spielzeug') {
            $title = 'FrauKruner - Gebrauchtes erotisches Spielzeug kaufen';
            $description = 'Entdecke eine Auswahl an gebrauchtem erotischem Spielzeug. Von Vibratoren bis zu anderen Erotikartikeln - alles unter strengen hygienischen Standards.';
        }
    @endphp
    @section('canonical', request()->fullUrlWithoutQuery(['page']))

    @section('title', $title)
    @section('description', $description)
    @section('head')
        @if (request()->has('time') ||
                request()->has('seller') ||
                request()->has('rating') ||
                request()->has('tag') ||
                request()->has('finishings') ||
                request()->has('additions'))
            <meta name="robots" content="nofollow, noindex">
            <link rel="canonical" href="{{ url()->current() }}">
        @else
            <meta name="robots" content="dofollow">
        @endif
    @stop


    <?php
    function checkSlug($slug, $arr)
    {
        $arr = explode(',', $arr);
        if (in_array($slug, $arr)) {
            return 'checked';
        }
    }
    ?>

    <x-slider />


    <!--Heading Shop-->
    <section class="container-xxl">
        <div class="shop-heading-section">

            <div class="shop-section-right">
                <form role="search" method="get" class="search-form-shop" id="searchFormShop" action="">
                    <label>
                        <input type="search" class="search-field-shop" placeholder="Suche…" value=""
                            name="search" title="Suchen" />
                    </label>
                    <input type="submit" class="search-submit-shop" value="Suchen" />
                </form>
            </div>


            <div class="shop-heading-section-center-left">

                <!-- Modal Category -->
                <button type="button" class="btn-modal" data-bs-toggle="modal" data-bs-target="#kategorienModal">
                    <img data-src="{{ asset('assets/img/icons/kategorie-icon.svg') }}" class="lazy"> Kategorien
                </button>


                <!-- Modal -->
                <div class="modal fade" id="kategorienModal" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="kategorienModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollabled">
                        <div class="modal-content">
                            <div class="modal-header">
                                <p class="h5 modal-title" id="kategorienModalLabel">Kategorien</p>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="sorting-list-non-collapsing">
                                    <ul>
                                    <li class="@if (empty(request()->all()) || request()->has('bestseller')) current-menu-item @endif">
                                        @if (request()->has('bestseller'))
                                            <a href="{{ route('shop', ['bestseller' => true]) }}">Alle</a>
                                        @else
                                            <a href="{{ route('shop') }}">Alle</a>
                                        @endif
                                    </li>
                                    <li class="" >
                                        <a href="/getragene-unterwaesche-kaufen" title="Getregene Unterwäsche kaufen">Unterwäsche</a>
                                    </li>
                                        @foreach ($categories as $category)
                                            <li class="@if (request('category') == $category->slug) current-menu-item @endif"> <a
                                                    href="javascript::void(0)"
                                                    onclick="filterSecond('{{ $category->slug }}','category')">{{ $category->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal Category -->

                <!-- Modal Filter -->
                <button type="button" class="btn-modal" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <img data-src="{{ asset('assets/img/icons/filter-icon.svg') }}" class="lazy"> Filter
                </button>

                <!-- Modal -->
                <div class="modal fade" id="filterModal" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollabled">
                        <div class="modal-content">
                            <div class="modal-header">
                                <p class="h5 modal-title" id="filterModalLabel">Filter <span style="margin-left:15px;"><a
                                            href="{{ route('shop') }}">zurücksetzen</a></span></p>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">

                                <div class="sorting-list-collapsing">
                                    <button class="sidebar-collapse-button-closed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapseTragedauer"
                                        aria-expanded="false" aria-controls="collapseTragedauer">
                                        <span class="arrow"><span></span><span></span></span> Tragedauer
                                    </button>

                                    <div class="collapse collapse__sidebar" id="collapseTragedauer">
                                        <ul>
                                            @foreach ($wearingTimes as $time)
                                                @if (!is_object($time))
                                                    @continue
                                                @endif
                                                <li><a onclick="filterSecond('{{ $time->name }}','time')"
                                                        href="javascript::void(0)">
                                                        {{ $time->name }} </a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="sorting-list-collapsing">
                                    <button class="sidebar-collapse-button-closed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#rating" aria-expanded="false"
                                        aria-controls="rating">
                                        <span class="arrow"><span></span><span></span></span> Erfahrung
                                    </button>

                                    <div class="collapse collapse__sidebar" id="rating">
                                        <Ul>
                                            @foreach ([1, 2, 3, 4, 5] as $rating)
                                                <li class="@if (request('rating') == $rating) current-menu-item @endif">
                                                    <a href="javascript::void(0)"
                                                        onclick="filterSecond('{{ $rating }}','rating')">{{ $rating }}
                                                        Stern</a>
                                                </li>
                                            @endforeach
                                        </Ul>

                                    </div>
                                </div>

                                {{-- <div class="sorting-list-collapsing">
                                    <button class="sidebar-collapse-button-closed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapseBewertung"
                                        aria-expanded="false" aria-controls="collapseBewertung">
                                        <span class="arrow"><span></span><span></span></span> Kategorien
                                    </button>

                                    <div class="collapse collapse__sidebar" id="collapseBewertung">
                                        <Ul>
                                            @foreach ($categories as $category)
                                                <li class=""><a
                                                        href="javascript::void(0)" onclick="filterSecond('{{$category->slug}}','category')">{{ $category->name }}</a>
                                                </li>
                                            @endforeach
                                        </Ul>

                                    </div>
                                </div>
                                 --}}
                                 {{-- <div class="sorting-list-collapsing">
                                    <button class="sidebar-collapse-button-closed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#user" aria-expanded="false"
                                        aria-controls="user">
                                        <span class="arrow"><span></span><span></span></span> Verkäuferin
                                    </button>

                                    <div class="collapse collapse__sidebar" id="user">
                                        <Ul>
                                            @foreach ($users as $user)
                                                <li><a href="javascript::void(0)"
                                                        onclick="filterSecond('{{ $user->id }}','seller')">{{ optional($user->profile)->username ?? ($user->username ?? $user->name) }}</a>
                                                </li>
                                            @endforeach
                                        </Ul>
                                    </div>
                                </div>
                                --}}
                                <div class="sorting-list-collapsing">
                                    <button class="sidebar-collapse-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#veredelungen"
                                        aria-expanded="false" aria-controls="veredelungen">
                                        <span class="arrow"><span></span><span></span></span> Veredelungen
                                    </button>

                                    <div class="collapse collapse__sidebar " id="veredelungen">
                                        @foreach ($finishings as $finishing)
                                            <input id="{{ $finishing->name }}" type="checkbox"
                                                class="finishings common_selector" value="{{ $finishing->name }}"
                                                {{ checkSlug($finishing->name, request('finishings')) }} /><label
                                                for="{{ $finishing->name }}" class="filterItem" data-status="false"
                                                data-key="finishing"
                                                data-value="{{ $finishing->name }}">{{ $finishing->name }}</label>
                                        @endforeach
                                    </div>
                                </div>



                                <div class="sorting-list-collapsing">
                                    <button class="sidebar-collapse-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#Zusatzoptionentamim"
                                        aria-expanded="false" aria-controls="Zusatzoptionentamim">
                                        <span class="arrow"><span></span><span></span></span> Zusatzoptionen
                                    </button>

                                    <div class="collapse collapse__sidebar " id="Zusatzoptionentamim">
                                        <Ul>
                                            @foreach ($additions as $addition)
                                                <li><a
                                                        href="{{ route('shop', ['additions' => $addition->name]) }}">{{ $addition->name }}</a>
                                                </li>
                                            @endforeach
                                        </Ul>
                                    </div>
                                </div>


                                <div class="sorting-list-collapsing">
                                    <p class="h3 small">Schlagwörter</p>
                                    <div class="sorting-list-tags-cloud">
                                        @foreach ($tags as $tag)
                                            <a href="javascript::void(0)"
                                                onclick="filterSecond('{{ $tag->name }}','tag')">
                                                {{ $tag->name }}
                                                {{ $loop->last == 1 ? '' : ',' }}</a>
                                        @endforeach

                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal Filter -->

                <a href="{{ route('shop', ['orderBy' => 'verkaeufe']) }}" class="bestseller-main-shop">Bestseller
                    <svg xmlns="http://www.w3.org/2000/svg" width="12.724" height="7.367"
                        viewBox="0 0 12.724 7.367">
                        <g id="Gruppe_1296" data-name="Gruppe 1296"
                            transform="translate(328.862 10492.522) rotate(180)">
                            <path id="Pfad_1325" data-name="Pfad 1325" d="M0,0,6.3,6,0,12"
                                transform="translate(328.5 10485.5) rotate(90)" fill="none" stroke-miterlimit="10"
                                stroke-width="1" />
                        </g>
                    </svg>
                </a>


            </div>

        </div>
    </section>
    <!--Heading Shop-->


    <section class="container-xxl">
        <div class="shop-heading-section">
            <aside class="shop-section-right align-items-start">
                <div class="d-flex mb-5 small">
                    <a href="{{ route('shop') }}">Auswahl zurücksetzen</a>
                </div>


                <a href="{{ route('shop', ['orderBy' => 'verkaeufe']) }}" class="h3 small">
                    Bestseller
                </a>

                <div class="sorting-list-collapsing mt-2">
                    <button class="sidebar-collapse-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#categoryCollapse" aria-expanded="true" aria-controls="categoryCollapse">
                        <span class="arrow"><span></span><span></span></span> Kategorien
                    </button>

                    <div class="collapse collapse__sidebar show" id="categoryCollapse">
                        <ul>
                            <li class="@if (empty(request()->all()) || request()->has('bestseller')) current-menu-item @endif">
                                @if (request()->has('bestseller'))
                                    <a href="{{ route('shop', ['bestseller' => true]) }}">Alle</a>
                                @else
                                    <a href="{{ route('shop') }}">Alle</a>
                                @endif
                            </li>
                            <li class="" >
                                <a href="/getragene-unterwaesche-kaufen" title="Getregene Unterwäsche kaufen">Unterwäsche</a>
                            </li>
                            @foreach ($categories as $category)
                                <li class="@if (request('category') == $category->slug) current-menu-item @endif"><a
                                        href="javascript::void(0)"
                                        onclick="filterSecond('{{ $category->slug }}','category')">{{ $category->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="sorting-list-collapsing">
                    <button class="sidebar-collapse-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseTragedauer" aria-expanded="false"
                        aria-controls="collapseTragedauer">
                        <span class="arrow"><span></span><span></span></span> Tragedauer
                    </button>

                    <div class="collapse collapse__sidebar" id="collapseTragedauer">
                        <ul>
                            @foreach ($wearingTimes as $time)
                                @if (!is_object($time))
                                    @continue
                                @endif
                                <li><a onclick="filterSecond('{{ $time->name }}','time')"
                                        href="javascript::void(0)">
                                        {{ $time->name }} </a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="sorting-list-collapsing">
                    <button class="sidebar-collapse-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseBewertung" aria-expanded="false" aria-controls="collapseBewertung">
                        <span class="arrow"><span></span><span></span></span> Erfahrung
                    </button>

                    <div class="collapse__sidebar collapse" id="collapseBewertung" style="">
                        <ul>
                            @foreach ([1, 2, 3, 4, 5] as $rating)
                                <li class="@if (request('rating') == $rating) current-menu-item @endif"><a
                                        href="javascript::void(0)"
                                        onclick="filterSecond('{{ $rating }}','rating')">{{ $rating }}
                                        Stern</a>
                                </li>
                            @endforeach

                        </ul>
                    </div>
                </div>





                {{--
                <div class="sorting-list-collapsing">
                    <button class="sidebar-collapse-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#woman" aria-expanded="false" aria-controls="collapseVerkaeferin">
                        <span class="arrow"><span></span><span></span></span> Verkäuferin
                    </button>

                    <div class="collapse__sidebar collapse " id="woman" style="">
                        <ul>
                            @foreach ($users as $user)
                                <li>
                                    <a href="javascript::void(0)"
                                        onclick="filterSecond('{{ $user->id }}','seller')">{{ optional($user->profile)->username ?? ($user->username ?? $user->name) }}</a>
                                </li>
                            @endforeach

                        </ul>
                    </div>
                </div>

                --}}
                <div class="sorting-list-collapsing">
                    <button class="sidebar-collapse-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#veredelungen" aria-expanded="false" aria-controls="veredelungen">
                        <span class="arrow"><span></span><span></span></span> Veredelungen
                    </button>

                    <div class="collapse collapse__sidebar " id="veredelungen">
                        @foreach ($finishings as $finishing)
                            <input id="{{ $finishing->name }}" type="checkbox" class="finishings common_selector"
                                value="{{ $finishing->name }}"
                                {{ checkSlug($finishing->name, request('finishings')) }} /><label
                                for="{{ $finishing->name }}" class="filterItem" data-status="false"
                                data-key="finishing"
                                data-value="{{ $finishing->name }}">{{ $finishing->name }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="sorting-list-collapsing">
                    <button class="sidebar-collapse-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#finish" aria-expanded="false" aria-controls="finish">
                        <span class="arrow"><span></span><span></span></span> Zusatzoptionen

                    </button>

                    <div class="collapse collapse__sidebar " id="finish">
                        @foreach ($additions as $addition)
                            <input id="{{ $addition->name }}" type="checkbox" class="additions common_selector"
                                value="{{ $addition->name }}"
                                {{ checkSlug($addition->name, request('additions')) }} /><label
                                for="{{ $addition->name }}" class="filterItem" data-status="false"
                                data-key="finishing"
                                data-value="{{ $addition->name }}">{{ $addition->name }}</label>
                        @endforeach
                    </div>
                </div>

                <div class="sorting-list-collapsing">
                    <p class="h3 small">Schlagwörter</p>
                    <div class="sorting-list-tags-cloud">
                        @foreach ($tags as $tag)
                            <a href="javascript::void(0)"
                                onclick="filterSecond('{{ $tag->name }}','tag')">{{ $tag->name }}
                                {{ $loop->last == 1 ? '' : ',' }}</a>
                        @endforeach

                    </div>
                </div>


            </aside>
            <!-- Products-->

            <div class="shop-section-center-left">

                @if (request('category') == 'Socken und Strümpfe')
                    <h1>Getragene Socken kaufen</h1>
                @elseif (request('category') == 'slip')
                    <h1>Getragene Slips kaufen</h1>
                @elseif (request('category') == 'dessous')
                    <h1>Getragene Dessous kaufen</h1>
                @elseif (request('category') == 'string')
                    <h1>Getragene Strings kaufen </h1>
                @elseif (request('category') == 'panties')
                    <h1>Getragene Panties kaufen</h1>
                @elseif (request('category') == 'schuhe')
                    <h1>Getragene Schuhe kaufen</h1>
                @elseif (request('category') == 'exkret')
                    <h1>Exkret kaufen</h1>
                @elseif (request('category') == 'bh')
                    <h1>Getragene BHs kaufen</h1>
                @elseif (request('category') == 'video')
                    <h1>Wunschvideos kaufen</h1>
                @elseif (request('category') == 'foto')
                    <h1>Nacktfotos kaufen</h1>
                @elseif (request('category') == 'spielzeug')
                    <h1>Gebrauchtes erotisches Spielzeug kaufen</h1>
                @else
                    <h1>Shop der Träume</h1>
                @endif
                @foreach ($products->chunk(6) as $key => $chunk)
                    <div class="product-list-shop">
                        @foreach ($chunk as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    @if (isset($profiles->chunk(1)[$key]))
                    @foreach ($profiles->chunk(1)[$key] as $profile)
                        <x-shop.product :profile="$profile" />
                    @endforeach
                    @endif

                @endforeach
                {{ $products->onEachSide(2)->withQueryString()->links() }}

                @if (request('category') == 'Socken und Strümpfe')
                    <h2 class="mt-5 small">Getragene Socken sind immer paarweise</h2>
                    <p>Vermutlich kennt so jeder das Phänomen, dass ein Paar Socken in die Waschmaschine gegeben werden und am Ende nur eine einzelne Socke herauskommt. In den USA verschwinden jedes Jahr rund 1,3 Milliarden, in Deutschland rund 1,245 Milliarden Socken pro Jahr. Forscher haben sogar eine „Sockentverlustformel“ entwickelt, um zu berechnen, wie wahrscheinlich es ist, dass getragene Socken beim Waschen verschwinden. Angeblich sind es pro Kopf 15 getragene Socken pro Jahr. Diese Zahl verdeutlicht, dass der Verlust von Socken nicht nur ein weit verbreitetes Phänomen ist, sondern auch leicht finanzielle Auswirkungen hat. Aber das ist ein anderes Thema. Bei Frau Kruner kannst du glücklicherweise getragene Socken kaufen und sie sind immer paarweise. Das ist ein Versprechen. </p>

                    <h3 class="small">Getragene Socken kaufen: Ein diskreter Markt für besondere Wünsche</h3>
                    <p>Das Interesse an Socken kaufen getragen, insbesondere getragene Frauensocken kaufen, hat in den letzten Jahren stark zugenommen. Viele Menschen schätzen die einzigartige Anziehungskraft und den persönlichen Reiz dieser Artikel. Egal ob du sie aus der Wäsche deiner Partnerin nimmst, oder gebrauchte Socken online kaufen möchtest, es wird dich umhauen. Und dies ist nicht zwangsläufig auf den Geruch zurückzuführen. Der Geruch enthält Pheromone und andere körpereigene Duftstoffe, die eine erotische Wirkung haben können. Manche Menschen verbinden getragene Socken kaufen aber eher mit Machtspielen, zum Beispiel als Zeichen der Unterwerfung oder Verehrung gegenüber einer anderen Person. Dies kann z. B. der eigene Partner sein. Von ihr/ ihm gebrauchte Socken zu benutzen ist ein Highlight, eben weil ihr vertraut miteinander seid. Oder eine halb fremde Person, der man die Socken aus der Wäsche entwendet, während man auf der Toilette ist. Oder eben eine gänzlich unbekannte Person. Hier kommen wir ins Spiel. Bei uns getragene Socken kaufen ist ein diskreter Markt für besondere Wünsche. Ohne gesellschaftliche Vorurteile, Scham und Hürden. </p>

                    <h3 class="small">Getragene Nylons - das etwas andere Material</h3>
                    <p>Nylonfans aufgepasst. Wer hätte gedacht, dass dieser Stoff so langlebig und wasserfest ist? Zerrissene Strümpfe und Laufmaschen kennt so ziemlich jeder. Dennoch ist Nylon ein sehr langlebiger Stoff, der zudem auch wasserfest ist. Die synthetische Faser, die aus Polyamiden besteht, ist ein echter Alleskönner, nicht nur in der Modeindustrie. Das softe und glänzende Material spielt eine große Rolle, da es viel mehr, als ein visuelles Element ist. Getragene Nylons ist das etwas andere Material, hauptsächlich liegt das an der Haptik. Zudem ist oft die Haut durch getragene Nylons zu sehen. Ein leicht erotischer Einblick, immer perfekt in Szene gesetzt ist. Oft ist nicht zu erkennen, ob es eine Nylonstrumpfhose oder nur getragene Nylonstrümpfe sind, die mit einer Art Strapshalter in Position gehalten werden. </p>

                    <h3 class="small">Gebrauchte Socken verkaufen Preis – dein umfassender Guide für den sicheren Verkauf</h3>
                    <p>Ein Shop für den Verkauf ist schnell erstellt, doch was ist mit der Preisgestaltung? Gebrauchte Socken verkaufen Preis wird schnell im Internet gesucht, doch die Preise sind weit auseinander. Wie setzt sich der für dich richtige Preis für gebrauchte Socken zusammen? Hier lässt dich ein Blick in die Shopseite einen ersten Richtwert finden. Schaue wie die Preise von anderen Nutzern sind. Beachte dazu auch immer, ob du Markenware hast. Die Preise für gebrauchte Socken Adidas oder einem no name Produkt, welches du in der Mehrfachpackung günstig erworben hast, gehen auseinander. Dein Fußgeruch und die Kreativität, die du in deinen Shop und in deine Fotos steckst, zählen auch als wichtiger Faktor für die Preisgestaltung. Getragene Socken ebay ist kein Richtwert für unsere Plattform. Auch nicht getragene Socken Vinted. Bitte achte darauf, dich niemals unter Wert anzubieten. Wir garantieren Qualität und möchten dies auch weiterhin gewährleisten. Bist du eine kleine Bekanntheit oder hast in Social Media eine Fanbase, dann kannst du recht hoch ansetzten. Der getragene Socken Preis richtet sich zudem auch nach der Tragezeit und den Veredelungen. Gebrauchte Socken online kaufen übrigens nicht nur Männer. Benutzte Socken sind ein Naturprodukt, dennoch legen wir großen Wert auf die Hygiene. Das ist kein Widerspruch, denn die Hygiene von der wir reden, betrifft den medizinischen Zustand unserer Shops. Nach oben hin hast du für gebrauchte Socken verkaufen preislich keine Grenze.</p>

                    <section itemscope="" itemtype="https://schema.org/FAQPage">

                        <h2 class="mt-5 small">FAQ zu getragenen Socken</h2>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSocken1" aria-expanded="true" aria-controls="collapseSocken1" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Was sind getragene Socken?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSocken1" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Getragene Socken sind Socken, die von einer Person für eine gewisse Zeit getragen wurden, bevor sie weiterverkauft werden. Diese Produkte sind in bestimmten Nischenmärkten aufgrund ihrer einzigartigen Eigenschaften wie Geruch und/ oder Rückstände gefragt.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSocken2" aria-expanded="true" aria-controls="collapseSocken2" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Was ist der Unterschied zwischen getragenen Socken und Unterwäsche?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSocken2" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Der Hauptunterschied liegt in der Textilart und den damit verbundenen Vorlieben. Während Unterwäsche vor allem aufgrund ihrer Nähe zur intimen Natur geschätzt wird, liegt der Fokus bei Socken auf dem Fußgeruch und den im Alltag möglichen Tragespuren. Beide Artikel haben sehr unterschiedliche Düfte und Spuren. Beide Kategorien haben jedoch ähnliche Käufergruppen.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSocken3" aria-expanded="true" aria-controls="collapseSocken3" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Ich stehe auf Socken, bin ich krank?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSocken3" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Nein, du bist weder krank, noch musst du damit zum Arzt oder Psychologen. Solche und andere Vorlieben fallen unter den Bereich der sexuellen Präferenzen und sind, solange sie einvernehmlich und nicht schädlich sind, sogar vollkommen legitim und keine Krankheit. Im Gegenteil, denn es ist Ausdruck von Individualität und intimer Aktivität, was gut für deinen Körper ist. Problematisch wird es erst, wenn du dich im Alltag eingeschränkt fühlst, dein normales Leben darunter leidet oder du Schwierigkeiten hast, den Tag zu bewältigen. In einem solchen Fall kann es sinnvoll sein, dir professionelle Unterstützung zu holen. Wenn du das Gefühl hast, dass dich deine Gedanken oder Vorlieben im Alltag belasten, kannst du dich jederzeit anonym und kostenlos an folgende Anlaufstellen wenden: <a href="https://www.nummergegenkummer.de/" rel="nofollow" title="Nummer gegen Kummer - Beratung für Kinder und Jugendliche">Nummer gegen Kummer: 116 111</a> <a href="https://www.profamilia.de/themen/sexuelle-orientierung-und-geschlechtliche-identitaet" rel="nofollow" title="Pro Familia - Beratung zu sexueller Orientierung und geschlechtlicher Identität">Pro Familia: 069 26957790</a></p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSocken4" aria-expanded="true" aria-controls="collapseSocken4" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Kann ich getragene Socken verkaufen?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSocken4" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Hier ist es wichtig, die Voraussetzungen zu erfüllen. Beim getragenen Socken Verkauf geht es in erster Linie um den Duft und nicht jede*r Mensch hat auch Fußgeruch. Wenn du verkaufen möchtest, ist es wichtig, dass du ehrlich zu dir selbst bist, denn das spiegelt sich final auch in deinen Erfahrungen wieder. Es gibt aber auch eine andere Art von getragenen Socken und das ist der Fanartikel. Hier geht es primär darum, wer die Socken getragen hat, zweitranging oder gar nicht um den Duft. Oft ist das bei Influencers zu beobachten (Merchandise). Hier werden Produkte im dreistelligen Bereich verkauft, die aber wenig mit der Qualität zu tun haben, die für diese wirkliche Neigung essenziell sind.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSocken5" aria-expanded="true" aria-controls="collapseSocken5" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Kann ich Socken verkaufen, wenn sie nur kurz getragen wurden?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSocken5" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Es kommt darauf an, was der Käufer möchte oder du in deinem Produkt angeboten hast. Wenn du ein paar "Stinkesocken verkaufen" möchtest, dann solltest du sie schon so lange tragen, dass auch das Angebot erfüllt wird. Hier kommt der vorherige Punkt zum Tragen: LINK: "Kann ich getragene Socken verkaufen"</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSocken6" aria-expanded="true" aria-controls="collapseSocken6" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Getragene Socken kaufen, aber wo?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSocken6" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Rundum abgesichert bist du bis dato nur bei FrauKruner.de. Wir haben hohe Standards, was den Datenschutz und die Anonymität angeht. Unsere Verkäuferinnen sind qualifiziert und Fake Profile zu 100% ausgeschlossen. Wir haften, wenn du Ware nicht erhalten hast, ersetzen dir deinen Kauf oder erstatten dir dein Geld zurück. Unser Kundenservice ist echt und montags - freitags telefonisch unter 030 96607799 zu erreichen und das auch an Feiertagen. Per Mail antworten wir innerhalb von 24h und auch am Wochenende. Bei uns bekommst du echte Unterstützung, nicht nur automatisierte Antworten.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSocken7" aria-expanded="true" aria-controls="collapseSocken7" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Welche Risiken gibt es beim Kauf von benutzten Socken?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSocken7" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Die Hygienestandards müssen eingehalten werden. Wenn du Infektionen an den Füßen hast oder häufiger damit in Kontakt kommst, solltest du keine getragenen Socken verkaufen. Weiterhin ist die Wahl des richtigen Anbieters wichtig. Eine sichere Plattform für getragene Socken ohne den Austausch persönlicher Daten ist bis dato nur FrauKruner.de. In diesem sensiblen Bereich müssen Betrug und Missbrauch ausgeschlossen und potenzielle Angriffsflächen so weit wie möglich minimiert werden. Zudem haben Fake-Profile bei uns keine Chance und wir sorgen dafür, dass Verkäufer und Käufer gleichermaßen geschützt sind.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSocken8" aria-expanded="true" aria-controls="collapseSocken8" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Wie sollte man intime Produkte sicher versenden?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSocken8" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Die Artikel werden ausschließlich vakuumiert verschickt. Bei dem Versand von diesen speziellen Produkten sollte man auf Diskretion und Anonymität achten. Vermeide es, auf dem Paket sichtbare Hinweise auf den Inhalt zu geben. Viele Verkäufer*innen verwenden geruchsneutrale Verpackungen und achten darauf, dass die Artikel während des Transports nicht beschädigt werden können. Die Verpackung muss neutral sein. Ein Fensterbriefumschlag gehört nicht dazu. Du notierst niemals deine private Adresse als Absenderadresse, sondern: Frau Kruner - Schönhauser Allee 163 - 10435 Berlin. Der Versand mit Sendungsnummer ist essenziell. Den ganzen Ablauf findest du hier: (LINK: https://fraukruner.de/page/verkauferin-werden )</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSocken9" aria-expanded="true" aria-controls="collapseSocken9" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Welcher Stoff eignet sich am besten für Socken?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSocken9" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Wir empfehlen Naturfasern wie z. B. Baumwollen, Wolle, Leinen, Wolle, je nach Tätigkeitsbereich (Sport, Alltag, Wandern, Schlafen). Diesese nehmen den natürlichen Geruch am besten auf und verfälschen ihn nicht. Dennoch haben auch die syntetischen Fasern wie Nylon und Polyester ihre Fans. Hauptsächlich für Nylonstrümpfe. Das Material hat trotz der künstlichen Herstellung und der schlechten Atmungsaktivität seinen Reiz. Gerüche können hier schnell entstehen, ggf. aber auch schnell künstlich überlagert werden.</p>
                            </div>
                        </div>

                    </section>


                @elseif (request('category') == 'slip')
                    <h2 class="mt-5 small">Wie aus einer langweiligen Unterhose ein Slip gebraucht wurde </h2>

                    <p>Kleidung hatte schon in unseren früheren Kulturen eine symbolische Bedeutung. Intime Kleidungsstücke, insbesondere gebrauchte Slips kaufen, werden oft mit Verlangen in Verbindung gebracht. Die Kombination aus der Natur, dem Geruchssinn, dem Tabu, der Fantasie und der Nähe sorgen dafür, dass aus einer langweiligen Unterhose ein Slip gebraucht wurde. Er hatte schon immer eine Anziehungskraft, weil unsere Gene und unser Geruchssinn so geprägt wurden. Wir alle stehen auf Gerüche und dies merken wir oft nur unterschwellig. Da dieses kleine Stoffstückchen direkt an unseren Geschlechtsorganen sitzt, ist diese Überleitung ein Selbstläufer. Ein Alltagsprodukt wird plötzlich zu einem Cameltoe Slip oder einem inside Slip. Die unkomplizierte Verfügbarkeit geben dem Produkt die Aufmerksamkeit, die es verdient hat. Früher unsichtbar, heute ein Fokus. Gebrauchte Slips kaufen ist nicht mehr wegzudenken und schon lange kein gesellschaftliches No-Go mehr. Was einst nur in den Köpfen schwirrte, wird heute unter getragene Slips online bestellt. Ganz locker und einfach.  </p>

                    <h3 class="small">Ein kleines Stück Stoff mit viel Potenzial - getragene Slips</h3>
                    <p>Unsere Welt wird immer offener für die Bedürfnissen jedes einzelnen. Das hat wiederrum dazu beigetragen, dass getragene Slips kaufen ein Dauerbrenner geworden ist. Früher war es eine Seltenheit oder sogar ein völliges Tabu, doch heute ist es ein alltägliches Geschäft. Die zunehmende Aufklärung und auch der Einfluss des Internets haben unsere Gesellschaft nachhaltig geprägt. Themen wie getragene Slips kaufen, die einst verschwiegen wurden, sind nun offen zugänglich. Übrigens ist das teuerste Produkt dieser Art ein mit Diamanten und Edelsteinen besetztes Stück, welches über eine Million kostet. Dies ist aber kein getragener Slip und es ist auch fragwürdig, ob dieser für den Alltag geeignet ist. Aber auch hier gibt es einen Markt, nur nicht bei uns. Wir sind nur auf getragene Slips kaufen und alles drum herum spezialisiert. Diesem Zusammenhang muss noch gesagt werden, dass es nicht schädlich ist, getragene Slips einfach mehrere Tage lang zu tragen. Früher dachten die Menschen sogar, dass das Wechseln der Unterwäsche schädlich sei und trugen diese mehrere Wochen. Wo heute neue Märkte entstehen, wäre das vor einigen Jahren noch undenkbar gewesen. Es ist ein kleines Stück Stoff mit viel Potenzial, getragene Slips. Sie lösen einen Reiz aus, der in Erinnerung bleibt.  </p>

                    <h3 class="small">Frau Kruner Berlin – der getragene Slips Shop aus der Hauptstadt</h3>
                    <p>Haben wir eigentlich in diesem Bereich der Fetischware schon die Grenze erreicht? Ich denke nicht. Frau Kruner Berlin, der getragene Slips Shop aus der Hauptstadt bietet sehr spezielle Ware an. In den nächsten Jahren wird sich zeigen, wie sich die Trends weiterentwickeln. Unsere Hauptstadt bietet eine wunderbare Mischung aus Freiheit, Offenheit und kultureller Vielfalt. Es ist der Nabel der Zeit, eine pulsierende Großstadt. Nicht zu vergessen die historische Bedeutung der Metropole. Das bunte Zentrum zieht täglich zahlreiche Menschen an, die den Spirit und die Geschichte erleben wollen. Die gelebte Freiheit bildet das Fundament von Berlin und auch von Frau Kruner. Der getragene Slips Shop aus der Hauptstadt lockt mit seinem einzigartigen Angebot für getragene Slips. Doch warum ist dies kein klassisches Geschäft, in dem man sich seine Ware direkt aussuchen und mitnehmen kann? Ein Frauen Slip getragen ist ein Produkt, welches eine Maßanfertigung ist. Ein handgefertigtes Einzelstück, ja ein Unikat, welches direkt nach den Wünschen des Kunden produziert und veredelt wird. Beispiel: Der Kunde möchte einen weißen getragenen Slip kaufen, der mit NS veredelt und 3 Tage getragen wurde. Im Shop hätten wir einen Weißen, der aber 1 Tag getragen wurde und mit KV veredelt wurde. Eine Vorproduktion macht bei dieser Art der Ware keinen Sinn und es wird immer ein Produkt sein, welches man nicht im Laden kaufen kann. Eben eine Maßanfertigung, nach den genauen Wünschen der Kunden. Deshalb ist ein Slip getragen auch so was Besonderes. </p>

                    <h3 class="small">Getragene Slips mal anders</h3>
                    <p>Der größte jemals hergestellt Slip wurde in England vorgestellt. Er betrug einen Durchmesser von 50 Metern. Diesen in einen getragenen Slip umzuwandeln, ist nahezu unmöglich. Dass, was unsere Kunden an Slips getragen so schätzen, und zwar die Aufnahmefähigkeit des Zwickels, wurde bei den Astronauten entfernt. Sie tragen eine Art Funktionskleidung mit einer feuchtigkeitsregulierenden Technologie, die extra für diesen Zweck entworfen wurde. Macht Sinn, da sie weltfremden Bedingungen ausgesetzt sind. In Argentinien verschenkt man traditionell zu Weihnachten rote Unterwäsche, die an Silvester getragen werden soll, um Glück im neuen Jahr zu bringen. Getragene Slips mal anders. Auch Unterwäsche für Tiere gibt es, damit diese an den speziellen Tagen auslaufsicher sind. Die Geschichte der Unterhosen ist nahezu unglaublich und mit unseren speziellen Artikeln, hauptsächlich Slips getragen Damen, sind wir ein Teil davon. </p>

                @elseif (request('category') == 'dessous')
                    <h2 class="mt-5 small">Getragene Dessous kaufen ist nicht ungewöhnlich</h2>
                    <p>So verschieden wie wir, so verschieden sind auch die Geschmäcker. Getragene Dessous kaufen ist nicht ungewöhnlich und trägt zu einem erfüllten Liebesleben bei. Es kann in so unendlich vielen Varianten hergestellt und wiederverwendet werden. Der eine mag lieber das klassische benutzte Dessous, der andere mag Dessous verspielt mit Rüschen und Veredelungen und noch ein anderer sucht nur die passende Größe, um es selbst zu tragen. Es gibt hier einen eindeutigen Klassiker. Das getragene Dessous schwarz wird am meisten gesucht. Attraktive Kombinationen verbinden Erotik und ein schönes Tragegefühl, den viele Kunden schätzen. </p>
                    <h3 class="small">Getragene Reizwäsche kaufen – eine noch junge Geschichte? </h3>
                    <p>Getragene Reizwäsche kaufen ist das gleiche wie getragene Dessous kaufen. Reizwäsche ist nur ein veraltetes Wort und klingt doch eher bieder. Und das ist diese Art von Wäsche auf keinen Fall. Das Wort Dessous kommt aus dem Französischen und bedeutet „unten drunter“. Reizwäsche wurde dazu entworfen eine erotische oder verführerische Wirkung zu erzielen. Sie kann aus einer Vielzahl von Materialien hergestellt werden, darunter Spitze, Satin, Seide und Mesh. Es gibt sie in verschiedenen Stilen unter anderem Bhs, Slips, Strings, Korsetts, Strumpfhaltergürtel, große Größen und Reizwäsche Männer. Das spezielle Untendrunter, gibt es schon seit vielen Jahrhunderten. Es hat sich aber im Stil stark verändert. Mit der Geburt des Korsetts hat es dann so richtig Fahrt aufgenommen und ist der Reizwäsche ähnlich, die wir heute kennen. Dazu ist das Thema getragene Reizwäsche kaufen eine noch junge Geschichte. </p>
                    <h3 class="small">Was gibt es noch zum Thema Dessous getragen? </h3>
                    <p>Immer mehr Menschen kennen diesen Trend. Ob es wirklich gerade nur „in“ ist, lässt sich schwer beantworten. Fakt ist aber, dass Thema Dessous kaufen getragen ist schon einige Jahrzehnte lang im Internet bekannt ist. Ein Fetisch, der also nicht so neu ist, wie es auf Social Media gerne verbreitet wird. Aber die Sicherheit deiner Daten und der Anonymität, ist neu. Getragene Dessous in atemberaubenden Designs oder klassisch und bequem. Und ja, auch diese sexy Kleidung kann bequem sein. Bei der richtigen Wahl des Artikels kann sie ganz normal unter der Alltagskleidung getragen werden. Ein Hauch von Erotik, für sich selbst. Was gibt es noch zum Thema Dessous getragen zu sagen? Sie tragen dazu bei, sich in seinem Körper schön und sexy zu fühlen und jeder ist in ihr perfekt in Szene gesetzt. Und manchmal reicht einfach nur das Anziehen, um in eine andere Rolle zu schlüpfen. Wie alt warst du, als du das erste Mal Dessous getragen hast?</p>
                    <h3 class="small">Von renommierten Marken gebrauchte Dessous kaufen</h3>
                    <p>Heute gibt es mehr Shops für diese spezielle Wäsche, als es jemals gab. Der Genießer kennt sich so gut aus, dass er genau weiß, was er möchte. Von renommierten Marken gebrauchte Dessous kaufen ist etwas, worauf Wert gelegt wird und dafür wird auch gerne etwas mehr ausgegeben. Klassischerweise sind es getragene Dessous Hunkemöller und auch etwas von Victoria Secret getragen ist gerne gesehen. Noch oben hin gibt es keinen Grenzen. Audabe spielen hier in einer ganz anderen Preisklasse. Da die Preise jedoch erst im dreistelligen Bereich beginnen, steht das Preis-Leistungs-Verhältnis hier nicht mehr in einem optimalen Verhältnis. Von renommierten Marken gebrauchte Dessous kaufen ist luxuriös, aber ob der hohe Preis den Mehrwert rechtfertigt, bleibt Geschmackssache.</p>
                @elseif (request('category') == 'string')
                    <h2 class="mt-5 small">Getragene Tangas oder getragene Strings kaufen – wo ist der Unterschied?</h2>
                    <p>Tangas und Strings werden schnell verwechselt. Wo ist der Unterschied? Sie unterscheiden sich in der Schnittform und der Bedeckung der Kehrseite, dem Po. Auf den ersten Blick sieht beides identisch aus, aber bestehen Strings aus noch viel weniger Stoff, als Tangas. Ein getragener String ist ein nur wirklich kleiner Streifen am Po. Fast schon unscheinbar und unsichtbar. Die extremste Form ist der G-String. Hauchdünne Streifen halten das winzige Stück Stoff an dem Intimbereich. Eine hervorragende Lösung für enge Kleider, bei denen kein Abdruck der Wäsche zu sehen sein darf. Ein Tanga besteht aus etwas mehr Stoff, als ein getragener String und etwas weniger als ein Slip. Wenn du dennoch nicht weißt, ob du eher getragene Tangas oder getragene Strings kaufen möchtest, dann empfehlen wir dir, die Strings. Sie können mehr Veredelungen und Inhalte aufnehmen als getragene Strings. Auch ist der Zwickel etwas größer. </p>
                    <h3 class="small">Warum solltest du gebrauchte Strings kaufen?</h3>
                    <p>Die unglaubliche Anziehungskraft, mit den individuellen, animalischen Duftnoten, hat einen besonderen Trigger. Sie spielt mit den Kräften unserer Natur. Also warum solltest du gebrauchte Strings kaufen? Um ebenfalls deine Natur auszuleben. Es ist nichts Verwerfliches, seinem Trieb nachzugehen, sich auszutoben und den Alltag ausklingen zu lassen. Er ist viel mehr, als nur eine schnelle Erleichterung. Es ist ein Spiel mit den verschiedensten Sinnen. Die Vorstellung der Nähe zur Trägerin, machen gebrauchte Strings zu begehrten Produkten. Es ist nicht nur die Veredelung, die das intime Erlebnis ausmacht, sondern auch die Farbe, der Schnitt und der Stoff. Auch spielen die verschiedensten Materialien eine wichtige Rolle, zudem macht die Optik einen Unterschied. Der String, wie wir ihn heute kennen, ist eine recht neue Erfindung. Erst in den 1980 Jahren wurde er durch den „Hauteng-Trend“ populär. Seine Anfänge gehen zurück in die 1930 Jahre. Der Name kommt aus dem Englischen und bedeutet „dünnes Band“. Und genau das verkörpert ein gebrauchter String. </p>

                    <h3 class="small">
                    Oder doch lieber einen Tanga gebraucht?
                    </h3>
                    <p>Sie haben den Vorteil, dass sie durch den größeren Zwickel, mehr Veredelungen aufnehmen können. Bei einer Großzahl der Verkäufer ist es genau das, worauf es ankommt. So viel Duft wie möglich soll enthalten sein. Damit das Tanga kaufen gebraucht auch nicht zu einem Problem für dich wird, solltest du eine sichere Plattform wählen. Mit den richtigen Informationen und einem sicheren Vorgehen kannst du getragene Tangas diskret und zuverlässig erwerben. Nutze vertrauenswürdige Plattformen und achte auf Diskretion und Authentizität des Betreibers, um das beste Kauferlebnis zu gewährleisten. In einer sicheren Umgebung kannst du simpel Tangas gebraucht kaufen. Durch unseren deutschen Firmensitz bist du stets abgesichert und kannst die Vorzüge der strengen Richtlinien und Standards genießen. It- Pieces, virale Designs, moderne Schnitte, aber auch Basics, ziehen sich durch den Shop. Einen Tanga gebraucht kaufen ist deine Entspannung und unser Job.  </p>

                @elseif (request('category') == 'panties')
                    <h2 class="mt-5 small">Ästhetik und Entspannung in einem Kleidungsstück – eine getragene Panty</h2>
                    <p>Historisch gesehen sind Panties, also eng anliegende Damenunterhosen, nicht direkt von Männerhosen abgeleitet, sondern haben sich aus älteren Formen der Unterwäsche entwickelt. Doch Panties in ihrer modernen Form entstanden erst im 20. Jahrhundert. In den 1920er Jahren wurde sie zu Ästhetik und Entspannung in einem Kleidungsstück. Eine getragene Panty, gibt es zeitgeschichtlich schon eine kurze Zeit später. Doch was macht sie so besonders? Eine getragene Panties kaufen erfreut sich großer Beliebtheit, da es in der heutigen Zeit kaum noch eine Rolle spielt, wer sie anschließend trägt. Die Grenzen zwischen traditionellen Geschlechternormen verschwimmen zunehmend, zudem ist sie Unisexunterwäsche. Auch Männer entscheiden sich häufig für enganliegende, breit geschnittene Unterhosen. Diese getragene Panty vereint eine körpernahe Passform mit einem angenehmen Tragegefühl und ist sowohl im Alltag, als auch beim Sport beliebt.</p>
                    <h3 class="small">Getragene Pantys oder getragene Panties?</h3>
                    <p>Bei der Einzahl ist es einfach, hier heißt es getragene Panty, doch bei der Mehrzahl wird es kniffliger. Die Übersetzung für Panty ist Höschen, wurde eingedeutscht und somit kommt es zu verschiedenen Schreibweisen. Getragene Panties wäre die grammatikalisch korrekte Form. Ein y wird in der Mehrzahl zu einem ie. Dennoch gibt es im Deutschen auch die Schreibweise getragene Pantys. Dies ist nicht die offizielle Schreibweise, wird aber oft verwendet. </p>
                    <h3 class="small">benutzte Panties und Nachhaltigkeit</h3>
                    <p>In den letzten Jahren hat die Nachfrage nach nachhaltiger Unterwäsche zugenommen. Nicht nur für die Erstbenutzung, sondern auch für den Fetischverkauf. Es ist allgemein bekannt, dass natürliche Stoffe die Gerüche viel besser aufnehmen. Zum Beispiel eine aus Baumwolle benutzte Panties Damen kaufen. Synthetische Stoffe können das Endergebnis verfälschen. Viele Marken setzen auf umweltfreundliche Materialien wie Bio-Baumwolle oder recycelte Stoffe. Diese Entwicklungen zeigen, dass benutzte Panties Damen nicht nur bequem, sondern auch umweltbewusst sein können. Getragene Panties kaufen ist mehr als nur ein funktionales Kleidungsstück. Sie verbinden Tragekomfort, Ästhetik und Funktionalität und tragen maßgeblich zum Wohlbefinden bei, z. B. Baumwollpanties getragen. Von ihrer geschichtlichen Entwicklung bis hin zu modernen, nachhaltigen Designs. Sie sind ein unverzichtbarer Teil unserer Garderobe, der uns täglich begleitet und unterstützt. Eine bereits benutzte Panties und Nachhaltigkeit gehen Hand in Hand. Sie kann unzählige Male wiederverwendet und unisex getragen werden. Somit trägt dieses kleine Kleidungsstück zur Nachhaltigkeit bei. Gebrauchte Panties kaufen und anschließend selbst tragen, ist die Krönung des Recyclings. </p>
  
                @elseif (request('category') == 'schuhe')
                    <h2 class="mt-5 small">Getragene Schuhe kaufen - von der Erfindung zum Fetischartikel</h2>
                    <p>Getragene Schuhe kaufen haben von der Erfindung zum Fetischartikel eine lange Reise hinter sich. Schuhe gibt es seit Tausenden von Jahren. Die ältesten bekannten Treter stammen aus der Steinzeit und sind über 10.000 Jahre alt. Sie bestanden aus einfachen Leder- oder Pflanzenfasern, die die Füße vor Kälte und rauem Untergrund schützten. Tatsächlich gab es lange Zeit keine Unterscheidung zwischen linkem und rechtem Schuh. Die Wende kam erst um 1850, als man begann, diese anatomisch an die Fußform anzupassen. Besonders in Deutschland setzte sich die Unterscheidung schnell durch, da sie für besseren Tragekomfort sorgte. Heute wäre es kaum vorstellbar, zwei identische Schuhe zu tragen. Vielleicht ist es auch deshalb nichts Neues mehr, wenn man bereits getragene Schuhe kaufen möchte. Unabhängig ob dies als Fetischartikel gilt, oder nicht. Getragene Schuhe kaufen hat den Vorteil, dass diese eben perfekt passen und der meist harte Stoff schon durch jemand anderen eingelaufen wurde. </p>
                    <h3 class="small">getragene Sneaker oder getragene Highheels? </h3>
                    <p>Wann hast du davon erfahren, dass man so ein spezielles Produkt erwerben kann? Und jetzt reden wir nicht von einfachen bereits getragene Schuhe für den Alltag, sondern als Fetischartikel. Wir verstehen die Sensibilität dieser Neigung, wenn du dir dreckige Schuhe kaufen möchtest. Wir gewährleisten absolute Diskretion bei jedem Kauf. Ihre Bestellungen werden vertraulich behandelt und in neutraler Verpackung versendet, um deine Privatsphäre vollständig zu schützen. Du brauchst dir nur noch die Frage zu stellen, ob du lieber getragene Sneaker oder lieber doch getragene Highheels möchtest. Übrigens gibt es auch sogenannte Stiefellecker. Das sind diejenigen, die Schuhe mit dem Mund ablecken und sogar reinigen. Oft ist dies ein Spiel von Dominanz und Unterwürfigkeit. Übrigens wurden die ersten Higheels von Männern im 17. Jahrhundert getragen. Anstatt getragene Schuhe Kleinanzeigen zu suchen, kaufe deine Lieblingsteile sicher bei uns. Mit Modder und Schlamm wurde schon so mancher Käufer verwöhnt. Getragene Sneaker kaufen hat den Zweck seine Bedürfnisse und Vorlieben zu erfüllen. </p>
                    <h3 class="small">was benutzte Schuhe über deine Persönlichkeit sagen</h3>
                    <p>Eine Studie hat gezeigt, dass sich die Persönlichkeit eines Menschen, anhand der Wahl der Treter einordnen lässt. Die Forscher baten Teilnehmer, sich benutzte Schuhe von fremden Personen anzusehen und dann deren Alter, Einkommen, Geschlecht und sogar Persönlichkeitsmerkmale einzuschätzen. Überraschenderweise lagen sie in vielen Fällen richtig. Zeig mir deine benutzten Schuhe und ich sage dir, wer du bist. Vielleicht sind die Kunden genau aus dem Grund, auf bestimmte Arten festgelegt. 2 Richtungen sind oft ganz klar ersichtlich. Sexy Schuhe wie zum Beispiel getragene Stiefel kaufen oder sportliche getragene Sneaker. Natürlich gibt es auch diejenigen, die sich nicht festlegen, aber der Großteil der Liebhaber, bevorzugt eine bestimmte Variante. Was benutzte Schuhe über deine Persönlichkeit sagen, ist im Fetischbereich leider noch nicht erforscht. Wir sind uns aber sicher, dass man auch hier bestimmte Persönlichkeiten herausfinden könnte. </p>

                    <section itemscope="" itemtype="https://schema.org/FAQPage">

                        <h2 class="mt-5 small">FAQ zu getragenen Schuhen</h2>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSchuhe1" aria-expanded="true" aria-controls="collapseSchuhe1" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Wieso kaufen Menschen getragene Schuhe?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSchuhe1" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Dies hat 2 Gründe. Zum einen als Alltagsprodukt und zum anderen als Vorliebe. Getragene Schuhe kaufen (ohne Hintergedanken) hat den Vorteil der Second Hand Wirtschaft. Dies ist gut für die Umwelt und schont den Geldbeutel. Darüber hinaus sind diese Schuhe meist schon deutlich von dem Vorbesitzer eingelaufen und es gibt keine lästigen Schmerzen mehr, die neue Schuhe oft mit sich bringen. Zudem sind bereits ausgelatschte Schuhe bei Menschen beliebt, die Fußfehlstellungen wie z. B. den Hallux Valgus haben. Zum anderen ist die intime Vorliebe ein Grund für getragene Schuhe.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSchuhe2" aria-expanded="true" aria-controls="collapseSchuhe2" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Sind getragene Schuhe eklig, Risiko?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSchuhe2" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Ob es eklig ist, hängt in erster Linie von deinem persönlichen Empfingen ab. Wenn du schon vor der Bestellung ein komisches Gefühl hast, dann solltest du es lieber nicht machen. Bereits getragene Schuhe kann man unkompliziert mit Sprays und in der Waschmaschine reinigen, bevor man sie verkauft. Getragene Schuhe haben kein Risiko und "eklig" ist subjektiv. Im Nischenbereich werden die Schuhe nicht gereinigt, denn der Geruch und die Rückstände sind genau das, worauf es ankommt.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSchuhe3" aria-expanded="true" aria-controls="collapseSchuhe3" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Gebrauchte Schuhe online kaufen?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSchuhe3" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Ja, dies ist sogar weit verbreitet. Vinted, Kleinanzeigen und andere Märkte sind oft die 1. Anlaufstelle. Es muss aber ganz klar zwischen einem Alltagsprodukt und einem Fetischverkauf unterschieden werden. Achte bei deinen Käufen auf die Seriosität des Anbieters, die Erfahrungen und sichere Zahlmethoden, wie bei FrauKruner.de. Ein privater Datentausch ist nicht notwendig. Das Rundum-Sorglos-Paket für gebrauchte Schuhe online erhältst du nur bei uns.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSchuhe4" aria-expanded="true" aria-controls="collapseSchuhe4" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Worauf sollte man beim Kauf gebrauchter Schuhe achten?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSchuhe4" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Wenn wir von dem Alltagsprodukt sprechen dann achtest du auf die Erfahrungen, die Produktbeschreibung und auf das Rückgaberecht. Um bei der Größe keinen Fehler zu machen, lass dir die Sohle ausmessen und schaue dir alle Fotos genau an. Bei dem Fetischprodukt ist es egal, da diese meisten nicht mehr selbst getragen werden. Hier geht es primär um die Veredelungen und um das, was zuvor mit dem Produkt gemacht wurde.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSchuhe5" aria-expanded="true" aria-controls="collapseSchuhe5" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Stiefellecker und andere kreative Ideen mit Schuhen
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSchuhe5" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Getragene Schuhe sind mehr als nur Fußbekleidung. Ob Sammlerstück, Kunstobjekt, Vorliebe oder Hobby, die Kreativität kennt keine Grenzen. Der Stiefellecker ist dabei ein Spiel aus Dominanz und Unterwerfung, wie der Name schon sagt. Hochhackige Schuhe und Highheels kommen hier zum Einsatz. Der Begriff ist selbsterklärend und mag polarisieren, gehört aber für manche in den Bereich Roleplay. Auch das Shoeplay gehört in diese Neigung.</p>
                            </div>
                        </div>

                    </section>
                   
                @elseif (request('category') == 'exkret')
                    <h2 class="mt-5 small">Exkretion oder Exkret?</h2>
                   <p>Wir sind bei dem Thema Ausscheidungen. Die Fachausdrücke dafür sind "Exkretion" und "Exkret" und diese sind eng miteinander verbunden. Dennoch beziehen sie sich jedoch auf unterschiedliche Aspekte. Die Exkretion ist der Vorgang der Ausscheidung und das Exkret ist das Produkt. Dies kann Kot, Spucke, Harn, Schweiß, Sperma, Blut, Tränen und vieles mehr sein. Da diese Wörter in erster Linie erschreckend wirken, haben sich im Laufe der Zeit eigene, neue Kreationen, entwickelt. Der Kot wird zu Kaviar und KV, der Harn zu NS, Watersports, Natursekt und golden Shower. Klingt doch eindeutig viel schöner, nicht wahr? </p>
                   <h3 class="small">Beim Körperflüssigkeiten kaufen darf es gerne etwas mehr sein</h3>
                   <p>Das hört sich schon erstmal sehr speziell an. Doch kann man mit solchen Säften den Alltag perfekt ausklingen lassen. Je nach Neigung. Körperflüssigkeiten kaufen ist keine neumodische Erscheinung. Dieses animalische Verhalten, steckt eigentlich in uns allen. Mit dem Duft der Sekrete, kann man die Paarungsbereitschaft des Gegenübers ermitteln. Diese Inhaltsstoffe verraten sehr viel über uns und den zyklusbedingten Stand des Gegenübers. Körperflüssigkeiten austauschen gehörte schon immer zu uns und ist so alt, wie die Menschheit. Heute wird es auch gerne auch als eine Art Challenge einsetzten und wir kommen schnell in den Bereich der Liter. Eine Flasche Natursekt kaufen, ist kein Einzelfall. NS kaufen ist zudem auch Geschmackssache. NS würzig oder NS klar machen einen immensen Unterschied aus. Dazu noch ob du devot oder dominant veranlagt bist und was genau du damit vorhast. Die Golden Shower kaufen oder auch Watersports wird auch als Praktik ausgeübt. Dabei gibt es den aktiven und den passiven Part. Hat man keinen Partner, kann man sich eine Flasche Natursekt oder eine Flasche Urin online kaufen. Natursekt kaufen bietet bemerkenswert viele Möglichkeiten und spannende Erfahrungen. Wusstest du, dass du in Ausnahmesituationen deinen eigenen Urin trinken und somit dein Überleben sichern kannst? In einigen Kulturen ist dies sogar eine jahrhundertalte Tradition. Die Urintherapie. Schädlich ist es sicher nicht. Der Mensch scheidet keinen Stoff aus, der ihm selbst schadet. Beim Körperflüssigkeiten kaufen darf es gerne etwas mehr sein. </p>
                   <h3 class="small">KV kaufen -  Kannst du dir das vorstellen? </h3>
                   <p>Der Ausscheidungsprozess ist etwas ganz Natürliches. Doch was auf den ersten Blick nur als reines Abfallprodukt erscheint, kann in Wirklichkeit wertvolle Verwendung finden. Und Kaviar kaufen ist gewiss kein Einzelfall. Zumindest wird öffentlich nicht darüber gesprochen, da es schnell zu Vorurteilen kommt. Niemand wird sich im Job hinstellen und sagen, dass er KV kaufen ausprobiert hat und dies als aufregend erlebt hat. Die Reaktionen von anderen sind vorprogrammiert. In der Szene ist KV online kaufen schon lange keine Rarität mehr. Offen werden die Wünsche des Kunden besprochen, als wenn es um das Normalste der Welt ginge. Und so soll es sein. Bei Frau Kruner ist es ein gewöhnliches Produkt. KV kaufen, kannst du dir das vorstellen? </p>

                    <section itemscope="" itemtype="https://schema.org/FAQPage">

                        <h2 class="mt-5 small">FAQ zu Exkreten</h2>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExkret1" aria-expanded="true" aria-controls="collapseExkret1" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Was bedeutet Exkret?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseExkret1" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Exkrete (lat.: excernere = ausscheiden) sind vom Körper ausgeschiedenen Stoffe wie z. B. Kot, Speichel, Harn, Schweiß usw. Die Exkretion ist der Akt der Ausscheidung. Es ist die Abgabe von überflüssigen Stoffwechselprodukten, die der Körper nicht mehr braucht.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExkret2" aria-expanded="true" aria-controls="collapseExkret2" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Was ist NS, Watersports, Goldenshower und Natursekt?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseExkret2" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Alle Begriffe meinen das gleiche und zwar den Urin. Da das Wort Urin aber nicht so schön klingt, wurde es durch schönere Begriffe oder Abkürzungen ersetzt.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExkret3" aria-expanded="true" aria-controls="collapseExkret3" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Warum ist NS so beliebt?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseExkret3" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Die Goldenshower ist in der Welt der Nischen sehr bekannt. Der Fetisch, wenn man es so betiteln möchte, ist fast schon ein alltägliches Produkt, mit dem Erfahrungen geteilt und sich ausprobiert wird. Da NS von Natur aus in einem gesunden Körper steril ist, sind die Einsatzmöglichkeiten enorm groß und vermutlich ist NS auch deshalb so beliebt.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExkret4" aria-expanded="true" aria-controls="collapseExkret4" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Was macht man mit Natursekt (NS)?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseExkret4" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Die Spiele mit Natursekt gehen in sehr viele verschiedene Richtungen. Dies kann als Goldenshower- Dusche genutzt werden, um den ganzen Körper damit einzuseifen. Auch als Mutprobe im Spiel aus Dominanz und Unterwerfung findet es Anklang. NS kann sowohl alleine als auch in Gesellschaft angewandt werden. Oft werden Spiele dieser Art auch in Paarbeziehungen ausgelebt. Dabei kann einer entweder nur der aktive oder nur der passiv Part sein, oder beides. Es kann warm auf den Körper verteilt werden, in das Gesicht oder den Mund. Der Fantasie sind keine Grenzen gesetzt. Wichtig ist jedoch, dass keine Grenzen überschritten werden. Vorherige Absprachen sind unerlässlich und niemand darf etwas machen, was den anderen Schaden könnte oder dieser es nicht ausdrücklich erlaubt hat.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExkret5" aria-expanded="true" aria-controls="collapseExkret5" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Sicher Natursekt kaufen, aber wo?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseExkret5" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Anbieter gibt es viele, man könnte es schon fast wie ein Haifischbecken sehen. Doch Vorsicht, nicht jede Plattform ist auch sicher. Das betrifft nicht nur die Bezahlmethoden und den Ablauf sondern auch die Shops der Plattform. Wer Natursekt sicher kaufen möchte, sollte ganz genau hinschauen, welcher Anbieter wirklich seriös ist. Uns ist bewusst, dass sich im Internet viele Fakeprofile tummeln und man nie weiß, woher der NS wirklich kommt. Ein hübsches Profil, sexy Fotos und tolle Produkte, doch wer bedient das Profil wirklich? Oft fangen hier die Probleme an, denn die Fotos sind geklaut und hinter dem Profil steckt im schlimmsten Fall ein Mann. Daher ist es grundlegend wichtig, auf den richtigen Marktplatz zu setzten. Deshalb ist es essenziell, auf den richtigen Natursekt-sicher-kaufen-Anbieter zu setzen. Bei FrauKruner.de ist das ganz eindeutig geregelt: Es melden sich nur Personen an, die ihren Personalausweis und ihre Steuernummer eindeutig verifiziert haben. Zusätzlich sorgen interne Sicherheitsmechanismen dafür, dass kein Profil an Dritte weitergegeben werden kann.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExkret6" aria-expanded="true" aria-controls="collapseExkret6" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Kann man Natursekt trinken und ist es gefährlich?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseExkret6" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Nein. Die hygienischen Standards und regelmäßige gesundheitliche Prüfungen sind Pflicht, um als Produzentin bei FrauKruner.de zu verkaufen. Selbst wenn wir davon ausgehen, dass dieser aufgrund der Lieferzeit länger unterwegs war, dann ist das, was wir normalerweise essen, stärker kontaminiert als Natursekt. Wusstest du eigentlich, dass bereits in alten medizinischen Texten aus Ägypten, Indien und China Urin zur Linderung unterschiedlicher Beschwerden eingesetzt wurde? In der indischen Ayurveda-Lehre gilt die sogenannte Shivambu-Kur als spirituell und körperlich reinigend. Auch im antiken Griechenland, bei Naturheilkundlern der Renaissance sowie in Teilen Afrikas und Südamerikas ist der Einsatz von Natursekt dokumentiert. Die Urintherapie umfasst verschiedene Anwendungen wie z. B. das Trinken von frischem Mittelstrahlurin am Morgen, das Auftragen auf die Haut bei Ekzemen, Akne und Insektenstichen und Spülungen oder Einreibungen bei Gelenkbeschwerden. Das Thema Natursekt ist viel älter als man denkt und Natursektlover kommen heute ohne Bedenken auf ihre Kosten.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExkret7" aria-expanded="true" aria-controls="collapseExkret7" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                KV kaufen anonym
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseExkret7" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">KV steht für Kaviar und hat nichts mit Fischen zu tun. Das braune Ausscheidungsprodukt ist selbst in der Nische, eine Nische, dennoch findet auch dieses ihren Weg in den Mainstream. Die Anwendungsgebiete sind auch hier breit gefächert. KV kaufen kann bei uns anonym durchgeführt werden.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExkret8" aria-expanded="true" aria-controls="collapseExkret8" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Was macht man mit einem benutzten Kondom?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseExkret8" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Ein benutztes Kondom kaufen und auf unterschiedlichste Weise in Fantasien einbinden, ist gar nicht so selten. Manche  bevorzugen es, wenn das Kondom von einer fremden Person befüllt wurde und andere wiederum sammeln das Ejakulat selbst, um es später gezielt in ihr Spiel zu integrieren. Auch in Paarbeziehungen wird es gelegentlich verwendet, um eine zusätzliche, „unsichtbare dritte Person" in die Rollenspiele einzubinden. Der Reiz liegt für viele im Spiel mit Dominanz, Kontrolle, Voyeurismus oder in der bewussten Grenzüberschreitung. In manchen Fällen dient ein benutztes Kondom auch als symbolisches Objekt. Wer ein benutztes Kondom kaufen möchte sollte immer auf die Seriosität des Anbieters achten, denn auch in diesem sensiblen Bereich gilt die Sicherheit und gegenseitiger Respekt stehen an erster Stelle.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExkret9" aria-expanded="true" aria-controls="collapseExkret9" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Therapie mit KI
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseExkret9" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Wenn du Probleme oder Sorgen in diesem oder anderen Bereichen des Lebens hast, kannst du dich nicht nur an das Sorgentelefon wenden, sondern auch die Therapie mit KI hat sich bereits schon jetzt als sinnvoll erwiesen. Du hast einen kostenfreien sofortigen Ansprechpartner, der bereits schon jetzt in vielen Fällen extrem gute Hilfe geleistet hat. Therapieplätze sind Mangelware und manchmal tut auch einfach nur ein Gespräch gut. ChatGPT (LINK https://chatgpt.com/) ist die vorerst beste Wahl, zudem kann er dir die richtigen Ansprechpartner, Dienststellen und Telefonnummern geben. Das Sorgentelefon ist ein weitere Anlaufstellen mit echten Menschen.</p>
                            </div>
                        </div>

                    </section>

                @elseif (request('category') == 'bh')
                    <h2 class="mt-5 small">Ein getragener BH – Faszination und Leidenschaft in einem Kleidungsstück</h2>
                    <p>Das Wort BH ist eine Abkürzung und steht für Büstenhalter oder Busenhalter. Im Alltag tragen BHs eine Menge Verantwortung, sind oft kaum spürbar und bieten der Trägerin einen angenehmes Körpergefühl. Eine solche Erfindung verkörpert Sinnlichkeit, Erotik, und Komfort und kann dazu noch unzählige Designs haben. Verspielter BH, sexy BH, TShirt BH oder mit Comics bedruckt. Ein Sporttop ist übrigens auch eine Art BH. Viele Liebhaber benutzter BH schätzen die Geschichte, dieses Kleidungsstücks. Was macht aber ein Kunde mit dieser Ware? Er kann ihn selbst tragen, einer Doll anziehen oder sich verwöhnen. Wenn du noch mehr Echtheit und Knistern ins Spiel bringen möchtest, solltest du einen getragenen BH kaufen, denn er trägt die Spuren echter Momente und macht die Erfahrung umso intensiver. Ein Kleidungsstück mit Charakter. Der sogenannte Büstenhalter ist im Alltag nicht mehr wegzudenken und er leistet täglich gute Arbeit. Ein getragener BH ist viel mehr als Faszination und Leidenschaft in einem Kleidungsstück. Übrigens ist Berlin</p>
                    <h3 class="small">
                    die BH-freie Hauptstadt
                    </h3>
                    <p>Berlin ist bekannt für seine Freiheit, Offenheit und Individualität. Hier kannst du sein wie und wer du möchtest. Niemand wird dich komisch anschauen. In kaum einer anderen deutschen Stadt sieht man so viele Frauen, die auf einen BH getragen verzichten. In den Sommermonaten scheint es so, als sei es die BH- freie Hauptstadt. Eine ungezwungene freie Zone, in der es wippt, springt und hüpft. Brüste. So natürlich wie sie heute sind, waren sie schon lange nicht mehr. Der Verzicht auf den BH ist hier längst nicht nur ein modisches Statement, sondern auch Ausdruck von Selbstbestimmung und Körperbewusstsein. Viele Frauen genießen die Freiheit, sich nicht den Erwartungen konservativer Dresscodes zu unterwerfen, sondern sich so zu kleiden, oder eben nicht zu kleiden, wie sie es möchten. Doch die Mode geht auch in die andere Richtung. Viele schwören auf ihre Alltagshelden und verwenden den BH getragen einfach obendrüber. Stylische Schnitte sorgen dafür, dass der benutzte BH sichtbar als Statement getragen wird. Es wird so gut verpackt und gekleidet, dass es niemandem auffällt, dass die Wäsche offensichtlich getragen wird. Eher scheint es, ein Accessoire zu sein.  </p>
                    <h3 class="small">Getragene BHs kaufen – diskret und unkompliziert</h3>
                    <p>Wer getragene BHs kaufen möchte, diskret und unkompliziert, muss nicht lange suchen. Angebote gibt es viele, aber wenn es um die Qualität und Seriosität geht, fallen die meisten Anbieter direkt weg. Getragene BHs Frau Kruner ist der sicherste Weg, den man aktuell gehen kann. Qualität, ausgewählte Shops und Authentizität gehen hier Hand in Hand. Erlebe die Anziehungskraft und Magie von diesen wunderbaren einzigartigen Stücken und entdecke eine Welt voller sinnlicher Details. Jetzt einen Bh gebraucht kaufen und eine unvergleichliche Erfahrung genießen.</p>
                @elseif (request('category') == 'video')
                    <h2 class="mt-5 small">Dein persönliches Wunschvideo kaufen</h2>
                    <p>Unsere Welt dreht sich gefühlt immer schneller und somit auch die Art des Konsums und der Befriedigung. Was früher ein Filmchen auf einer Videokassette mit Hintergrundmusik und schlecht synchronisierte Audios war, lässt dich heute dein persönliches Wunschvideo kaufen, dass genau nach deinem Leitfaden erstellt wurde. Ultrascharfe Auflösung und genau die Inhalte, die dich auch interessieren. Wenn du das möchtest, versteht sich. Du kannst auch einfach zwischen unzähligen, bereits vorgefertigten Videos, nach verschiedenen Themen schauen und dich von der Kreativität der Creator inspirieren lassen. Wir haben eine schöne Auswahl an Selbstbefriedigungs Video, Göttin Video oder dem klassischen Tape mit einem Partner. Die exklusiven Crush Video bieten dir ein einzigartiges Erlebnis, das du so nirgendwo anders findest. Das Crushing ist ein relativ neuer Trend, der sich in den letzten Jahren gezeigt hat. Es ist das Zertreten von Gegenständen und Objekten. Oft wird dies mit den nackten Füßen ausgeübt, es können aber auch die Hände oder der ganze Körper eingesetzt werden. Psychologen meinen, dass dies für den Zuschauer eine Art Erleichterung mit sich bringt. Und wir alle kennen es, wenn man eine Coladose zerdrückt, Sand zwischen den Händen gleiten lässt, oder mal vor Wut etwas zerstört. Ein Art Befreiung und ein Aufatmen ist zu spüren. Zusammen mit viel Haut kann das einen besonderen Reiz ausüben. Ob uns dieser Trend noch viele Jahre lang begleitet, werden wir sehen. Falls du auf Ballons stehst, haben wir auch das. Luftballonvideo für dein Erleben. Ein Looner Video, welches nicht nur die Ballons zum Platzen bringt. Ein großer Vorteil zu der damaligen Zeit ist zudem, dass unsere Videos von Amateuren erstellt werden. Keine Fake Shows, keine professionellen Darsteller, keine Inszenierung. Das macht einen entscheidenden Unterschied. </p>
                    <h3 class="small">Die Buntheit von SB Videos kaufen</h3>
                    <p>Die Fülle und die Buntheit von SB Videos kaufen und allem was dazu gehört ist nahezu grenzenlos und lässt das Surfen wie einen Rausch erleben. Ein SB Video ist die Abkürzung für Selbstbefriedigungsvideo. Ein erotischer Clip jagt den nächsten. Jeder weitere Klick, könnte ein weiteres Upgrade sein. Suchst du nach einem speziellen Inhalt, den du im Shop nicht finden kannst, dann kontaktiere den Support! Dein Wunschvideo kann direkt besprochen und für dich eingestellt werden. Für alle weiteren kleinen Änderungen nutze gerne das Kommentarfeld. Dieses hinterlässt der Creatorin eine direkte Nachricht, wenn du Hinweise zu einem Wunschvideo hinterlassen möchtest.</p>
                @elseif (request('category') == 'foto')
                    <h2 class="mt-5 small">Sexy Bilder kaufen ist mehr, als nur ein kurzer Zeitvertreib</h2>
                    <p>Erotik Fotos kaufen haben eine besondere Anziehungskraft, weil sie Emotionen, Erinnerungen und Ästhetik vereinen. Diese sogenannten sexy Bilder, hautsächlich Wunschfotos, sind deshalb so reizvoll, weil sie Momente festhalten und die Fantasie anregen. Suchst du erotische Bilder, die genau auf deine Wünsche abgestimmt sind? Von Fuß Fotos, bis hin zu Crush Fotos, jedes Bild wird mit höchster Qualität produziert und individuell angepasst, um deine Erwartungen zu übertreffen. Um unsere exklusiven FSK 18 Fotos kaufen und genießen zu können, brauchst du nur eine Altersverifizierung und schon kannst du die Inhalte genießen und auf deinen Geräten sichern. So hast du sie immer schnell griffbereit. Vielleicht ist auch genau das der Punkt, weshalb dieses Geschäft so floriert. Die schnelle und unkomplizierte Verfügbarkeit und die Wunscherstellung. Sexy Bilder kaufen ist mehr, als nur ein Zeitvertreib. Es ist auch mehr, als einfach nur ein Bild. Erotik Fotos bieten dir eine Erfahrung, die du so nirgendwo anders findest. Achte bitte stets auf die Vertrauenswürdigkeit der Anbieter und der Produzenten auf anderen Seiten, denn ist schon so mancher Kunde auf unseriösen Plattformen auf Fake reingefallen. Deshalb arbeiten wir nur mit ausgewählten Shops, damit dein Erlebnis kein Reinfall wird. </p>
                    <h3 class="small">Gründe, warum Nacktfotos kaufen so gut funktioniert:</h3>
                    <p>Die Gründe, warum Nacktfotos kaufen so gut funktioniert, ist relativ einfach. Ein gut komponiertes Foto kann Schönheit auf einzigartige Weise einfangen, sei es durch Licht, Farben oder Perspektiven. Besonders nackt Fotos oder Fotos FSK 18 haben eine starke visuelle Wirkung. Sie ermöglichen es, vergängliche Augenblicke für immer zu bewahren. Wunschfotos, Füße-Fotos oder andere kreative Inszenierungen erzählen immer eine andere Geschichte. Besonders individuell beim Nacktbilder kaufen ist die persönliche Note, wenn du hierzu einen Wunsch äußerst. Du kannst dir sicher sein, dass du die einzige Person auf der Welt bist, der diese Bilder besitzt. Dazu noch mit den Wünschen, wie du es am liebsten hast. Durch erotische Fotos kaufen kann sich jeder individuell ausdrücken und Emotionen transportieren. Besonders nackt Fotos erzeugen oft eine direkte, emotionale Reaktion. Ein speziell für dich erstelltes Fotopaket oder ein einzigartiges close up foto (Nahaufnahme) sorgen für eine persönliche Verbindung und hebt sich von Massenproduktionen ab. Welche Art von Fotos fasziniert dich am meisten? </p>
                @elseif (request('category') == 'spielzeug')
                    <h2 class="mt-5 small">Gebrauchtes erotisches Spielzeug - Nachhaltigkeit trifft auf Leidenschaft</h2>
                    <p>Der Markt für gebrauchtes erotisches Spielzeug wächst stetig und bietet eine nachhaltige Alternative zum Neukauf. Viele Menschen schätzen die Möglichkeit, hochwertige Artikel zu einem günstigeren Preis zu erwerben, während sie gleichzeitig einen Beitrag zur Umwelt leisten. Die Nachfrage nach gebrauchtem Erotikspielzeug steigt kontinuierlich, da immer mehr Menschen die Vorteile des Second-Hand-Kaufs erkennen. Von Vibratoren bis hin zu anderen Erotikartikeln - die Auswahl ist vielfältig und bietet für jeden Geschmack etwas Passendes.</p>

                    <section itemscope="" itemtype="https://schema.org/FAQPage">

                        <h2 class="mt-5 small">FAQ zu gebrauchtem erotischem Spielzeug</h2>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpielzeug1" aria-expanded="true" aria-controls="collapseSpielzeug1" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Kann man benutztes erotisches Spielzeug kaufen oder verkaufen?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSpielzeug1" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Ja, unter bestimmten Bedingungen ist der Kauf und Verkauf von benutztem Erotikspielzeug erlaubt, sofern hygienische Standards eingehalten werden und das Produkt in einwandfreiem Zustand ist. Viele Artikel aus Silikon, Metall oder Glas lassen sich professionell reinigen und sind nach fachgerechter Aufbereitung sicher nutzbar. Für den Nischenbereich ist es essenziell, dass das Produkt nach der letzten Benutzung nicht mehr gereinigt wird, denn es geht genau darum.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpielzeug2" aria-expanded="true" aria-controls="collapseSpielzeug2" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Ist ein benutzter Vibrator kaufen legal?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSpielzeug2" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Ja, der Kauf und Verkauf ist legal, sofern der der Artikel nach geltendem Recht keine gesundheitsgefährdenden Mängel aufweist, kein Verstoß gegen Jugendschutzgesetz oder Strafrecht aufweist. Ab dem 16. Lebensjahr ist man eingeschränkt geschäftsfähig und somit ist der Erwerb legal. Es dürfen keine Fotos oder andere Bildaufnahmen mitgeliefert werden. Dies bedarf der Volljährigkeit. Dies gilt nicht nur für den benutzten Vibrator, sondern für alle gebrauchten Erotikspielzeuge dieser Art.</p>
                            </div>
                        </div>

                        <div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpielzeug3" aria-expanded="true" aria-controls="collapseSpielzeug3" itemprop="name">
                                <span class="arrow"><span></span><span></span></span>
                                Ist es eklig, gebrauchtes Sexspielzeug zu kaufen?
                            </button>

                            <div class="collapse_faq__sidebar ms-0 collapse " id="collapseSpielzeug3" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <p itemprop="text">Das ist eine ganz normale Frage und nein, es ist nicht eklig, wenn du gebrauchtes Sexspielzeug kaufen möchtest. Entscheidend ist wo und unter welchen Bedingungen du es erwirbst. Im Alltagsbereich werden die Produkte gründlich desinfiziert, geprüft und nur dann weiterverkauft, wenn das Material eine hygienische Aufbereitung erlaubt. Im Fetischbereich ist das anders, denn hier geht es um die Nicht-Reinigung, da genau die den entsprechenden Reiz ausübt. Dennoch sollten die hygienischen Standards eingehalten werden und sich regelmäßig auf STI´s untersucht werden. Das Produkt darf keine Schäden oder Mängel haben. Der Second-Hand-Kauf ist in jedem Fall nachhaltiger, als der Neukauf, egal aus welchen Gründen.</p>
                            </div>
                        </div>

                    </section>

                @elseif (request('category') == 'sonstiges')
                    <h2 class="mt-5 small">Benutzte Bettwäsche kaufen oder lieber Badezimmermüll kaufen?</h2>
                    <p>In der Kategorie Sonstiges ist alles enthalten, was in keine andere Kategorie passt. Faszinierende Produkte der besonderen Art werden angeboten. Artikel müssen nicht vernichtet werden, wenn man sie nicht mehr braucht oder als Abfall betitelt. Mit ein bisschen Know How lässt sich so manches alte Produkt weiterverwerten. Die Frage, ob man benutzte Bettwäsche kaufen oder lieber Badezimmermüll kaufen sollte, stellt sich nur bedingt. Beides ist sich auf eine spezielle Art sehr ähnlich. So nah und intim kannst du sonst nur wenig, bis gar keinem Menschen sein. Die benutzte Bettwäsche hat den Vorteil, dass sie Nähe auf eine wundersame Weise spenden kann. Sie gibt ein „ich bin bei dir- Gefühl“. Wir alle wissen, wie kuschelig ein bereits eingeschlafenes Bett ist und wenn hier noch die Gerüche von jemand anderen sind, ist das sehr wohlig. Ein getragener Schlafanzug bietet ein ähnliches Gefühl. Die benutzte Bettwäsche kann für viele weitere Zwecke verwendet werden. Und nun zu dem Biomüll. Warum sollte man von jemanden den Müll, explizit den Badezimmermüll kaufen, wenn man diesen genauso gut selbst produzieren kann? Nun ja, wenn man es selbst herstellt, weiß man schon, was drin ist. Es ist der Reiz, der hier die Hauptrolle spielt. Schon allein das Öffnen von dieser extremen Besonderheit ist unschlagbar und erweckt den Entdeckergeist. Was ist drin? Was hat die Person gegessen, konsumiert, wie hat sie gelebt und mit wem? Wenn hier z. B. ein benutztes Kondom oder ein benutzter Tampon enthalten ist, sind die Rückschlüsse ziemlich eindeutig. Es ist ein Spiel aus Neugier und Abenteuerlust, gewürzt mit einem Hauch von Erotik und dem Reiz des Verbotenen. Noch interessanter wird es bei benutzte Ohrenstäbchen oder Watte mit Pupsen geht. All diese Artikel haben das Ziel, jemanden auf eine besondere Weise sehr nahe zu sein. Obwohl es so außergewöhnlich erscheint, ist es längst zu etwas völlig Normalem geworden. Als wäre das Außergewöhnliche inzwischen der neue Standard. </p>
                    <h3 class="small">Die Haare aus der Bürste oder die benutzte Windel - Warum Nischenprodukte so wichtig sind</h3>
                    <p>Die Massenprodukte dominieren die Welt, doch spielen die Nischenprodukte hier die entscheidende Rolle.  Sie bieten nicht nur die Befriedigung sehr spezieller Bedürfnisse, sondern schaffen auch einen sicheren und vertrauenswürdigen Ort für Käufer und Verkäufer. Denn an einem Ort, an dem man sich vollkommen entspannen kann, braucht man sich nur mit der eigentlichen Frage beschäftigen: Nehme ich die Haare aus der Bürste oder die benutzte Windel? Übrigens gibt es diesen Windelfetisch schon einige Jahre. Eine getragene Windel ist auch etwas für Erwachsene und gar nicht so selten. Haare kaufen ist hier schon etwas seltener. Wenn es künstlerischer sein soll, dann ist die Leinwand mit Füßen gemalt, eine gute Alternative. Das Schamhaar kaufen und Fingernägel kaufen, reihen sich direkt ein, neben einem handgeschriebenen Liebesbrief. Das Internet hat uns allen die Türen geöffnet und uns die Chance gegeben, mit allen Sinnen zu erleben. Es ist die Nische in der Nische und egal wie du dich entscheidest, du machst es schon richtig.</p>
                @else
                @endif


                {{-- <div class="aehnliche-produkte">
                    <!-- Products from other categorys (max-3)-->
                    <h3>Ähnliche Produkte</h3>

                    <div class="product-list-shop">

                        <!-- Products from other categorys-->
                        <div class="product-list-shop__product">
                            <a href="/single-product-page.html">
                                <div class="product-list-shop__product__image">
                                    <img data-src="img/sample-images/IMG_3881.jpg" class="lazy" alt="Product name">
                                </div>
                                <div class="product-list-shop__product__content">
                                    <p class="text-uppercase">Slips</p>
                                    <p>Schöner duftende Socken die bis zum Himmel riechen und noch viel höher 🥰 </p>
                                    <span class="price">29,99€</span>
                                </div>
                            </a>
                        </div>
                        <!-- Products from other categorys-->

                        <!-- Products from other categorys-->
                        <div class="product-list-shop__product">
                            <a href="/single-product-page.html">
                                <div class="product-list-shop__product__image">
                                    <img data-src="img/sample-images/IMG_3881.jpg" class="lazy" alt="Product name">
                                </div>
                                <div class="product-list-shop__product__content">
                                    <p class="text-uppercase">Slips</p>
                                    <p>Schöner duftende Socken die bis zum Himmel riechen und noch viel höher 🥰 </p>
                                    <span class="price">29,99€</span>
                                </div>
                            </a>
                        </div>
                        <!-- Products from other categorys-->

                        <!-- Products from other categorys-->
                        <div class="product-list-shop__product">
                            <a href="/single-product-page.html">
                                <div class="product-list-shop__product__image">
                                    <img data-src="img/sample-images/IMG_3881.jpg" class="lazy" alt="Product name">
                                </div>
                                <div class="product-list-shop__product__content">
                                    <p class="text-uppercase">Slips</p>
                                    <p>Schöner duftende Socken die bis zum Himmel riechen und noch viel höher 🥰 </p>
                                    <span class="price">29,99€</span>
                                </div>
                            </a>
                        </div>
                        <!-- Products from other categorys-->


                    </div>
                </div> --}}


            </div>
            <!-- Products-->
        </div>
    </section>

    <script src="{{ asset('assets/js/jquery.js') }}" crossorigin="anonymous"></script>

    <script>
        function filterSecond(e, peram) {
            var currentUrl = window.location.href;
            var url = new URL(currentUrl);

            url.searchParams.set(peram, e);
            url.searchParams.delete("page");
            // console.log(e);

            //price
            var newUrl = url.href;
            window.location = newUrl;
        }
    </script>
    <script>
        function get_filter(class_name) {
            var filter = [];
            $('.' + class_name + ':checked').each(function() {
                filter.push($(this).val());
            });
            return String(filter);
        }
        $('.common_selector').click(function() {
            filter();
        });

        function filter() {
            let finishings = get_filter('finishings');
            let additions = get_filter('additions');
            let time = get_filter('time');
            let short = get_filter('short');
            let category = get_filter('category');
            var currentUrl = window.location.href;
            var url = new URL(currentUrl);
            console.log('url.searchParams')
            //finishings
            if (finishings) {
                url.searchParams.set("finishings", finishings);
                url.searchParams.delete("page");
            } else {
                url.searchParams.delete("finishings");
            }
            //additions
            if (additions) {
                url.searchParams.set("additions", additions);
                url.searchParams.delete("page");
            } else {
                url.searchParams.delete("additions");
            }
            //time
            if (time) {
                url.searchParams.set("time", time);
                url.searchParams.delete("page");
            } else {
                url.searchParams.delete("time");
            }
            //category
            if (category) {
                url.searchParams.set("category", category);
                url.searchParams.delete("page");
            } else {

                url.searchParams.delete("category");
            }
            //price
            var newUrl = url.href;
            window.location = newUrl;
        }
    </script>

</x-front_app>
