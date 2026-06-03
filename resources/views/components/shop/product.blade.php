<div class="slider-boosted-user pt-5 pb-5">
  <div class="row">
    <div class="col-12 col-md-6">
      <div class="d-flex">
        <a href="{{ route('user.profile', $profile->id) }}">
          <img data-src="{{ $profile->profile->imgUrl() }}" class="lazy slider-boosted-user__img" height="60px" width="60px">
        </a>
        <div class="p-2">
          <a href="{{ route('user.profile', $profile->id) }}">{{ $profile->username }}</a>
          <span class="star-rating-container" onclick="reviews({{ $profile->id }})">
            <div class="star-rating">
              <input name="rating" type="number" value="{{ round($profile->avg_rating) }}" class="rating published_rating" data-size="xs" readonly>
            </div>
            <p class="mt-0" style="cursor:pointer;">
              {{ $profile->total_rating }}
            </p>
          </span>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 d-none d-md-flex justify-content-end">
      <div class="slider-angebote-shop-categorys-pfeil-container">
        <img data-src="{{ asset('assets/img/icons/icon-pfeil-secondary-rechts-links.svg') }}" class="slider-angebote-shop-categorys-pfeil-links lazy" alt="Pfeil nach links" height="26px" width="43px">
        <img data-src="{{ asset('assets/img/icons/icon-pfeil-rechts.svg') }}" class="slider-angebote-shop-categorys-pfeil-rechts lazy" alt="Pfeil nach links" height="26px" width="43px">
      </div>
    </div>
  </div>

  <div class="slider-angebote-shop-categorys">
    @foreach ($profile->products as $product)
      <div class="slider-angebote-shop-categorys__slide">
        <a href="{{ $product->path() }}">
          <div class="slider-angebote-shop-categorys__slide__image-field" data-background-image-url="{{ str_replace(' ', '%20', media_url($product->image)) }}">
          </div>
          <div class="slider-angebote-shop-categorys__slide__content">
            <p class="h6 mb-0">{{ $product?->category?->name }}</p>
            <b>{{ $product->name }}</b>
            <p>{{ Str::limit($product->details, 60) }} </p>
            <span class="slider-angebote-shop-categorys__slide__content-price">{{ Shop::price($product->total_price) }}</span>
          </div>
        </a>
      </div>
    @endforeach
  </div>

  <div class="slider-angebote-shop-categorys-pfeil-container d-block d-md-none">
    <img data-src="{{ asset('assets/img/icons/icon-pfeil-secondary-rechts-links.svg') }}" class="slider-angebote-shop-categorys-pfeil-links lazy" alt="Pfeil nach links" height="26px" width="43px">
    <img data-src="{{ asset('assets/img/icons/icon-pfeil-rechts.svg') }}" class="slider-angebote-shop-categorys-pfeil-rechts lazy" alt="Pfeil nach links" height="26px" width="43px">
  </div>

</div>
<div class="shop-section-center-left">
  <a rel="sponsored" class="no-before mt-3 d-none d-sm-block" target="_blank" href="https://bit.ly/sexamfoncom">
    <img class="img-fluid lazy" title="Sexamfon.com Werbung" data-src="{{ asset('assets/img/werbung/Telefonsex-2411.jpg') }}">
  </a>

  <a rel="sponsored" class="no-before mt-3 d-block d-sm-none" target="_blank" href="https://bit.ly/sexamfoncom">
    <img class="img-fluid lazy" title="Sexamfon.com Werbung" data-src="{{ asset('assets/img/werbung/Telefonsex-24112.jpg') }}">
  </a>


</div>

