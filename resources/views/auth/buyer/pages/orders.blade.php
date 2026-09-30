<x-dashboard type='buyer' title="Meine Käufe" :bread="[
    'Startseite' => route('buyer.dashboard'),
    'Profil' => route('buyer.dashboard'),
    'Meine Käufe' => route('buyer.orders'),
]">

    <div class="card-fields-shopping-cart">
        {{-- Eine Karte je Bestellung. Die Artikel stehen als Positionen darin:
             Bezahlt wurde die Bestellung als Ganzes, und es gibt eine Rechnung
             dafür. Video, Fotos und die Bewertung gelten dagegen je Artikel,
             denn die kommen von der jeweiligen Herstellerin. --}}
        @foreach ($orders as $order)
            @php
                // Altbestellung ohne Positionen: dann ist sie ihre eigene Position.
                $positions = $order->childrens->isNotEmpty() ? $order->childrens : collect([$order]);
                $alleStorniert = $positions->every(fn ($p) => (int) ($p->status ?? 0) === 3);
                $istVorkasse = $order->payment_gateway === 'pre_payment';
                $istBezahlt = (int) ($order->payment_status ?? 0) !== 0;
            @endphp

            <div class="card-item-profile-sells {{ $alleStorniert ? 'storniert' : '' }}" style="border-bottom:none">

                {{-- Kopf der Bestellung: Nummer, Datum, Gesamtbetrag, Rechnung. --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-2">
                    <div>
                        <b class="text-primary">Bestellung {{ $order->orderNumber() }}</b>
                        <div class="text-grey small">
                            Bestellt: {{ $order->created_at->format('d.m.Y') }}
                            @if ($positions->count() > 1)
                                · {{ $positions->count() }} Artikel
                            @endif
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="col-prod-profile-sells-price__price">
                            {{-- Im Kopf der Bestellung ist der Gutschein bereits abgezogen. --}}
                            <div>{{ Shop::price($order->total) }}</div>
                        </span>

                        @unless ($istVorkasse)
                            @if (! $istBezahlt && ! $alleStorniert)
                                <a class="btn btn-primary" target="_blank"
                                    href="{{ route('payment', $order) }}">Bezahlen</a>
                            @endif
                        @endunless
                        @if ($istVorkasse && (int) ($order->status ?? 0) === 0)
                            <a class="btn btn-primary" target="_blank"
                                href="{{ route('buyer.pre.payment', $order) }}">Bezahlen</a>
                        @endif

                        {{-- Eine Rechnung für die ganze Bestellung. --}}
                        @if ($istBezahlt)
                            <a href="{{ route('invoice', $order) }}" class="btn btn-secondary">Rechnung</a>
                        @endif
                    </div>
                </div>

                @if (filled($order->discount_code) && (float) $order->discount > 0)
                    <div class="text-grey small pb-2">
                        Gutschein {{ $order->discount_code }}: −{{ Shop::price($order->discount) }}
                    </div>
                @endif

                {{-- Die Artikel dieser Bestellung. --}}
                @foreach ($positions as $position)
                    <div class="card-item-profile-sells__main-info {{ (int) ($position->status ?? 0) === 3 ? 'storniert' : '' }}">
                        <div class="col-prod-profile-sells-image">
                            @if (isset($position->vendor->id))
                                <a href="{{ route('user.profile', $position->vendor->id) }}" class="no-before">
                                    <img data-src="{{ media_url($position->product->image) ?: 'https://www.fraukruner.de/assets/img/user.png' }}"
                                        class="lazy img-fluid" alt="{{ $position->product->name }}">
                                </a>
                            @else
                                <img data-src="https://www.fraukruner.de/assets/img/user.png" class="lazy img-fluid"
                                    alt="{{ $position->product->name }}">
                            @endif
                        </div>

                        <div class="col-prod-profile-sells-text">
                            <div class="col-prod-profile-sells-text__prod-summary">
                                <h6 class="text-primary">{{ $position->product->category->name }}</h6>
                                <p>{{ $position->product_name ?? $position->product->name }}</p>
                                @if (isset($position->vendor->id))
                                    <a href="{{ route('user.profile', $position->vendor->id) }}" title="zum Profil">zum
                                        Profil</a>
                                @endif

                                <div class="order-details-buyer-s text-grey small">
                                    @if (filled($position->shipping_date))
                                        <div class="d-flex">
                                            <span style="min-width:100px;">Versendet:</span>
                                            <span>{{ $position->shipping_date->format('d.m.Y') }}
                                                @if (filled($position->shipping_method))
                                                    ({{ $position->shipping_method }})
                                                @endif
                                            </span>
                                        </div>
                                    @else
                                        <div class="d-flex">
                                            <span style="min-width:100px;">Versand:</span>
                                            <span>offen</span>
                                        </div>
                                    @endif
                                    @if ((int) ($position->status ?? 0) === 3)
                                        <div class="d-flex">
                                            <span style="min-width:100px;">Status:</span>
                                            <span>storniert</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-prod-profile-sells-buttons text-center">
                            {{-- Video, Fotos und Bewertung gelten je Artikel. --}}
                            @php
                                $viewDeadline = $position->shipping_date
                                    ? \Carbon\Carbon::parse($position->shipping_date)
                                    : $position->created_at;
                            @endphp
                            @if ($viewDeadline->gte(now()->subWeeks(4)))
                                @if (filled($position->video) && Storage::exists($position->video) && $position->status !== 3)
                                    <a class="btn btn-secondary" target="_blank" href="{{ Storage::url($position->video) }}">Video
                                        ansehen</a>
                                    <a target="_blank" class="small no-before" href="{{ Storage::url($position->video) }}"
                                        download><i class="fa fa-download" aria-hidden="true"></i> Video herunterladen</a>
                                @endif
                                @if (!$position->orderimages->isEmpty() && $position->status !== 3)
                                    <a class="btn btn-secondary" target="_blank"
                                        href="{{ route('buyer.photos', $position) }}">Foto ansehen</a>
                                @endif
                            @endif

                            @if ($position->is_rated == false)
                                @if (isset($position->vendor->id) && (int) ($position->payment_status ?? 0) !== 0 && $position->status !== 3)
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#ratingModal" data-order-id="{{ $position->id }}"
                                        data-user-id="{{ $position->vendor->id }}">
                                        Erfahrung teilen
                                    </button>
                                @endif
                            @else
                                <span class="text-grey small">Erfahrung geteilt</span>
                            @endif
                        </div>

                        <div class="col-prod-profile-sells-price">
                            <span class="col-prod-profile-sells-price__price">
                                <div>{{ Shop::price($position->total) }}</div>
                            </span>
                        </div>
                    </div>
                @endforeach

                <div class="col-prod-profile-sells-addons">
                    <div class="col-prod-profile-sells-addons__placeholder"></div>
                </div>
                <hr>
            </div>
        @endforeach
    </div>


    <div class="modal fade" id="ratingModal" tabindex="-1" role="dialog" aria-labelledby="ratingModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ratingModalLabel">Deine Erfahrung</h5>
                    <button type="button" class="btn btn-close border-0" data-bs-dismiss="modal"
                        aria-label="Schließen">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Rating Form -->
                    <style>
                        .rating-container .clear-rating {
                            padding-right: 0;
                        }

                        .rating-container .star {
                            font-size: 22px;
                        }

                        #ratingModal .rating-container .caption {
                            display: none;
                        }
                    </style>
                    <form id="ratingForm" action="" method="POST">
                        @csrf
                        <div class="form-group mb-2">
                            <input name="rating" type="number" value="1" class="rating product_rating"
                                min="1" max="5" step=".5" data-size="xs" required>
                        </div>
                        <div class="form-group">
                            <label for="comment">Erfahrungstext</label>
                            <textarea class="form-control" id="comment" name="comment" rows="2" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Erfahrung teilen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('ratingModal').addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var orderId = button.getAttribute('data-order-id');
                var userId = button.getAttribute('data-user-id');
                var actionUrl = "{{ url('rating') }}" + '/' + userId + '/' + orderId;
                document.getElementById('ratingForm').setAttribute('action', actionUrl);

                var $rating = $('#ratingModal .product_rating');
                if ($rating.length && typeof $rating.rating === 'function') {
                    $rating.rating('destroy');
                    $rating.rating({
                        showCaption: false,
                        language: 'de',
                    });
                }
            });
        </script>
    @endpush

</x-dashboard>

