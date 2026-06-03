@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/choice.min.css') }}">
    <style>
        input[type="file"] {
            display: none;
        }

        input:disabled {
            background-color: #ecedf0;
            border: 1px solid #ecedf0;
        }

        .custom-file-upload {
            display: inline-block;
            cursor: pointer !important;
        }


        .choices {
            margin-bottom: 0px !important;
        }

        .choices__inner {
            border: none !important;
            padding: 0px !important;
            background-color: transparent !important;
        }

        .choices__list--multiple .choices__item {
            background-color: #122253 !important;
            border: none;
        }

        .choices[data-type*=select-multiple] .choices__button,
        .choices[data-type*=text] .choices__button {
            border-left: 1px solid #fff !important;
        }

        .choices__input {
            background-color: transparent !important;
        }

        .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: transparent !important;
        }
    </style>
@endpush
<div class="row">
    <div class="col-12 col-md-6 mt-4">
        <h5 class="small">
            <details data-popover="up">
                <summary>?</summary>
                <div class="popoverBody">
                    Nenne hier kurz oder in einem Schlagwort deinen Artikel. Beispiel: String
                </div>
            </details>Produktname
        </h5>
        <input type="text" id="name" class="@error('name') is-invalid @enderror" name="name"
            value="{{ old('name', $product->name) }}">
        <!-- Value Only for Developer Purpose-->
        @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>


    <div class="col-12 col-md-6 mt-4">
        <h5 class="small">
            <details data-popover="up">
                <summary>?</summary>
                <div class="popoverBody">
                    Kategorie des Produkts.
                </div>
            </details>Kategorie
        </h5>
        <select name="category" id="category" class="select-classic @error('category') is-invalid @enderror">

            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @if ($category->id == $product->category_id) selected @endif>
                    {{ $category->name }}
                </option>
            @endforeach


        </select>
        @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>


    <div class="col-12 col-md-6 mt-4">
        <h5 class="small">
            <details data-popover="up">
                <summary>?</summary>
                <div class="popoverBody">
                    Hier kannst du deinen Artikel etwas genauer beschreiben, damit der Käufer sieht,
                    was er von dir erhalten könnte. Bsp. schwarzer feuchter Spitzenstring.
                </div>
            </details>Kurzbeschreibung
        </h5>
        <input type="text" id="details" name="details" value="{!! old('details', $product->details) !!}"
            class="@error('details') is-invalid @enderror">
        @error('details')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <!-- Value Only for Developer Purpose-->
    </div>

    <div class="col-12 col-md-6 mt-4">
        <h5 class="small">
            <details data-popover="up">
                <summary>?</summary>
                <div class="popoverBody">
                    Hier kannst du alle Schlagworte nennen, damit dein Produkt gefunden werden kann und Reichweite
                    bekommt. Bitte verwende nur Tags, die auch wirklich zu deinem Artikel passen.
                    (Verwende z.B. nicht den Hashtag Adidas um mehr Reichweite zu bekommen, wenn es keine Adidassocken
                    Socken sind. Du könntest einen Käufer täuschen, der vielleicht das Original sucht).

                </div>
            </details>Tags
        </h5>
        {{-- <input type="text" id="tags" name="tags" value="{{ old('tags', $product->tags) }}"
        class="@error('tags') is-invalid @enderror"> --}}
        @php
            $selectedTags = json_decode($product->tags);
            if ($selectedTags) {
                $tags = $tags->pluck('name')->diff($selectedTags);
            }

        @endphp

        <select name="tags[]" id="tags" class="select-classic @error('tags') is-invalid @enderror"
            @if ($product->tags)  @endif multiple>
            @if (!$selectedTags)
                @foreach ($tags as $tag)
                    <option value="{{ $tag->name }}">
                        {{ $tag->name }}
                    </option>
                @endforeach
            @else
                @foreach ($selectedTags as $key => $item)
                    <option {{ $item ? 'selected' : '' }}>{{ $item }}
                    </option>
                @endforeach

                @foreach ($tags as $tag)
                    <option value="{{ $tag }}">
                        {{ $tag }}
                    </option>
                @endforeach
            @endif


        </select>
        @error('tags')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror

    </div>

    <div class="col-12 col-md-12 mt-4">
        <h5 class="small">
            <details data-popover="up">
                <summary>?</summary>
                <div class="popoverBody">
                    Schreibe hier alles, was du zu deinem Angebot zu sagen hast. Beschreibe deinen Artikel, deine
                    Veredelungen, erzähle über deine Fotos und Videos und andere Details, damit keine Fragen offen sind.
                </div>
            </details>Beschreibung</h4>
            <textarea name="description" id="description" class="@error('description') is-invalid @enderror">{!! old('description', $product->description) !!}</textarea>
            @error('description')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
            <!-- Value Only for Developer Purpose-->
    </div>
    <div class="col-12" id="forms">

    </div>

    <div class="col-12 mt-4">

        <h5 class="small">
            <details data-popover="up">
                <summary>?</summary>
                <div class="popoverBody">
                    Achte bitte darauf, dass das Anzeigebild FSK 16 sein muss. Dies ist für alle
                    sichtbar. Weitere Bilder zu einem Artikel können FSK 18 sein. Diese sind nur für
                    altersgeprüfte User sichtbar. Wenn du es genauer wissen möchtest, lese bitte
                    hier die <a href="/page/nutzungsbedingungen">Richtlinien</a>.
                </div>
            </details>Fotos hochladen
        </h5>
        <div class="p-3" style="border:1px solid #B2B2B2;">
            <div class="d-flex">
                @if ($product->image)
                    <div class="pb-2 pt-2">
                        <img src="{{ media_url($product->image) }}" height="80px" alt="">
                    </div>
                @endif

                <div class="p-2">
                    <div class="fw-bold">
                        <span>Hauptbild</span>
                        <br>
                        <label class="custom-file-upload mt-3 btn btn-outline-secondary" for="d-image">
                            <input type="file" class="form-control p-3" id="d-image"
                                style="border:1px solid #B2B2B2;" name="thumbnail"
                                {{ $product->image ? '' : 'required' }}>
                            <i class="fa fa-upload" aria-hidden="true"></i>
                            Foto hochladen
                        </label>

                    </div>

                </div>

            </div>
            <hr>
            <div>


                <div class="d-flex justify-content-between align-items-center fw-bold">
                    <label for="a-image" class="mt-2">Zusätzliche Fotos</label>
                    <button type="button" onclick="addRow()" class="btn btn-secondary btn-sm"><i
                            class="fa fa-plus"></i></button>
                </div>
                <div id="images">
                    @foreach ($product->images as $image)
                        <div
                            class="d-flex justify-content-start justify-content-md-between align-items-start align-items-md-center flex-column flex-md-row py-2">
                            <div
                                class="d-flex w-100 justify-content-between  justify-content-md-start align-items-center">

                                <label class="custom-file-upload mt-3 btn btn-outline-secondary"
                                    for="image{{ $loop->iteration }}">
                                    <input type="file" id="image{{ $loop->iteration }}"
                                        name="images[{{ $loop->iteration }}][image]" class="form-control p-3"
                                        style="display: none;">
                                    <i class="fa fa-upload" aria-hidden="true"></i> Foto hochladen
                                </label>
                                <input type="hidden" name="images[{{ $loop->iteration }}][id]"
                                    value="{{ $image->id }}">

                                <a href="{{ media_url($image->image) }}"
                                    class="m-3 bild-ansehen-form-produkt-einstellen" style="margin-left:20px"
                                    target="_blank" title="Bild ansehen">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="23.701" height="17.51"
                                        viewBox="0 0 23.701 17.51">
                                        <g id="Icon_feather-eye" data-name="Icon feather-eye"
                                            transform="translate(-1 -5.5)">
                                            <path id="Pfad_1619" data-name="Pfad 1619"
                                                d="M1.5,14.255S5.627,6,12.85,6,24.2,14.255,24.2,14.255,20.073,22.51,12.85,22.51,1.5,14.255,1.5,14.255Z"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="1" />
                                            <path id="Pfad_1620" data-name="Pfad 1620"
                                                d="M19.691,16.6a3.1,3.1,0,1,1-3.1-3.1A3.1,3.1,0,0,1,19.691,16.6Z"
                                                transform="translate(-3.745 -2.341)" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                                        </g>
                                    </svg>

                                </a>
                            </div>
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div class="m-3">
                                    <input value="1" type="checkbox"
                                        name="images[{{ $loop->iteration }}][nsfw]" id="nsfw{{ $loop->iteration }}"
                                        @if ($image->nsfw) checked @endif>
                                    <label for="nsfw{{ $loop->iteration }}">
                                        FSK 18
                                    </label>
                                </div>
                                <button class="bild-ansehen-form-produkt-einstellen m-3" onclick="removeRow(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13.707" height="13.707"
                                        viewBox="0 0 13.707 13.707">
                                        <g id="Gruppe_1628" data-name="Gruppe 1628"
                                            transform="translate(-1338.146 -348.253)">
                                            <line id="Linie_229" data-name="Linie 229" x2="13" y2="13"
                                                transform="translate(1338.5 348.607)" fill="none"
                                                stroke-width="1"></line>
                                            <line id="Linie_230" data-name="Linie 230" x2="13" y2="13"
                                                transform="translate(1351.5 348.607) rotate(90)" fill="none"
                                                stroke-width="1"></line>
                                        </g>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>

    <div class="col-12 mt-4" id="additionsDiv">


        <div class="border-profile">
            <div class="accordion accordion-flush accordion-single-product-placement" id="accordionVeredlungen">
                <div class="accordion-item">
                    <h5 class="accordion-header small" id="flush-headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#flush-collapseOne" aria-expanded="false"
                            aria-controls="flush-collapseOne">
                            Veredelungen
                        </button>
                    </h5>
                    <div id="flush-collapseOne" class="accordion-collapse collapse"
                        aria-labelledby="flush-headingOne" data-bs-parent="#accordionVeredlungen">
                        <div class="accordion-body">
                            <h5 class="small">
                                <details data-popover="up">
                                    <summary>?</summary>
                                    <div class="popoverBody">
                                        Wähle zu jedem Artikel die Veredelungen, die du anbieten möchtest. Zusätzlich
                                        schreibst du den Aufpreis in das entsprechende Feld daneben.
                                    </div>
                                </details>Weitere Informationen
                            </h5>

                            <div class="veredelungen-dropdown" id="totalFinishing">
                                <ul>
                                    @foreach ($finishings as $finishing)
                                        <li>
                                            <input id="finishing-{{ $finishing->id }}"
                                                @if (array_key_exists($finishing->name, $product->finishings)) checked @endif
                                                onclick="finish({{ $finishing->id }})" type="checkbox" /><label
                                                for="finishing-{{ $finishing->id }}">{{ $finishing->name }}
                                            </label> <input required type="number" min="0"
                                                id="finishngPrice{{ $finishing->id }}"
                                                name="finishings[{{ $finishing->name }}]"
                                                value="{{ old(
                                                    " finishings[$finishing->name
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ]",
                                                    @$product->finishings[$finishing->name],
                                                ) }}"
                                                class="price-info-veredelung" disabled><span class="currency">€</span>

                                        </li>
                                    @endforeach



                                </ul>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
    <div class="col-12 mt-4">

        <div class="row">

            <div class="col-12 col-md-6 mb-5" id="wearingDiv">
                <h5 class="small mt-4 mt-md-0 @error('wearing_time') text-danger @enderror">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Trage ein, wie lange du bereit wärest, z.B. einen Slip zu tragen und lege den
                            Preis fest. <br>
                            Bsp. 3 Tage 20,-<br>
                            2 Tage 10,-

                        </div>
                    </details>Tragedauer
                </h5>
                <div class="border-profile">
                    <div class="veredelungen-field " id="totalWearing">
                        <ul>
                            @foreach ($wearingTimes as $wearingTime)
                                <li>
                                    <input id="checkboxWear-{{ $wearingTime->id }}"
                                        @if (array_key_exists($wearingTime->name, $product->wearing_time)) checked @endif
                                        onclick="wear({{ $wearingTime->id }})" type="checkbox" />
                                    <label for="checkboxWear-{{ $wearingTime->id }}">{{ $wearingTime->name }}</label>

                                    <input type="number" min="0" id="wear-{{ $wearingTime->id }}"
                                        value="{{ old("wearingTime[$wearingTime->name]", @$product->wearing_time[$wearingTime->name]) }}"
                                        name="wearing_time[{{ $wearingTime->name }}]" class="price-info-veredelung"
                                        @if (!array_key_exists($wearingTime->name, $product->wearing_time)) disabled @endif>
                                    <span class="currency">€</span>
                                </li>
                            @endforeach



                        </ul>
                        @error('wearing_time')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>Mindestens ein Tag Tragedauer wird benötigt.</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 mb-5" id="addtionGroup">
                <h5 class="small mt-4 mt-md-0 @error('addition') text-danger @enderror">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Du möchtest Bildmaterial anbieten? Dann fülle das Feld aus und notiere zusätzlich in der
                            Beschreibung, was der Käufer von dir erhalten wird. Beispiel: 5 Minuten langes Video mit
                            Strip, dirty talk und close-up.
                        </div>
                    </details>Zusätze
                </h5>
                <div class="border-profile">
                    <div class="veredelungen-field" id="totalAddition">
                        <ul>
                            @foreach ($additions as $addition)
                                <li><input id="additionCheckbox-{{ $addition->id }}"
                                        @if (array_key_exists($addition->name, $product->addition)) checked @endif
                                        onclick="addition({{ $addition->id }})" type="checkbox" /><label
                                        for="additionCheckbox-{{ $addition->id }}">{{ $addition->name }}</label>
                                    <input type="number" min="0" required id="addition-{{ $addition->id }}"
                                        name="addition[{{ $addition->name }}]"
                                        value="{{ old(
                                            "
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                addition[$addition->name ]",
                                            @$product->addition[$addition->name],
                                        ) }}"
                                        class="price-info-veredelung" disabled><span class="currency">€</span>
                                </li>
                            @endforeach


                            @error('addition')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror

                        </ul>
                    </div>
                </div>
            </div>
        </div>




        <div class="row">

            <div class="col-12 ">

                <h5 class="small">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Sollte es sich bei deinem Artikel um ein Einzelstück handeln, klicke bitte NEIN an. Der
                            Artikel wird direkt nach dem Verkauf aus dem Shop rausgenommen, um einen mehrfachen Verkauf
                            zu vermeiden.
                        </div>
                    </details>Artikel mehrfach vorhanden?
                </h5>
                <div class="preis-angaben">
                    <input class="options radio" type="radio" value="0" name="selloption" id="yes"
                        {{ $product->selloption ? '' : 'checked' }}>
                    <label for="yes">Nein</label>
                    <input class="options radio ms-5" type="radio" value="1" name="selloption"
                        id="no" {{ $product->selloption ? 'checked' : '' }}>
                    <label for="no">Ja</label>
                    @error('selloption')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>


        </div>
        <div class="row">

            <div class="col-12 col-md-6">

                <h5 class="small mt-5">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Hier gibst du den Preis deines Artikels ohne zusätzliche Extras an. Wenn ein Käufer
                            Zusatzoptionen auswählt, werden diese automatisch zum Gesamtpreis addiert. Der Mindestpreis
                            beträgt 10 €. Alle Preise müssen inkl. ggf. anfallende USt. angegeben werden.
                        </div>
                    </details>Artikelpreis
                </h5>
                <div class="preis-angaben">

                    <span class="preis-angaben-produkt @error('price') is-invalid @enderror"><input type="number"
                            min="10" id="price" name="price"
                            value="{{ old('price', $product->price) }}" class=""><span
                            class="currency">€</span></span>
                    @error('price')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>


            <div class="col-12 col-md-6">

                <h5 class="small mt-5">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Tipp von mir: Lege diesen Preis pauschal in einer glatten Summe fest.
                            Beispiel: 4,- Versandkosten für einen Slip. Darin enthalten ist das
                            Porto, der Briefumschlag und der Vakuumierbeutel.
                        </div>
                    </details>Versandkosten
                </h5>
                <div class="preis-angaben ">
                    <span class="preis-angaben-produkt @error('shipping_cost') is-invalid @enderror"><input
                            type="number" min="0" id="shipping_cost" name="shipping_cost"
                            value="{{ old('shipping_cost', $product->shipping_cost) }}"><span
                            class="currency">€</span></span>
                    @error('shipping_cost')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        </div>
        {{-- Button Boost     --}}
        @if (auth()->user()->status == false || auth()->user()->visibiliti_status == false || auth()->user()->verified == false)
        @else
            @if ($packages)
                <button type="button" {{ $product->boosted ? 'disabled' : '' }}
                    {{ auth()->user()->status ? '' : 'disabled' }} data-bs-toggle="modal"
                    data-bs-target="#modalBoostProduct" data-id="{{ $product->id }}"
                    class="btn-boost-single mt-3 mb-3 product-boost">
                    Artikel pushen (optional)
                    <svg xmlns="http://www.w3.org/2000/svg" width="7.367" height="12.724"
                        viewBox="0 0 7.367 12.724">
                        <path id="Pfad_1268" data-name="Pfad 1268" d="M230.093-248.119l6.3,6-6.3,6"
                            transform="translate(-229.748 248.481)" fill="none" stroke-miterlimit="10"
                            stroke-width="1" />
                    </svg>
                </button>
                @if ($product->boosted)
                    <div class="boost-status">Für
                        {{ $product->boosts->count() > 0 ? $product->boosts[0]->package->days : '' }}. Tage gepuscht
                    </div>
                @endif
            @endif
        @endif

        <button class="btn btn-primary mt-3" type="submit">Jetzt verkaufen</button>
    </div>
    @if (auth()->user()->status == false || auth()->user()->visibiliti_status == false || auth()->user()->verified == false)
    @else
        <!-- Modal Boost -->
        @if ($packages)
            <div class="modal fade" id="modalBoostProduct" tabindex="-1" aria-labelledby="modalBoostProductLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalBoostProductLabel">Artikel - pushen</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Verwerfen"></button>
                        </div>
                        <div class="modal-body">



                            <p class="text-primary">Artikel pushen und einfach mehr verkaufen!</p>
                            <ul class="single-boost-list">
                                @foreach ($packages as $package)
                                    <li><input id="{{ $package->id }}tage" name="package" type="checkbox"
                                            value="{{ $package->id }}" /><label
                                            for="{{ $package->id }}tage"><b>{{ $package->days }} Tage</b>
                                            pushen</label><span class="price-boost-single"> für nur
                                            <b>{{ $package->price_with_tax }}</b><span class="currency">
                                                €</span></span></li>
                                @endforeach

                            </ul>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn" data-bs-dismiss="modal">Verwerfen</button>
                            <button type="submit" class="btn btn-primary "
                                {{ auth()->user()->status ? '' : 'disabled' }}>Aktualisieren</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif



