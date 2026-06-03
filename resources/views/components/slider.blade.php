<section class="bg-secondary">
    <div class="main-slider-shop">
        <img src="{{ asset('assets/img/front-page/hoeschen-kruner.png') }}" class="content-before-slide">
        <div class="carousel-shop-left">
            @foreach ($sliders as $slider)
                <div class="carousel-shop-left__cell" data-background-image-url="{{ media_url($slider->image) }}">
                </div>
            @endforeach
        </div>
        <div class="mein-slider-shop-pfeil-container">
            <img src="{{ asset('assets/img/icons/icon-pfeil-links.svg') }}" class="main-slider-shop-pfeil-links"
                alt="Pfeil nach links" height="26px" width="43px">
            <img src="{{ asset('assets/img/icons/icon-pfeil-rechts.svg') }}" class="main-slider-shop-pfeil-rechts"
                alt="Pfeil nach links" height="26px" width="43px">
        </div>

        <div class="carousel-shop-right">
            @foreach ($sliders as $slider)
                <div class="carousel-shop-right__cell">
                    <div class="carousel-shop-right__content">
                        <p class="h2">{{ $slider->heading }}</p>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>

<!--Discount Information-->
<div class="container-after-slider-shop">
    <div class="container-xxl d-flex justify-content-center align-items-center text-center">
        {{ setting('site.discount_info') }}
    </div>

</div>
<!--Discount Information-->

