<x-front_app>
    @php
    $title = 'FrauKruner - Getragene Unterwäsche ist mehr als nur ein Fetisch';
    $description = 'Getragene Unterwäsche kommt ohne Smalltalk und Kommunikation aus und ist dabei noch schonend zur Umwelt. Ein handmade Alleskönner, vom Glücksbringer bis zum Recycling und weite aus mehr, als nur ein Fetisch.';
    @endphp

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
                                <p class="h5 modal-title text-secondary" id="kategorienModalLabel">Kategorien</p>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="sorting-list-non-collapsing">
                                    <ul>
                                        <li class="">
                                             <a href="{{ route('shop') }}">Alle Kategorien</a>
                                        </li>
                                        <li class="current-menu-item" >
                                                <a
                                                href="/getragene-unterwaesche-kaufen">Unterwäsche</a>
                                            
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
                                <p class="h5 modal-title text-secondary" id="filterModalLabel">Filter <span style="margin-left:15px;"><a
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

                                <div class="sorting-list-collapsing">
                                    <button class="sidebar-collapse-button-closed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapseBewertung"
                                        aria-expanded="false" aria-controls="collapseBewertung">
                                        <span class="arrow"><span></span><span></span></span> Kategorien
                                    </button>

                                    <div class="collapse collapse__sidebar" id="collapseBewertung">
                                        <ul>
                                        <li class="">
                                             <a href="{{ route('shop') }}">Alle Kategorien</a>
                                        </li>
                                        <li class="current-menu-item" >
                                                <a
                                                href="/getragene-unterwaesche-kaufen">Unterwäsche</a>
                                            
                                        </li>
                                            @foreach ($categories as $category)
                                                <li class=""><a
                                                        href="javascript::void(0)" onclick="filterSecond('{{$category->slug}}','category')">{{ $category->name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>

                                    </div>
                                </div>
                                
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
                        <li class="">
                                             <a href="{{ route('shop') }}">Alle Kategorien</a>
                                        </li>
                                        <li class="current-menu-item" >
                                                <a
                                                href="/getragene-unterwaesche-kaufen">Unterwäsche</a>
                                            
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
            <h1>Getragene Unterwäsche kaufen</h1>
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


<section class="mt-5 mb-5" itemscope="" itemtype="https://schema.org/FAQPage">

<h2 class="mt-5 small">Getragene Unterwäsche kaufen - eine kleine Geschichte</h2>
<p>Unterwäsche ist ein Bestandteil unserer täglichen Kleidung. Ein Stück, das Komfort, Funktionalität und Stil vereint und den Intimbereich vor Reizungen schützt. Das tägliche Kleidungsstück hat sich im Laufe der Geschichte zu einem unverzichtbaren Begleiter entwickelt. Diese reicht bis in die Antike zurück. Man benutzte früher Leinen und Tücher, um den Schritt zu bedecken. Die älteste noch erhaltene getragene Unterwäsche ist aus dem 15. Jahrhundert. Im Laufe der Zeit hat sie sich zu dem entwickelt, wie wir sie heute kennen. Dazu im Vergleich ist getragene Unterwäsche kaufen eine kurze Geschichte. Aber dieser Abzweig erfreut sich immer mehr wachsender Beliebtheit und bietet zahlreichen Möglichkeiten. Getragene Unteräsche kaufen ist mehr als nur ein Fetisch. Es ist ein sinnliches Erlebnis, das Nähe auf eine außergewöhnliche Weise ermöglicht. </p>

<h3 class="small">gebrauchte Unterwäsche – wer kauft sowas?</h3>
<p>Hauptsächlich ist das Geschäft von Frau zu Mann, aber Ausnahmen gibt es viele. Den typischen Käufer gibt es nicht und auf der Straße würde ihn niemand erkennen. Er ist der Mann von nebenan, es ist die Frau von nebenan. Gebrauchte Unterwäsche zieht sich durch alle Gesellschaftsschichten und durch jedes Alter. Und warum ist es so beliebt? Gebrauchte Unterwäsche kaufen geht ohne ein Kennenlernen, ohne Smalltalk, Kommunikation, ja ohne aus dem Haus zu gehen. Es ist einfach, schnell, sicher und ohne viel Blabla. Getragene Männer Unterwäsche ist besonders bei Kunden gefragt, die nach maskulinen und authentischen Stücken suchen. Wusstest du, dass eine Studie zeigt, dass Männer, die Boxershorts tragen, eine höhere Spermienqualität haben als diejenigen, die enge Shorts tragen? Der Grund ist die bessere Belüftung. Weite Shorts sind gewiss nicht mehr zeitgemäß, aber wenn es gesund ist, dann drücken wir ein Auge zu. Also zurück zum Thema gebrauchte Unterwäsche, wer kauft das? Es kann jeder sein. Ein Thema das so individuell ist, wie wir. </p>

<h3 class="small">Wie getragene Unterwäsche zum Recyclen beiträgt </h3>
<p>Da getragene Unterwäsche wiederverwendet wird, spielt sie unserer Umwelt in die Karten. Es trägt zur Reduzierung von Abfall bei. Diese Art von Produkt ist keinesfalls ein Einmalprodukt. Oft wird bereits getragene Unterwäsche selbst, oder zum Beispiel Puppen angezogen. Manch ein Kunde trägt diese sogar als eine Art Glücksbringer bei sich. Somit ist es plausibel, wie getragene Unterwäsche zum Recyclen beiträgt. Oftmals leben diese Produkte noch Jahre weiter. Eine andere Art zur Unterstützung der Natur ist das jegliche weglassen von Unterwäsche. Dies war bereits vor einigen Jahren Trend und nannte sich „going commando“. Wie auch immer du dich entscheidest, mit deiner Lieblingskreation kannst du dich einfach ausleben. Getragene Unterwäsche online kaufen ist heute so einfach. Direkt von deinem Wohnzimmer aus, kannst du getragene Damenunterwäsche kaufen und trägst damit auch einen Teil zur Nachhaltigkeit bei.</p>

<h3 class="small">Benutzte Unterwäsche kaufen – ein abenteuerliches Highlight</h3>
<p>Benutzte Unterwäsche ist nicht nur ein Fetisch, sondern hilft auch, seine Sexualität besser zu verstehen und sich ohne Schuldgefühle oder Scham zu akzeptieren. Viele Menschen sind von der Intimität und der Einzigartigkeit solcher Artikel fasziniert. Für diese Ware, die man nicht im Laden kaufen kann, gibt es zahlreiche Plattformen. Dies betrifft zum Beispiel getragene Unterwäsche ebay und auch getragene Unterwäsche Kleinanzeigen. Doch Vorsicht, hier sind viele Scammer unterwegs, die nur schreiben wollen. Wirkliches Interesse ist hier selten. Zudem besteht für beide Seiten immer das Problem mit dem Austausch der Daten. Von getragene Unterwäsche ebay und Co. raten wir dir ab, wenn du sicher bleiben möchtest. Das gleiche gilt auch für getragene Unterwäsche Vinted. Der Direktverkauf hat große Nachteile. Benutzte Unterwäsche kaufen ist ein abenteuerliches Highlight, wenn du auf die richtige Wahl der Anbieter achtest. </p>

<h2 class="mt-5 small">FAQ zur Unterwäsche</h2>

<div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
	<button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUNterwaesche1" aria-expanded="true" aria-controls="collapseUNterwaesche1" itemprop="name">
		<span class="arrow"><span></span><span></span></span>
Was ist getragene Unterwäsche?

</button>

	<div class="collapse_faq__sidebar ms-0 collapse " id="collapseUNterwaesche1" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		<p itemprop="text">Getragene Unterwäsche bezieht sich auf Unterwäsche, die von einer Person getragen wurde, bevor sie verkauft wird. Diese Produkte sind häufig in Nischenmärkten von Interesse. Sie tragen den unsichtbaren und oft anziehenden Duft der Pheromone in sich. Der Begriff "getragene Unterwäsche" umfasst eine Vielzahl von persönlichen Produkten, die nicht nur die Wäsche beinhalten. </p>
	</div>
</div>


<div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
	<button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLegal" aria-expanded="true" aria-controls="collapseLegal" itemprop="name">
		<span class="arrow"><span></span><span></span></span>
Ist der Kauf von getragener Unterwäsche legal?

</button>

	<div class="collapse_faq__sidebar ms-0 collapse " id="collapseLegal" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		<p itemprop="text">Der Kauf von getragener Unterwäsche ist in Deutschland legal, solange er nicht gegen lokale Gesetze bezüglich Hygiene, Betrug oder sexueller Belästigung verstößt. Mit Erreichen des 16 Lebensjahrs ist man eingeschränkt geschäftsfähig und darf diese und ähnliche Produkte erwerben. In anderen Ländern gelten möglicherweise andere Vorschriften. </p>
	</div>
</div>


<div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
	<button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePreis" aria-expanded="true" aria-controls="collapsePreis" itemprop="name">
		<span class="arrow"><span></span><span></span></span>
Wie viel kostet ein getragener Slip?

</button>

	<div class="collapse_faq__sidebar ms-0 collapse " id="collapsePreis" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		<p itemprop="text">Der Preis für diese und ähnliche Produkte variiert je nach Art des Artikels, Marke, Zustand, Tragetage, Veredelungen und Verkäufer*in. In der Regel liegt der Basispreis für ein no Name Produkt zwischen 20 - 40 €. </p>
	</div>
</div>


<div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
	<button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKaufen" aria-expanded="true" aria-controls="collapseKaufen" itemprop="name">
		<span class="arrow"><span></span><span></span></span>
Wo kann ich getragene Unterwäsche kaufen?

</button>

	<div class="collapse_faq__sidebar ms-0 collapse " id="collapseKaufen" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		<p itemprop="text">Es gibt spezialisierte Plattformen und Online-Marktplätze, auf denen diese und andere Produkte gekauft werden können. Achte darauf, nur vertrauenswürdige und sichere Seiten zu wählen wie FrauKruner.de. Bei uns kannst du diskret einkaufen und anonym verkaufen.</p>
	</div>
</div>


<div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
	<button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRatgeber" aria-expanded="true" aria-controls="collapseRatgeber" itemprop="name">
		<span class="arrow"><span></span><span></span></span>
Welche getragene Unterwäsche ist die richtige für mich? 
</button>

	<div class="collapse_faq__sidebar ms-0 collapse " id="collapseRatgeber" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		<p itemprop="text">Wenn du keinerlei Erfahrungen hast und es gerne mal ausprobieren möchtest, könnte die Wahl des Produktes unter der Vielzahl von Angeboten und Artikelarten schwierig sein. Damit du aber ganz sicher das richtige Produkte für dich findest, schaue dir diesen ausführlichen Ratgeber an:
<a href="https://fraukruner.de/Neuigkeiten/getragene-unterwasche-so-findest-du-die-richtigen-tragetage" target="_blank" title="getragene Unterwäsche so findest du die richtigen Tragetage">hier</a></p>
	</div>
</div>


<div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
	<button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReinigen" aria-expanded="true" aria-controls="collapseReinigen" itemprop="name">
		<span class="arrow"><span></span><span></span></span>
Wie reinigt man Unterwäsche, bevor man sie verkauft?
</button>

	<div class="collapse_faq__sidebar ms-0 collapse " id="collapseReinigen" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		<p itemprop="text">Die Unterwäsche für den intimen Verkauf sollte ohne Weichspüler gewaschen werden, da dieser künstlichen Duft in die Wäsche einarbeitet. Nach dem Tragen und Veredeln wird die Wäsche nicht mehr gewaschen, sondern nur vakuumiert (luftdicht versiegelt). Verkaufst du Wäsche nicht als intimes Produkt, dann kannst du diese bei 60 C° bis 90 C° waschen, um sie weiterzugeben. Es ist wichtig, dass die hygienischen Standards eingehalten werden, unabhängig ob das Produkt als intimes Geschenk gilt, oder nicht. Eine regelmäßige Überprüfung nach STI´s ist in beiden Fällen essenziell. </p>
	</div>
</div>



<div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
	<button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWarum" aria-expanded="true" aria-controls="collapseWarum" itemprop="name">
		<span class="arrow"><span></span><span></span></span>
Warum kaufen Menschen getragene Unterwäsche?
</button>

	<div class="collapse_faq__sidebar ms-0 collapse " id="collapseWarum" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		<p itemprop="text">Es gibt verschiedene Gründe, warum Menschen benutzte Wäsche kaufen. Einige tun dies aus sexuellen oder Fetisch-Gründen, dem Verlangen nach Nähe, andere suchen nach einem emotionalen oder sensorischen Erlebnis. Es ist ein sehr persönliches Thema, das oft mit der Vorstellung von Intimität und persönlichen Vorlieben verbunden ist. Es ist leicht und ohne viel Aufwand zu beschaffen und nicht an Bedingungen geknüpft. </p>
	</div>
</div>


<div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
	<button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSicher" aria-expanded="true" aria-controls="collapseSicher" itemprop="name">
		<span class="arrow"><span></span><span></span></span>
Ist es sicher, getragene Wäsche zu kaufen?
</button>

	<div class="collapse_faq__sidebar ms-0 collapse " id="collapseSicher" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		<p itemprop="text">Ja, wenn man sie von einem sicheren Markplatz für getragene Unterwäsche wie Fraukruner.de kauft. Der Datenschutz und die Anonymität sind von großer Bedeutung für uns. Persönlichen Daten sind ausschließlich nur dem Team von FrauKruner.de sichtbar. Einkäufe können diskret durchgeführt werden. Tipp: Viele Käufer verwenden eine Packstation oder andere anonyme Lieferstellen.</p>
	</div>
</div>


<div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
	<button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBeliebtesten" aria-expanded="true" aria-controls="collapseBeliebtesten" itemprop="name">
		<span class="arrow"><span></span><span></span></span>
Welche Arten von Unterwäsche sind am beliebtesten?
</button>

	<div class="collapse_faq__sidebar ms-0 collapse " id="collapseBeliebtesten" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		<p itemprop="text">Am beliebtesten sind Slips, gefolgt von benutzten Panties und Strings. Slips und Panties haben einen größeren Zwickel, der mehr Inhalte aufnehmen kann. </p>
	</div>
</div>



<div class="faq-list-collapsing mb-0 ps-0" itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question">
	<button class="faq-collapse-button-closed ps-0 h3 small mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrivatverkauf" aria-expanded="true" aria-controls="collapsePrivatverkauf" itemprop="name">
		<span class="arrow"><span></span><span></span></span>
Ist getragene Unterwäsche ein Privatverkauf?
</button>

	<div class="collapse_faq__sidebar ms-0 collapse " id="collapsePrivatverkauf" style="" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		<p itemprop="text">Nein. Da du nicht einfach deine alte Kleidung verkaufst, sondern mit den Produkten etwas herstellst, bist du auch Hersteller*in von etwas. Somit handelst du immer gewerblich. Genaueres erfährst du hier: <a href="https://fraukruner.de/Neuigkeiten/privatverkauf-worauf-muss-ich-achten" target="_blank" title="Ist getragene Unterwäsche ein Privatverkauf?">hier</a></p>
	</div>
</div>



</section>


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
