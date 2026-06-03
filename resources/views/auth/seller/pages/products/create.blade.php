<x-dashboard type='seller' title='' :bread="[
    'Startseite' => route('home'),
    'Profil' => route('seller.dashboard'),
    'Meine Produkte' => route('seller.products'),
    'Neues Produkt hinzufügen' => route('seller.products.create'),
]">
    <a href="#profile-menu-scroll-point" class="btn-back"><span class="arrow-back"><span></span><span></span></span></a>
    <h1>Neues Produkt hinzufügen</h1>
    <hr>
    <form method="post" action="{{route('seller.products.store')}}" enctype="multipart/form-data" >
        @csrf
        @include('auth.seller.pages.products.includes.form')
    </form>

    {{--  <form action="{{route('seller.image.store')}}" methd="POST" id="image-upload" class="dropzone" enctype="multipart/form-data">
        @csrf

    </form>  --}}
</x-dashboard>


