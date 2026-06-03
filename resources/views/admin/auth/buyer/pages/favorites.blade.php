<x-dashboard type='buyer' title="Meine Favoriten" :bread="[
    'Startseite' => route('buyer.dashboard'),
    'Profil' => route('buyer.dashboard'),
    'Meine Favoriten' => route('buyer.favorites'),
]">

    <div class="card-fields-shopping-cart">
        <ul class="nav nav-tabs mt-3" id="favoriten" role="tablist">
            @if ($product_favorites->count() > 0 || $profile_favorites->count() > 0)
                @if ($product_favorites->count() > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#produkte-tab"
                            type="button" role="tab" aria-controls="produkte-tab"
                            aria-selected="true">Produkte</button>
                    </li>
                @endif
                @if ($profile_favorites->count() > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $product_favorites->count() == 0 ? 'active' : '' }}" id="profile-tab"
                            data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab"
                            aria-controls="profile-tab-pane"
                            aria-selected="{{ $product_favorites->count() == 0 ? 'true' : 'false' }}">Verkäufer*innen</button>
                    </li>
                @endif
            @endif
        </ul>
        <div class="tab-content 
            @if ($product_favorites->count() == 0 && $profile_favorites->count() == 0) @else
              border-end border-start border-bottom @endif
            p-3
        "
            id="favoritenContent">
            @if ($product_favorites->count() > 0)
                <div class="tab-pane fade show active" id="produkte-tab" role="tabpanel" aria-labelledby="home-tab"
                    tabindex="0">
                    <h5 class="my-3">Meine Lieblingsprodukte</h5>
                    <div class="row">
                        @foreach ($product_favorites as $item)
                            @php
                                $product = $item->favoritable;
                            @endphp
                            @if($product)
                            <div class="product-list-shop__product {{ $product->boosted ? 'boosted' : '' }} {{ $product->status == false || $product->user->status == false ? 'paused' : '' }} favorite-item favorite-item-{{ $item->id }}">
                                <a href="{{ $product->path() }}">
                                    <div class="product-list-shop__product__image">
                                        <img class="lazy" data-src="{{ media_url($product->image) }}" alt="{{ $product->name }}">
                                        <form action="{{ route('buyer.favorite.delete', $item) }}" method="post" class="favorite-delete-form" data-item-id="{{ $item->id }}">
                                            @csrf
                                            <button type="submit" class="heart-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                                    <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 0 1 1.06 0L12 10.94l5.47-5.47a.75.75 0 1 1 1.06 1.06L13.06 12l5.47 5.47a.75.75 0 1 1-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 0 1-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                        <span class="profil-pausiert-badge badge">Pausiert</span>
                                        <span class="boosted-badge badge">Gepusht</span>
                                    </div>
                                    <div class="product-list-shop__product__content">
                                        <p class="text-uppercase mb-0">{{ @$product->category->name }}</p>
                                        <b>{{ $product->name }}</b>
                                        @if ($product->status == false)
                                            <p>Hey, das Produkt ist leider gerade nicht aktiv.</p>
                                        @elseif ($product->user->status == false)
                                            <p>Hey, ich bin leider gerade nicht aktiv.</p>
                                        @else
                                            <p>{{ Str::limit($product->details, 60) }} </p>
                                        @endif
                                        <span class="price">{{ Shop::price($product->total_price) }}</span>
                                    </div>
                                </a>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($profile_favorites->count() > 0)
                <div class="tab-pane fade {{ $product_favorites->count() == 0 ? 'show active' : '' }}"
                    id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                    <h5 class="my-3">Meine Lieblingsprofile</h5>
                    <div class="row">
                        @foreach ($profile_favorites as $favorite)
                            @php
                                $vendor = $favorite->favoritable;
                                $avg_rating = round($vendor->ratings()->avg('rating'));
                            @endphp
                            @if($vendor)
                            <div
                                class="product-list-shop__product {{ $vendor->boosted ? 'boosted' : '' }} {{ !$vendor->status ? 'paused' : '' }} favorite-item favorite-item-{{ $favorite->id }}">
                                <a href="{{ route('user.profile', $vendor->id) }}">
                                    <div class="product-list-shop__product__image vendor">
                                        <img data-src="{{ $vendor->profileImage() ?: 'https://www.fraukruner.de/assets/img/user.png' }}"
                                            class="lazy" alt="{{ $vendor->username }}">
                                        <form action="{{ route('buyer.favorite.delete', $favorite) }}" method="post" class="favorite-delete-form" data-item-id="{{ $favorite->id }}">
                                            @csrf
                                            <button type="submit" class="heart-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="currentColor" class="size-6">
                                                    <path fill-rule="evenodd"
                                                        d="M5.47 5.47a.75.75 0 0 1 1.06 0L12 10.94l5.47-5.47a.75.75 0 1 1 1.06 1.06L13.06 12l5.47 5.47a.75.75 0 1 1-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 0 1-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 0 1 0-1.06Z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                        <span class="boosted-badge badge">Gepusht</span>
                                        <span class="profil-pausiert-badge badge">Pausiert</span>
                                    </div>
                                    <div class="product-list-shop__product__content mt-3">
                                        <b>{{ $vendor->username }} </b>
                                        @if ($vendor->status == false)
                                            <p>Hey, ich bin leider gerade nicht aktiv.</p>
                                        @else
                                            <p>{{ Illuminate\Support\Str::limit(html_entity_decode(strip_tags($vendor->profile ? $vendor->profile->description : '')), 50) }}
                                            </p>
                                        @endif
                                    </div>
                                </a>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($product_favorites->count() == 0 && $profile_favorites->count() == 0)
                <h5 class="mt-5">Du hast noch keine Favoriten.</h5>
            @endif
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.favorite-delete-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); // Stop form from reloading the page

                    let itemId = form.getAttribute('data-item-id');
                    let url = form.action;

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.querySelector('.favorite-item-' + itemId)
                            .remove(); // Select the item by class and remove it
                            } else {
                                alert('Das Element konnte nicht gelöscht werden. Bitte versuchen Sie es erneut.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Das Element konnte nicht gelöscht werden. Bitte versuchen Sie es erneut.');
                        });
                });
            });
        });
    </script>

</x-dashboard>

