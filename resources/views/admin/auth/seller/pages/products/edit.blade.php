<x-dashboard type='seller' title='{{ $product->name }}' :bread="[
    'Startseite' => route('home'),
    'Profil' => route('seller.dashboard'),
    'Meine Produkte' => route('seller.products'),
    $product->name => route('seller.products.edit',$product),
]">
    <a href="#profile-menu-scroll-point" class="btn-back"><span class="arrow-back"><span></span><span></span></span></a>
    {{--  <h1>Neues Produkt hinzufügen</h1>  --}}
    <hr>
    <form method="post" action="{{route('seller.products.update',$product)}}" enctype="multipart/form-data"> 
        @csrf
        @include('auth.seller.pages.products.includes.form')
    </form>
</x-dashboard>
