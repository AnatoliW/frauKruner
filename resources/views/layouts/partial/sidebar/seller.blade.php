@php
    use App\Order;
    use App\Product;
    use App\Notification;
    use App\Package;
    use App\Models\Boost;
    $notifications = Notification::forMe()
        ->count();
    $unseenNotifications = Notification::where('seen', 0)
        ->forMe()
        ->count();

    $reviews = Auth()
        ->user()
        ->ratings->count();
    $orders = Order::where('vendor_id', Auth()->id())
        ->whereNotNull('parent_id')
        ->where('payment_status', 1)
        ->count();
    $products = Product::where('user_id', Auth()->id())->count();
    $payments = Order::where('vendor_id', Auth()->id())
        ->where('status', 1)
        ->count();

    $packages = Package::where('type', 'Profile')->get();
    $boosts = Boost::where('user_id', auth()->id())
        ->latest()
        ->count();
@endphp
<aside class="profile-aside panel">
    <span id="profile-menu-scroll-point"></span>
    <div class="user-menu-buttons-action">
        <a href="{{ route('seller.products.create') }}" class="btn btn-primary-icon"><img
                data-src="{{ asset('assets/img/icons/plus-btn.svg') }}" class="lazy">Jetzt verkaufen</a>

                @if (auth()->user()->status==false || auth()->user()->visibiliti_status==false  || auth()->user()->verified==false)
       @else
        <button {{ auth()->user()->boosted ? 'disabled' : '' }} {{auth()->user()->status ? '' : 'disabled'}} {{auth()->user()->visibiliti_status ? '' : 'disabled'}}  type="button" data-bs-toggle="modal"
            data-bs-target="#modalBoostProfile" class="btn btn-boost">
            <svg xmlns="http://www.w3.org/2000/svg" width="8.242" height="12.592" viewBox="0 0 8.242 12.592">
                <g id="Gruppe_1640" data-name="Gruppe 1640" transform="translate(-199.42 -554.341)">
                    <path id="Pfad_1609" data-name="Pfad 1609" d="M230.093-248.119l3.945,3.759-3.945,3.759"
                        transform="translate(447.901 789.104) rotate(-90)" fill="none" stroke-miterlimit="10"
                        stroke-width="1" />
                    <path id="Pfad_1610" data-name="Pfad 1610" d="M-8151.459-5297.843v-11.867"
                        transform="translate(8355 5864.777)" fill="none" stroke-width="1" />
                </g>
            </svg>
            Profil pushen
        </button>
        @endif
        <!--<button type="button" data-bs-toggle="modal" data-bs-target="#modalBoostProfile" class="btn btn-boost">
   <svg xmlns="http://www.w3.org/2000/svg" width="8.242" height="12.592" viewBox="0 0 8.242 12.592">
   <g id="Gruppe_1640" data-name="Gruppe 1640" transform="translate(-199.42 -554.341)">
    <path id="Pfad_1609" data-name="Pfad 1609" d="M230.093-248.119l3.945,3.759-3.945,3.759" transform="translate(447.901 789.104) rotate(-90)" fill="none" stroke-miterlimit="10" stroke-width="1"/>
    <path id="Pfad_1610" data-name="Pfad 1610" d="M-8151.459-5297.843v-11.867" transform="translate(8355 5864.777)" fill="none" stroke-width="1"/>
   </g>
   </svg>
   Profil pushen
  </button>-->

        <!-- Modal -->
        <!-- <div class="modal fade" id="modalBoostProfile" tabindex="-1" aria-labelledby="modalBoostProfileLabel" aria-hidden="true">
   <div class="modal-dialog">
   <div class="modal-content">
    <div class="modal-header">
    <h5 class="modal-title" id="modalBoostProfileLabel">Profil pushen</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Verwerfen"></button>
    </div>
    <div class="modal-body">


     <form>
      <p class="text-primary">Profil pushen und einfach mehr verkaufen!</p>
      <ul class="single-boost-list">
       <li><input id="2tage" type="checkbox" /><label for="3tage">das Profil für <b>3 Tage</b> pushen</label><span class="price-boost-single"> für nur <b>3</b><span class="currency"> €</span></span></li>
       <li><input id="4tage" type="checkbox" /><label for="6tage">das Profil für <b>6 Tage</b> pushen</label><span class="price-boost-single"> für nur <b>5</b><span class="currency"> €</span></span></li>
       <li><input id="10tage" type="checkbox" /><label for="12tage">das Profil für <b>12 Tage</b> pushen</label><span class="price-boost-single"> für nur <b>10</b><span class="currency"> €</span></span></li>
      </ul>
     </form>

    </div>
    <div class="modal-footer">
     <button type="button" class="btn" data-bs-dismiss="modal">Verwerfen</button>
     <button type="button" class="btn btn-primary">Aktualisieren</button>
     </div>
   </div>
   </div>
  </div> -->
    </div>

    <ul class="profile-menu mb-4">
        <li class="profile-menu__head-item">
            <a href="{{ route('user.profile', Auth()->id()) }}" class="to-content-btn"><span></span>Mein öffentliches
                Profil</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('seller.sales') }}" class="to-content-btn"><span>{{ $orders }}</span>Verkäufe</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('seller.products') }}" class="to-content-btn"><span>{{ $products }}</span>Produkte</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('seller.news') }}" class="to-content-btn "
                style="{{ $unseenNotifications > 0 ? 'font-weight:700' : '' }}"><span>{{ $notifications }}</span>Nachrichten</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('seller.payments') }}"
                class="to-content-btn"><span>{{ $payments }}</span>Zahlungen</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('seller.reviews') }}"
                class="to-content-btn"><span>{{ $reviews }}</span>Bewertungen</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('seller.charges') }}"
                class="to-content-btn"><span>{{ $boosts }}</span>Rechnungen</a>
        </li>
        <li class="profile-menu__head-item">

            <div class="menu-item-collapsing">
                <button class="menu-item-collapsing-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseProfil" aria-expanded="false" aria-controls="collapseProfil">
                    Profileinstellungen<span class="arrow"><span></span><span></span></span>
                </button>

                <div class="collapse collapse__userprofile" id="collapseProfil">
                    <ul>
                        <li><a href="{{ route('seller.user_data') }}" class="to-content-btn">Nutzerdaten</a></li>
                        <li><a href="{{ route('seller.address') }}" class="to-content-btn">Adresse & Konto</a>
                        </li>
                        <li>
                            <form action="{{ route('seller.visibility') }}" method="post">
                                @csrf
                                @if (Auth()->user()->status == true)
                                    <button type="submit" class="btn btn-secondary">Profil pausieren</button>
                                @else
                                    <button type="submit" class="btn btn-primary">Profil aktivieren</button>
                                @endif
                            </form>

                        </li>
                    </ul>
                </div>
            </div>

        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('logout') }}"
                onclick="event.preventDefault();
            document.getElementById('logout-form').submit();">
                <span class="fa fa-toggle-off mr-3 mt-1"></span>Logout</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>

    <!-- START ADVERTISER: Orion DE from awin.com -->

    <a rel="sponsored" class="no-before" target="_blank" href="https://www.awin1.com/cread.php?s=2183628&v=11661&q=339666&r=1519089">
        <img class="img-fluid" style="max-width:100%;width:100%;" src="https://www.awin1.com/cshow.php?s=2183628&v=11661&q=339666&r=1519089" border="0">
    </a>

    <!-- END ADVERTISER: Orion DE from awin.com -->

</aside>


@if (auth()->user()->status==false || auth()->user()->visibiliti_status==false  || auth()->user()->verified==false)
@else
<div class="modal fade" id="modalBoostProfile" tabindex="-1" aria-labelledby="modalBoostProfileLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBoostProfileLabel">Profil pushen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Verwerfen"></button>
            </div>
            <form action="{{ route('seller.boost.store') }}" method="post">
                @csrf
                <div class="modal-body">



                    <p class="text-primary">Profil pushen und einfach mehr verkaufen!</p>
                    <ul class="single-boost-list">
                        @foreach ($packages as $package)
                            <!-- {{ $package->name }}  -->
                            <li><input id="{{ $package->id }}tage" type="radio" class="fancy-radio"
                                    name="package" value="{{ $package->id }}" />
                                <label for="{{ $package->id }}tage">das Profil für <b>{{ $package->days }} Tage</b>
                                    pushen
                                    <span class="price-boost-single"> für nur <b>{{ $package->price_with_tax }}</b><span
                                            class="currency"> €</span></span>
                                </label>
                            </li>
                        @endforeach
                    </ul>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Verwerfen</button>
                    <button type="submit" class="btn btn-primary" {{auth()->user()->status ? '' : 'disabled'}}  >zur Bezahlung</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif