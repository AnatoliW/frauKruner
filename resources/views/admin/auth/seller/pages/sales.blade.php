<x-dashboard type='seller' title="Meine Verkäufe" :bread="[
    'Startseite' => route('home'),
    'Profil' => route('seller.dashboard'),
    'Meine Verkäufe' => route('seller.sales'),
]">
    <style>
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 1);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;

        }

        #loadingOverlay img {
            max-width: 100px;

        }


        .blur-content {
            filter: blur(4px);
            pointer-events: none;
        }
    </style>

    <div id="loadingOverlay" style="display: none">
        <div class="d-flex justify-content-center flex-column align-items-center">
            <img src="{{ asset('assets/img/icons/lade-anim-farbe.gif') }}" alt="Datei wird hochgeladen">
            Datei wird hochgeladen...
        </div>
    </div>

    <div id="content" class="card-fields-shopping-cart">
        @if (Auth()->user()->status == false || Auth()->user()->visibiliti_status == false)
            <h5 class="small text-primary">
                <details data-popover="up">
                    <summary>?</summary>
                    <div class="popoverBody">
                        Mögliche Gründe dafür: <br>
                        1. Dein Verkäufer-Profil noch nicht von dem Admin verifiziert wurde (dies könnte in der Regel
                        1-3 Tage dauern). <br>
                        2. Du hast dein Profil in den Profileinstellungen deaktiviert.
                    </div>
                </details>Deine Produkte werden (noch) nicht gelistet
            </h5>
        @endif


        <!-- Selled Product Item-->
        @foreach ($orders as $order)
            <div class="card-item-profile-sells {{ $order->status == 3 ? 'storniert' : '' }}">
                <div class="card-item-profile-sells__main-info">
                    <div class="col-prod-profile-sells-image">
                        <a href="#" class="lightbox-public"
                            data-image-url="{{ $order->product->image ? media_url($order->product->image) : asset('assets/img/user.png') }}">
                            <img src="{{ $order->product->image ? media_url($order->product->image) : asset('assets/img/user.png') }}"
                                alt="{{ $order->product->name }}">
                        </a>
                    </div>

                    <div class="col-prod-profile-sells-text">
                        <div class="col-prod-profile-sells-text__prod-summary">
                            <h6 class="text-primary">{{ $order->product->category->name }}</h6>
                            <p>{{ $order->product->name }}</p>
                            <p class="text-grey small">

                                Bestellt am: {{ $order->created_at->format('d.m.Y') }}<br>
                                @foreach ($order->wearing_time as $data)
                                    Tragedauer: {{ $data }}<br>
                                @endforeach
                                @foreach ($order->finishings as $data)
                                    Veredelungen: {{ $data }}<br>
                                @endforeach
                                @foreach ($order->addition as $data)
                                    Zusatzoptionen: {{ $data }}<br>
                                @endforeach
                                Gutschrift-Nr.: FK{{ $order->created_at->year }}-{{ $order->id }}-{{ $order->vendor->id }}<br>
                            </p>

                        </div>

                    </div>





                    <div class="col-prod-profile-sells-buttons">

                    @if ($order->status !== 3)
                        @php
                            $hasVideo = false;
                            $hasPhoto = false;
                        @endphp

                        @foreach ($order->addition as $data)
                            @if (str_contains($data, 'Video'))
                                @php $hasVideo = true; @endphp
                            @elseif (str_contains($data, 'Foto'))
                                @php $hasPhoto = true; @endphp
                            @endif
                        @endforeach

                        @php
                            $uploadDeadline = $order->shipping_date
                                ? \Carbon\Carbon::parse($order->shipping_date)
                                : $order->created_at;
                        @endphp
                        @if ($uploadDeadline->gte(now()->subWeeks(4)))
                            @if ($hasVideo || $order->product->category->name == 'Video')
                                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#video" data-bs-whatever="{{ $order->id }}">Video hochladen</button>
                            @endif

                            @if ($hasPhoto || $order->product->category->name == 'Foto')
                                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#photo" data-bs-whatever="{{ $order->id }}">Foto hochladen</button>
                            @endif

                            @if (!$order->orderimages->isEmpty() || (filled($order->video) && Storage::exists($order->video)))
                            <a class="text-center no-before small" target="" href="{{ route('seller.photos', $order) }}" style="border:none">Dateien ansehen</a>
                            @endif
                        @endif


                    @endif





                        <!-- <a href="/warenkorb.html" class="btn btn-secondary"></a> -->
                        <!-- <a href="/warenkorb.html" class="btn btn-secondary">Foto hochladen</a> -->
                        <a href="{{ route('invoice', $order) }}" class="btn btn-secondary">Gutschrift</a>
                        @if ($order->message && $order->status !== 3)
                            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalId"
                                data-bs-whatever="{{ $order->message }}">Notiz</button>
                        @endif
                    </div>

                    <div class="col-prod-profile-sells-price">
                        <span
                            class="col-prod-profile-sells-price__price">{{ Shop::price($order->vendor_total) }}</span>
                    </div>
                </div>

                <div class="col-prod-profile-sells-addons">
                    <div class="col-prod-profile-sells-addons__placeholder"></div>

                </div>

                @php
                    $showShippingSection = $order->status !== 3;
                @endphp
                @if ($showShippingSection)
                    <hr>
                    <div class="sorting-list-collapsing-my-sells">
                        <button class="sidebar-collapse-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseVersenden1{{ $order->id }}" aria-expanded="false"
                            aria-controls="collapseVersenden1{{ $order->id }}">
                            {{ in_array($order->product->category->name ?? '', ['Video', 'Foto']) ? 'Als versendet markieren' : 'Versenden an' }}<span class="arrow">
                                <span></span>
                                <span></span>
                            </span>
                        </button>


                        <div class="collapse__options collapse " id="collapseVersenden1{{ $order->id }}"
                            style="">
                            @if (!in_array($order->product->category->name ?? '', ['Video', 'Foto']))
                                <p class="info-title">Adresse:<br>

                                </p>
                                <p class="buyer-name pb-0 mb-0">
                                    {{ $order->first_name }} {{ $order->last_name }}<br>
                                    @if (isset($order->additional))
                                        {{ $order->additional }}<br>
                                    @endif
                                    @if ($order->po_box)
                                        Postfach: {{ $order->po_box ?? '' }}<br>
                                    @endif
                                    {{ $order->street }} {{ $order->house_no }}<br>
                                </p>
                                <p class="buyer-postcode-city">
                                    <span>{{ $order->zip }}</span>
                                    <span>{{ $order->federal_state }}</span>
                                </p>
                            @endif

                            <form action="{{ route('seller.order.update', $order) }}" method="post">
                                @csrf
                                <div class="row">
                                    @php $isVideo = in_array($order->product->category->name ?? '', ['Video', 'Foto']); @endphp
                                    <div class="col-12 col-sm-5 mt-2">
                                        <select name="shipping_method" id="shipping_method" class="select-classic">
                                            @php
                                                $partners = $isVideo
                                                    ? ['Sonstiges']
                                                    : ['DHL', 'Hermes', 'DPD', 'UPS', 'GLS', 'Fedex', 'Deutsche Post', 'Anderes'];
                                            @endphp
                                            @foreach ($partners as $partner)
                                                <option value="{{ $partner }}"
                                                    @if ($partner === $order->shipping_method) selected @endif>
                                                    {{ $partner }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('shipping_method')
                                            <span class="text-danger">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    @if (!$isVideo)
                                        <div class="col-12 col-sm-7 mt-2">
                                            <input type="text" id="tracking_Id" name="tracking_Id"
                                                placeholder="12345678965432"
                                                value="{{ old('tracking_Id', $order->tracking_Id) }}">
                                            @error('tracking_Id')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                    @endif

                                    <div class="col-12 col-sm-{{ $isVideo ? '7' : '12' }} mt-2">
                                        <input type="date" id="shipping_date" name="shipping_date"
                                            value="{{ $order->shipping_date ? Carbon\Carbon::parse($order->shipping_date)->format('Y-m-d') : '' }}">
                                        @error('shipping_date')
                                            <span class="text-danger">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit" {{ ($isVideo ? $order->shipping_date : $order->tracking_Id) ? 'disabled' : '' }}
                                    class="btn btn-primary">
                                    {{ ($isVideo ? $order->shipping_date : $order->tracking_Id) ? 'Versendet' : ' Versenden' }}</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        <!-- Selled  Product Item-->




        <!-- Modal -->
        <div class="modal fade" id="modalId" tabindex="-1" role="dialog" aria-labelledby="modalTitleId"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">Notiz</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid" id="modal-messaage">

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>

                    </div>
                </div>
            </div>
        </div>
        <!-- Video -->
        <div class="modal fade" id="video" tabindex="-1" role="dialog" aria-labelledby="modalTitleId"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Video hochladen
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="{{ route('seller.video.upload') }}" method="post" id="videoForm"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="col-12 ">
                                    <h5 class="small">
                                        <details data-popover="up">
                                            <summary>?</summary>
                                            <div class="popoverBody">
                                                Das Video ist für den Käufer des Produktes sichtbar. Weitere Infos
                                                findest du in unseren <a href="/page/nutzungsbedingungen"
                                                    target="_blank">Nutzungsbedingungen</a>. Es wird das Videoformat .mp4 dringend empfohlen.
                                            </div>
                                        </details> Video hochladen (max. 1GB in .mp4)
                                    </h5>
                                    <div class="border-profile">
                                        <input type="file" name="video" id="videoInput"
                                            style="border:1px solid #B2B2B2;">
                                        <input type="hidden" name="order_id" value="" id="video_order_id">
                                    </div>

                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="video-upload-btn" type="button"
                                class="btn btn-primary me-2">Hochladen</button>
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Schließen</button>

                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Photo -->
        <div class="modal fade" id="photo" tabindex="-1" role="dialog" aria-labelledby="modalTitleId"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Foto hochladen
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="{{ route('seller.photo.upload') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="col-12 ">
                                    <h5 class="small">
                                        <details data-popover="up">
                                            <summary>?</summary>
                                            <div class="popoverBody">
                                                Das Foto ist für den Käufer des Produktes sichtbar. Weitere Infos
                                                findest du in unseren <a href="/page/nutzungsbedingungen"
                                                    target="_blank">Nutzungsbedingungen</a>.
                                            </div>
                                        </details> Foto hochladen
                                    </h5>
                                    <div class="border-profile">
                                        <input type="file" name="upload_photo[]" id="upload_photo"
                                            style="border:1px solid #B2B2B2;" accept="image/*"
                                            onchange="checkFileType(event)" multiple>
                                        <input type="hidden" name="order_id" id="photo_order_id">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script>
                            function checkFileType(event) {
                                var acceptableTypes = ["image/jpeg", "image/png", "image/gif", "image/bmp", "image/webp", "image/apng",
                                    "image/avif", "image/bmp", "image/tiff"
                                ];
                                for (let i = 0; i < event.target.files.length; i++) {
                                    if (!acceptableTypes.includes(event.target.files[i].type)) {
                                        alert("Das Bild-Format wird nicht unterstützt. ");
                                        event.target.value = null;
                                        break;
                                    }
                                }
                            }
                        </script>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary me-2">Hochladen</button>
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Schließen</button>

                        </div>
                    </form>
                </div>
            </div>
        </div>



        @push('js')
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js"></script>
            <script>
                var modalId = document.getElementById('modalId');

                modalId.addEventListener('show.bs.modal', function(event) {
                    // Button that triggered the modal
                    let button = event.relatedTarget;
                    // Extract info from data-bs-* attributes
                    let recipient = button.getAttribute('data-bs-whatever');
                    document.getElementById('modal-messaage').innerText = recipient;
                    // Use above variables to manipulate the DOM
                });
            </script>
            <script>
                var video = document.getElementById('video');

                video.addEventListener('show.bs.modal', function(event) {
                    let button = event.relatedTarget;
                    let recipient = button.getAttribute('data-bs-whatever');
                    document.getElementById('video_order_id').value = recipient;
                });
            </script>
            <script>
                var photo = document.getElementById('photo');

                photo.addEventListener('show.bs.modal', function(event) {
                    let button = event.relatedTarget;
                    let recipient = button.getAttribute('data-bs-whatever');
                    document.getElementById('photo_order_id').value = recipient;
                });
            </script>
        @endpush


    </div>

</x-dashboard>