</div>
<ul>

    @foreach ($errors->all() as $message)
        <li>{{ $message }}</li>
    @endforeach
</ul>



@push('scripts')
    <script src="{{ asset('assets/js/choice.min.js') }}"></script>

    <script>
        $(document).ready(function() {

            var multipleCancelButton = new Choices('#tags', {
                removeItemButton: true,
                maxItemCount: 5,
                searchResultLimit: 5,
                renderChoiceLimit: 5
            });


        });
    </script>
    <script>
        $("input[name='name']").keyup((e) => {
            let slug = e.target.value.toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            $("input[name='slug']").val(slug)
        });
    </script>
    <script>
        $('#totalAddition input:checked').each(function() {

            if ($(this).is(':checked')) {
                let id = $(this).attr('id');
                let finalId = id.slice(-1, );


                const checkbox = document.getElementById($(this).attr('id'));
                const addionPrice = document.getElementById('addition-' + finalId);
                addionPrice.disabled = false;

            } else {

                addionPrice.disabled = false;
                addionPrice.value = "";
            }
        });
        $('#totalWearing input:checked').each(function() {
            if ($(this).is(':checked')) {
                let id = $(this).attr('id');
                let finalId = id.slice(-1, );


                const checkbox = document.getElementById($(this).attr('id'));
                const wearPrice = document.getElementById('wear-' + finalId);
                wearPrice.disabled = false;

            } else {
                wearPrice.disabled = true;
                wearPrice.value = "";
            }
        });

        $('#totalFinishing input:checked').each(function() {
            if ($(this).is(':checked')) {
                let id = $(this).attr('id');
                let finalId = id.replace("finishing-", "");


                const checkbox = document.getElementById($(this).attr('id'));
                const finishngPrice = document.getElementById('finishngPrice' + finalId);
                console.log(finalId)

                finishngPrice.disabled = false;

            } else {
                finishngPrice.disabled = true;
                finishngPrice.value = "";

            }
        });



        function finish(e) {

            const finishngPrice = document.getElementById('finishngPrice' + e);
            const checkbox = document.getElementById('finishing-' + e);
            if (checkbox.checked) {
                finishngPrice.disabled = false;
            } else {
                finishngPrice.disabled = true;
                finishngPrice.value = "";
            }

        }

        function addition(e) {

            const addition = document.getElementById('addition-' + e);
            const additionCheckbox = document.getElementById('additionCheckbox-' + e);
            if (additionCheckbox.checked) {
                addition.disabled = false;
            } else {
                addition.disabled = true;
                addition.value = "";
            }

        }

        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('[id^="checkboxWear-"]');
            checkboxes.forEach(function(checkbox) {
                const id = checkbox.id.split('-')[1]; 
                wear(id); 
            });
        });

        function wear(id) {
            const wearInput = document.getElementById('wear-' + id);
            const checkboxWear = document.getElementById('checkboxWear-' + id);

            if (checkboxWear.checked) {
                wearInput.disabled = false;
            } else {
                wearInput.disabled = true;
                wearInput.value = "";
            }
            checkboxWear.addEventListener('change', function() {
                if (this.checked) {
                    wearInput.disabled = false;
                } else {
                    wearInput.disabled = true;
                    wearInput.value = "";
                }
            });
        }
    </script>



    <script>
        const addRow = () => {
            const imageContainer = document.getElementById('images');
            const index = imageContainer.childElementCount + 1;

            // Check if the number of images is already 3
            if (imageContainer.childElementCount >= 3) {
                alert('Du kannst maximal 3. Fotos hinzufügen.');
                return; // Prevent adding more rows
            }

            const newRow = document.createElement('div');
            newRow.classList.add('d-flex', 'justify-content-start', 'justify-content-md-between', 'align-items-start',
                'align-items-md-center', 'flex-column', 'flex-md-row', 'py-2');

            newRow.innerHTML = `
                <div class="d-flex w-100 justify-content-between justify-content-md-start align-items-center">
                    <!-- Custom File Upload -->
                    <label class="custom-file-upload mt-3 btn btn-outline-secondary" for="image${index}">
                        <input type="file" id="image${index}" name="images[${index}][image]" class="form-control p-3" style="display: none;">
                        <i class="fa fa-upload" aria-hidden="true"></i> Foto hochladen
                    </label>
                </div>

                <div class="d-flex w-100 justify-content-between align-items-center">
                    <div class="m-3">
                        <input value="1" type="checkbox"  name="images[${index}][nsfw]"  id="nsfw${index}">
                        <label for="nsfw${index}">
                            FSK 18
                        </label>
                    </div>

                    <button class="bild-ansehen-form-produkt-einstellen  m-3" onclick="removeRow(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13.707" height="13.707" viewBox="0 0 13.707 13.707">
                            <g id="Gruppe_1628" data-name="Gruppe 1628" transform="translate(-1338.146 -348.253)">
                                <line id="Linie_229" data-name="Linie 229" x2="13" y2="13" transform="translate(1338.5 348.607)" fill="none" stroke-width="1"></line>
                                <line id="Linie_230" data-name="Linie 230" x2="13" y2="13" transform="translate(1351.5 348.607) rotate(90)" fill="none" stroke-width="1"></line>
                            </g>
                        </svg>   
                    </button>
                </div>`;

            imageContainer.appendChild(newRow);
        }

        const removeRow = (el) => {
            const element = el.closest(
                '.d-flex.justify-content-start.justify-content-md-between.align-items-start.align-items-md-center.flex-column.flex-md-row.py-2'
            );
            element.remove();
        }
    </script>

    <script>
        $(document).ready(function() {
            // Function to handle the visibility of elements based on category value
            function updateVisibility() {
                var categoryVal = $('#category').val();
                if (categoryVal == 23 || categoryVal == 22) {
                    $('#addtionGroup').hide();
                    $('#wearingDiv').hide();
                    $('#additionsDiv').hide();
                } else if (categoryVal == 24) {
                    $('#addtionGroup').show();
                    $('#wearingDiv').hide();
                    $('#additionsDiv').hide();
                } else {
                    $('#wearingDiv').show();
                    $('#addtionGroup').show();
                    $('#additionsDiv').show();
                }
            }

            // Add change event listener to the category select
            $('#category').change(function() {
                updateVisibility();
            });

            // Initialize on load
            updateVisibility();
        });
    </script>
@endpush

