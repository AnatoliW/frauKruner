<x-front_app>
    @section('title',
        $user->username ??
        (optional($user->profile)->username ??
        ($user->name ??
        'Verkäufer/in bei Frau
        Kruner')))
    @section('description', $user->profile ? $user->profile->description : '')
    @if ($user->status == 0)
        @section('head')
<meta name="robots" content="noindex, nofollow">
        @endsection
    @endif
    @push('css')
        <link rel="stylesheet" href="{{ asset('assets/css/choice.min.css') }}">
        <style>
            .rating-disabled .rating-input,
            .rating-disabled .rating-stars {

                margin-left: -8px !important;
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

            .rating-disabled .rating-input,
            .rating-disabled .rating-stars {
                cursor: pointer !important;
            }
        </style>
    @endpush

    <!--classes added on pause here:  paused -->
    <div class="container-xxl profile-container-public__welcome_section {{ $user->status == false ? 'paused' : '' }}">
        <main class="profile-content__welcome-schreen">

            <ul class="breadcrumb-userprofile">
                <li><a href="/">Startseite</a></li>

                <li><a href="#">{{ $user->username ?? $user->name }}</a></li>
            </ul>
            <div class="profile-content__profile-meta">

                <span class="profil-pausiert-badge badge">Pausiert</span>
                @if ($user->profile)
                    <a href="#" class="lightbox-public"
                        data-image-url="{{ $user->profile->profile_img ? media_url($user->profile->profile_img) : asset('assets/img/user.png') }}">
                        <img src="{{ $user->profile->profile_img ? media_url($user->profile->profile_img) : asset('assets/img/user.png') }}"
                            alt="{{ $user->name }}">
                    </a>
                @else
                    <a href="#" class="lightbox-public" data-image-url="{{ asset('assets/img/user.png') }}">
                        <img src="{{ asset('assets/img/user.png') }}" alt="{{ $user->name }}">
                    </a>
                @endif
                <div class="d-flex flex-column">
                    <div class="star-rating-container my-0 mx-3" data-bs-toggle="modal"
                        data-bs-target="#bewertungModal">
                        <div class="star-rating">
                            <input name="rating" type="number" value="{{ $avg_rating }}"
                                class="rating published_rating" data-size="xs">
                        </div>
                        <p class="mt-0">
                            {{ $user->ratings->count() }}
                        </p>
                    </div>
                    <div class="d-flex">
                        <h3>{{ $user->username ?? $user->name }}</h3>

                        @auth
                            @if (auth()->user()->role_id == 2)
                                @if (isset(auth()->user()->favorites) && auth()->user()->favorites->contains('favoritable_id', $user->id))
                                    @php
                                        $favorite = auth()
                                            ->user()
                                            ->favorites->where('favoritable_id', $user->id)
                                            ->first();

                                    @endphp
                                    <form class="deleteFavoriteForm position-relative" method="POST">
                                        @csrf

                                        <input type="hidden" name="product" value="{{ $user->id }}">
                                        <input type="hidden" name="create" class="deleteFormfavCreate">
                                        <input type="hidden" name="favorite_id" value="{{ $favorite->id }}">
                                        <input type="hidden" name="model_type" value="{{ get_class($user) }}">

                                        <button type="submit" class="heart-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                                class="size-6">
                                                <path
                                                    d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
                                            </svg>

                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('favorite.store', $user->id) }}" method="post"
                                        class="favoriteForm position-relative">
                                        @csrf
                                        <input type="hidden" name="model_type" value="{{ get_class($user) }}">
                                        <input type="hidden" name="favorite_id" class="favoriteId">
                                        <button type="submit" class="heart-icon favoriteButton">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @else
                            <div class="position-relative">
                                <a href="javascript:void()" class="heart-icon " data-bs-toggle="modal"
                                    data-bs-target="#loginModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                    </svg>
                                </a>
                            </div>

                        @endauth


                    </div>


                </div>


            </div>
            <div class="profile-content__message">
                {!! $user->status == false
                    ? 'Hey, ich bin leider gerade nicht aktiv.<br><a href="/shop" title="zum Shop">Hier</a> kannst du weiter stöbern.'
                    : ($user->profile
                        ? $user->profile->description
                        : '') !!}
            </div>



            {{-- @php
                $rating = $user->ratings->where('user_id', auth()->id());
                $baught = App\Order::where('vendor_id', $user->id)
                    ->where('user_id', auth()->id())
                    ->where('status', 1)
                    ->count();
                $avg_rating = round($user->ratings()->avg('rating'));

            @endphp
            @auth
                @if ($baught >= 1 && $rating->count() < 1)
                    <section class="mt-5 pt-5">
                        <div class="col-md-12 mt-4" id="rating">
                            <h4>Ihre Bewertung</h4>
                        </div>
                        <form action="{{ route('rating', ['user'=>$user,'order'=>$order]) }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-lg-12 mt-3">
                                    <input name="rating" type="number" value="1" class="rating product_rating"
                                        min="1" max="5" step=".5" data-size="xs">
                                </div>
                                <div class="col-md-6">
                                    <div class="">
                                        <label for="comment">Bewertungstext</label>
                                        <textarea name="comment" style="height:100px" class="form-control @error('comment') is-invalid @enderror" id="comment"
                                            required></textarea>
                                        @error('comment')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-secondary">Senden</button>
                                </div>
                            </div>
                        </form>
                    </section>
                @endif
            @endauth --}}


            <h3 class="mt-11">Meine Produkte</h3>

            <div class="product-list-shop ">
                <!-- Single Products Item-->
                @foreach ($user->products->where('status', true)->sortByDesc('created_at') as $activeProduct)
                    <x-product-card :product="$activeProduct" />
                @endforeach

                @foreach ($user->products->where('status', false)->sortByDesc('created_at') as $inactiveProduct)
                    <x-product-card :product="$inactiveProduct" />
                @endforeach
            </div>



        </main>
    </div>
    @if ($user->ratings->count() > 0)
        <div class="modal fade" id="bewertungModal" tabindex="-1" aria-labelledby="bewertungModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <p class="h5 modal-title" id="bewertungModalLabel">Meine Bewertungen</p>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Schließen"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container-review">
                            @foreach ($user->ratings as $review)
                                <div class="single-review" style="  border-bottom: 1px solid #B2B2B2;padding:  0; ">

                                    <div class="star-ratings" style="flex-direction:column;align-items:start">

                                        <input name="rating" type="number" value="{{ $review->rating }}"
                                            class="rating published_rating" data-size="xs">
                                        <b class="pt-1">{{ $review->user->username[0] }}.</b>
                                        <p>{{ $review->review }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
                    </div>
                </div>
            </div>
        </div>
    @endif





    @push('scripts')
        <script src="{{ asset('js/custom/star-rating.js') }}" type="text/javascript"></script>
        <script type="text/javascript">
            $("#product_rating").rating({
                showCaption: true
            });
            $(".published_rating").rating({
                showCaption: false,
                readonly: true,
            });
        </script>
    @endpush
</x-front_app>

