<x-dashboard type='seller' title="Meine Produkte" :bread="[
    'Startseite' => route('home'),
    'Profil' => route('seller.dashboard'),
    'Meine Produkte' => route('seller.products'),
]">
    @php
        $packages = App\Package::where('type', 'Product')->get();
    @endphp
    @if (auth()->user()->status==false || auth()->user()->visibiliti_status==false  || auth()->user()->verified==false)
        <h5 class="small text-primary">
            <details data-popover="up">
                <summary>?</summary>
                <div class="popoverBody">
                    Mögliche Gründe dafür: <br>
                    1. Dein Verkäufer-Profil noch nicht von dem Admin verifiziert wurde (dies könnte in der Regel 1-3
                    Tage dauern). <br>
                    2. Du hast dein Profil in den Profileinstellungen deaktiviert.
                </div>
            </details>Deine Produkte werden (noch) nicht gelistet
        </h5>
    @endif


    <a href="{{ route('seller.products.create') }}" class="btn btn-primary-icon"><img
            data-src="{{ asset('assets/img/icons/plus-btn.svg') }}" class="lazy">Neues Produkt hinzufügen</a>
    <div class="card-fields-produkte">
        @foreach ($products as $product)
            <!-- Product Item-->
            <div class="card-item">
                <div class="card-item__main-info">
                    <div class="col-prod-image">
                        <a href="#" class="lightbox-public"
                            data-image-url="{{ $product->image ? media_url($product->image) : asset('assets/img/user.png') }}">
                            <img src="{{ $product->image ? media_url($product->image) : asset('assets/img/user.png') }}"
                                alt="{{ $product->image }}">
                        </a>
                    </div>

                    <div class="col-prod-text">
                        <div class="col-prod-text__prod-summary">
                            <h6 class="text-primary">{{ $product->category->name }}</h6>
                            <p>{{ $product->name }}</p>
                            <span class="col-prod-price__price">{{ Shop::price($product->price()) }}</span>
                        </div>

                    </div>
                    <div class="col-prod-price" style="width:20%">
                        <!-- Link Only for Testing-->
                        <div class="col-prod-price__buttons">
                            <!-- @if ($product->status == 0)
                                <a href="{{ route('seller.product.active', $product) }}" class="me-3 text-danger" title="Zum deaktivieren des Produktes klicken">Nicht aktiv</a>
                                @else
                                <a href="{{ route('seller.product.active', $product) }}" class="me-3 text-success" title="Zum aktivieren des Produktes klicken">Aktiv</a>
                                @endif -->

                            <label class="switch" @if (!auth()->user()->status) style="pointer-events: none; opacity: 0.6; cursor: not-allowed;" @endif>
                                <input type="checkbox" id="productStatusSwitch{{ $product->id }}"
                                    @if ($product->status == 1) checked @endif
                                    @if (!auth()->user()->status) disabled @endif>
                                <span class="slider round pauseitem {{ $product->id }}" data-product-id="{{ $product->id }}"></span>
                            </label>
                            @push('scripts')
                                <script>
                                    $(document).ready(function() {
                                        // Attach the event listener to the specific element once
                                        $('.slider.round.pauseitem.{{ $product->id }}').one('click', function() {
                                            const productId = $(this).data('product-id');
                                            const url = `/seller/dashboard/product-active/${productId}`;

                                            $.ajax({
                                                url: url,
                                                type: 'GET',
                                                cache: false, // Disable caching
                                                headers: {
                                                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                                                    'Pragma': 'no-cache',
                                                    'Expires': '0',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                },
                                                success: function(response) {
                                                    // Suppose the server returns the new product status in response.status
                                                    console.log('Updated product ID:', productId, 'Response:', response);

                                                    @if ($product->boosted || !auth()->user()->status || !$product->status)
                                                        pushBtn.prop('disabled', true);
                                                    @else
                                                        pushBtn.prop('disabled', false);
                                                    @endif
                                                },
                                                error: function(error) {
                                                    console.error('Error:', error);
                                                    alert('Es hat leider nicht geklappt. Bitte versuche es später erneut.');
                                                }
                                            });
                                        });
                                    });
                                </script>
                            @endpush



                            <a href="{{ route('seller.products.edit', $product->slug) }}"
                                class="col-prod-price__buttons__edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14.5" height="13.968"
                                    viewBox="0 0 14.5 13.968">
                                    <g id="Icon_feather-edit-3" data-name="Icon feather-edit-3"
                                        transform="translate(-4 -3.691)">
                                        <path id="Pfad_1376" data-name="Pfad 1376" d="M18,30h6.75"
                                            transform="translate(-6.75 -12.841)" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round" stroke-width="1" />
                                        <path id="Pfad_1377" data-name="Pfad 1377"
                                            d="M14.625,4.784a1.591,1.591,0,0,1,2.25,2.25L7.5,16.409l-3,.75.75-3Z"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="1" />
                                    </g>
                                </svg>
                            </a>

                            <form action="{{ route('seller.products.delete', $product) }}" method="post">
                                @csrf
                                <button class="pt-1" type="submit"
                                    onclick="return confirm('Bist du sicher, dass du dieses Produkt löschen möchtest?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13.707" height="13.707"
                                        viewBox="0 0 13.707 13.707">
                                        <g id="Gruppe_1628" data-name="Gruppe 1628"
                                            transform="translate(-1338.146 -348.253)">
                                            <line id="Linie_229" data-name="Linie 229" x2="13" y2="13"
                                                transform="translate(1338.5 348.607)" fill="none" stroke-width="1" />
                                            <line id="Linie_230" data-name="Linie 230" x2="13" y2="13"
                                                transform="translate(1351.5 348.607) rotate(90)" fill="none"
                                                stroke-width="1" />
                                        </g>
                                    </svg>
                                </button>
                            </form>
                        </div>

                        
                           @if (auth()->user()->status==false || auth()->user()->visibiliti_status==false  || auth()->user()->verified==false)
                           @else
                        <button type="button" id="pushButton{{ $product->id }}" data-bs-toggle="modal" data-id="{{ $product->id }}" 
                            data-bs-target="#modalBoostProduct" class="btn btn-boost w-100 d-750-none product-boost"
                             {{ ($product->boosted || !auth()->user()->status || !$product->status) ? 'disabled' : '' }}>
                            <svg xmlns="http://www.w3.org/2000/svg" width="8.242" height="12.592"
                                viewBox="0 0 8.242 12.592">
                                <g id="Gruppe_1640" data-name="Gruppe 1640" transform="translate(-199.42 -554.341)">
                                    <path id="Pfad_1609" data-name="Pfad 1609"
                                        d="M230.093-248.119l3.945,3.759-3.945,3.759"
                                        transform="translate(447.901 789.104) rotate(-90)" fill="none"
                                        stroke-miterlimit="10" stroke-width="1" />
                                    <path id="Pfad_1610" data-name="Pfad 1610" d="M-8151.459-5297.843v-11.867"
                                        transform="translate(8355 5864.777)" fill="none" stroke-width="1" />
                                </g>
                            </svg>
                            Artikel pushen
                        </button>
                        @endif
                        {{-- if pushed notice --}}

                        @if ($product->boosted)
                            <div class="boost-status">Für
                                {{ $product->boosts->count() > 0 ? $product->boosts[0]->package->days : '' }}. Tage
                                gepuscht</div>
                        @endif
                        {{-- if pushed  --}}


                        @if (auth()->user()->status==false || auth()->user()->visibiliti_status==false  || auth()->user()->verified==false)
                        @else
                        <div class="modal fade" id="modalBoostProduct" tabindex="-1"
                            aria-labelledby="modalBoostProductLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalBoostProductLabel">Artikel - pushen
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Verwerfen"></button>
                                    </div>
                                    <form action="" method="post" id="boost-form">
                                        <div class="modal-body">


                                            @csrf
                                            <p class="text-primary">Artikel pushen und einfach mehr verkaufen!</p>
                                            <ul class="single-boost-list">
                                                @foreach ($packages as $package)
                                                    <li><input id="{{ $package->id }}tage" name="package"
                                                            class="fancy-radio" value="{{ $package->id }}"
                                                            type="radio" /><label for="{{ $package->id }}tage">
                                                            <b>{{ $package->days }} Tage</b> pushen <span
                                                                class="price-boost-single"> für nur
                                                                <b>{{ $package->price_with_tax }}</b><span class="currency">
                                                                    €</span></span></label></li>
                                                @endforeach

                                            </ul>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn"
                                                data-bs-dismiss="modal">Verwerfen</button>
                                            <button type="submit" class="btn btn-primary" id="boostBtnSubmit"  {{auth()->user()->status ? '' : 'disabled'}}>zur Bezahlung</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

                <div class="col-prod-addons">
                    <div class="col-prod-addons__buttons">
                        <a href="{{ route('seller.products.edit', $product->slug) }}" class="btn btn-bearbeiten">
                            Bearbeiten
                        </a>

                        @if (auth()->user()->status==false || auth()->user()->visibiliti_status==false  || auth()->user()->verified==false)
                        @else
                        <button type="button" data-bs-toggle="modal" data-bs-target="#modalBoostProduct"
                            data-id="{{ $product->id }}" class="btn btn-boost product-boost"
                            {{ $product->boosted ? 'disabled' : '' }}  {{auth()->user()->status ? '' : 'disabled'}}>
                            <svg xmlns="http://www.w3.org/2000/svg" width="8.242" height="12.592"
                                viewBox="0 0 8.242 12.592">
                                <g id="Gruppe_1640" data-name="Gruppe 1640" transform="translate(-199.42 -554.341)">
                                    <path id="Pfad_1609" data-name="Pfad 1609"
                                        d="M230.093-248.119l3.945,3.759-3.945,3.759"
                                        transform="translate(447.901 789.104) rotate(-90)" fill="none"
                                        stroke-miterlimit="10" stroke-width="1" />
                                    <path id="Pfad_1610" data-name="Pfad 1610" d="M-8151.459-5297.843v-11.867"
                                        transform="translate(8355 5864.777)" fill="none" stroke-width="1" />
                                </g>
                            </svg>
                            Artikel pushen
                        </button>
                        @endif
                    </div>
                </div>

            </div>
            <!-- Product Item-->
        @endforeach





    </div>
    @push('scripts')
        @if (session()->has('boost'))
            <script>
                $(document).ready(function() {
                    var url = "{{ route('seller.boost.store') }}";
                    var route = url + '/' + "{{ session()->get('boost') }}";
                    $("#boost-form").attr("action", route);

                    $('#modalBoostProduct').modal('show')

                });
            </script>
        @endif
        <script>
            $(document).ready(function() {

                $(".product-boost").click(function() {
                    var url = "{{ route('seller.boost.store') }}";
                    var route = url + '/' + $(this).data('id');
                    $("#boost-form").attr("action", route);
                });
            });
        </script>
    @endpush
</x-dashboard>

