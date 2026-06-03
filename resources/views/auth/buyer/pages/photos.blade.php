<x-dashboard type='buyer' title="Foto Gallerie" :bread="
[
    'Startseite' => route('buyer.dashboard'),
    'Meine Käufe' => route('buyer.orders'),
    'Fotos' => route('buyer.photos',$order),

]
">

@push('css')
<style>


  @media (min-width: 992px){
  .slide-width{
   width:100%;
   display:flex;
   justify-content:flex-start;
  }

  .product-image-slider{
  padding-left: 0;
  }
}
</style>
@endpush




<div class="slide-width">
<div class="product-image-slider " style="">

@foreach ($images as $image)

<div class="product-image-slider__slide flex-column" style="position: absolute; left:0">
  <img data-flickity-lazyload="{{ media_url($image->image) }}" class="product-image-slider__slide__image" alt=" Produktbild">
  <a href="{{ media_url($image->image) }}" download class="download-button-downloads-front">
      <svg xmlns="http://www.w3.org/2000/svg" width="36.532" height="26.734" viewBox="0 0 36.532 26.734">
        <g id="download" transform="translate(-401.7 2.6)">
          <line id="Linie_176" data-name="Linie 176" y1="23.248" transform="translate(419.928 -2.6)" fill="none" stroke="#111941" stroke-miterlimit="10" stroke-width="0.948"/>
          <line id="Linie_177" data-name="Linie 177" x2="36.532" transform="translate(401.7 23.66)" fill="none" stroke="#111941" stroke-miterlimit="10" stroke-width="0.948"/>
          <path id="Pfad_1545" data-name="Pfad 1545" d="M431.478,16.3l-8.728,8.65L414.1,16.3" transform="translate(-2.823 -4.302)" fill="none" stroke="#111941" stroke-miterlimit="10" stroke-width="0.948"/>
        </g>
        </svg>
        Herunterladen
    </a>
</div>

@endforeach
</div>
</div>


</x-dashboard>
