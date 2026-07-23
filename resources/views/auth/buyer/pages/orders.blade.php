<x-dashboard type='buyer' title="Meine Käufe" :bread="[
    'Startseite' => route('buyer.dashboard'),
    'Profil' => route('buyer.dashboard'),
    'Meine Käufe' => route('buyer.orders'),
]">

    <div class="card-fields-shopping-cart">
        <!-- Selled Product Item-->
        @foreach ($orders as $order)
            <div class="card-item-profile-sells {{ $order->status == 3 ? 'storniert' : '' }}" style="border-bottom:none">
                <div class="card-item-profile-sells__main-info">
                    <div class="col-prod-profile-sells-image">
                        @if (isset($order->vendor->id))
                            <a href="{{ route('user.profile', $order->vendor->id) }}" class="no-before">
                                <img data-src="{{ media_url($order->product->image) ?: 'https://www.fraukruner.de/assets/img/user.png' }}"
                                    class="lazy img-fluid" alt="{{ $order->product->name }}">
                            </a>
                        @else
                            <img data-src="https://www.fraukruner.de/assets/img/user.png" class="lazy img-fluid"
                                alt="{{ $order->product->name }}">
                        @endif
                    </div>

                    <div class="col-prod-profile-sells-text">
                        <div class="col-prod-profile-sells-text__prod-summary">
                            <h6 class="text-primary">{{ $order->product->category->name }}</h6>
                            <p>{{ $order->product->name }}</p>
                            @if (isset($order->vendor->id))
                                <a href="{{ route('user.profile', $order->vendor->id) }}" title="zum Profil">zum
                                    Profil</a>
                            @endif

                            <div class="order-details-buyer-s text-grey small">
                                <div class="d-flex">
                                    <span style="min-width:100px;">Bestellt:</span>
                                    <span>{{ $order->created_at->format('d.m.Y') }}</span>
                                </div>
                                @if (filled($order->shipping_date))
                                    <div class="d-flex">
                                        <!-- if not sended yes then "offen" if -->
                                        <span style="min-width:100px;">Versendet:</span>
                                        <span>{{ $order->shipping_date->format('d.m.Y') }}
                                            @if (filled($order->shipping_method))
                                                ({{ $order->shipping_method }})
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>


                        </div>

                    </div>

                    <div class="col-prod-profile-sells-buttons text-center">

                        @if ($order->payment_gateway !== 'pre_payment')
                            @if ($order->payment_status == 0 && $order->status !== 3)
                                <a class="btn btn-primary" target="_blank"
                                    href="{{ route('payment', $order->parent_id) }}">Bezahlen</a>
                            @endif
                        @endif
                        @if ($order->payment_gateway == 'pre_payment' && $order->status == 0)
                            <a class="btn btn-primary" target="_blank"
                                href="{{ route('buyer.pre.payment', $order) }}">Bezahlen</a>
                        @endif

                        <!-- if ordered see photos and videos-->
                        @php
                            $viewDeadline = $order->shipping_date
                                ? \Carbon\Carbon::parse($order->shipping_date)
                                : $order->created_at;
                        @endphp
                        @if ($viewDeadline->gte(now()->subWeeks(4)))
                            @if (filled($order->video) && Storage::exists($order->video) && $order->status !== 3)
                                <a class="btn btn-secondary" target="_blank" href="{{ Storage::url($order->video) }}">Video
                                    ansehen</a>
                                {{-- <a class="btn btn-secondary" target="_blank" href="{{route('buyer.video.player',$order)}}">Video
                                    ansehen</a> --}}
                                <a target="_blank" class="small no-before" href="{{ Storage::url($order->video) }}"
                                    download><i class="fa fa-download" aria-hidden="true"></i> Video herunterladen</a>
                            @endif
                            @if (!$order->orderimages->isEmpty() && $order->status !== 3)
                                <a class="btn btn-secondary" target="_blank"
                                    href="{{ route('buyer.photos', $order) }}">Foto ansehen</a>
                            @endif
                        @endif
                        @if ($order->payment_status != 0)
                            <a href="{{ route('invoice', $order) }}" class="btn btn-secondary">Rechnung</a>
                        @endif

                        @if ($order->is_rated == false)
                            @if (isset($order->vendor->id) && $order->payment_status != 0 && $order->status !== 3)
                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#ratingModal" data-order-id="{{ $order->id }}"
                                    data-user-id="{{ $order->vendor->id }}">
                                    Erfahrung teilen
                                </button>
                            @endif
                        @else
                            <span class="text-grey small">Erfahrung geteilt</span>
                        @endif

                    </div>

                    <div class="col-prod-profile-sells-price">
                        <span class="col-prod-profile-sells-price__price">
                            @php
                                $finalTotal = $order->discount > 0 ? $order->total - $order->discount : $order->total;
                            @endphp
                            <div>{{ Shop::price($finalTotal) }}</div>

                        </span>
                    </div>

                </div>

                <div class="col-prod-profile-sells-addons">
                    <div class="col-prod-profile-sells-addons__placeholder"></div>

                </div>
                <hr>

                {{-- <div class="sorting-list-collapsing-my-sells">
                <b>Status:</b> <a href="https://www.dhl.de/de/privatkunden/dhl-sendungsverfolgung.html?piececode=TRACKINGCODE" target="_blank">Versendet</a> <!-- If the product was sended or not with Tracking link(Example DHL)-->
        </div>  --}}
            </div>
            <!-- Selled  Product Item-->
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

