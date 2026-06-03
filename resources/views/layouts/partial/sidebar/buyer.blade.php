@php
    use App\Order;
    use App\Notification;
    $orders=Order::where('user_id',Auth()->id())->children()->count();

    $favorites=auth()->user()->favorites->count();
    $notifications=Notification::where('user_id',auth()->id())->orWhere('role',auth()->user()->role_id)->count();
    $unseenNotifications = Notification::where('seen',0)
    ->where(function ($query) {
        $query->where('user_id', auth()->id())
            ->orWhere('role', auth()->user()->role_id);
    })
    ->count();
    

@endphp


<aside class="profile-aside panel">
    <span id="profile-menu-scroll-point"></span>
    <a href="/shop" class="btn btn-primary-icon">Jetzt einkaufen</a>
    <ul class="profile-menu mb-4">
        <li class="profile-menu__head-item">
            <a href="{{ route('buyer.dashboard') }}" class="to-content-btn"><span></span>Mein Profil</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('buyer.orders') }}"
                class="to-content-btn"><span>{{ $orders }}</span>Bestellungen</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('buyer.news') }}"
                class="to-content-btn" style="{{$unseenNotifications > 0 ? 'font-weight:700' :''}}"><span >{{$notifications}}</span>Nachrichten</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('buyer.favorites') }}"
                class="to-content-btn" ><span >{{$favorites}}</span>Favoriten</a>
        </li>
        <li class="profile-menu__head-item">

            <div class="menu-item-collapsing">
                <button class="menu-item-collapsing-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseProfil" aria-expanded="false" aria-controls="collapseProfil">
                    Profileinstellungen<span class="arrow"><span></span><span></span></span>
                </button>

                <div class="collapse collapse__userprofile" id="collapseProfil">
                    <ul>
                        <li><a href="{{ route('buyer.user.data') }}"
                                class="to-content-btn">Nutzerdaten</a></li>
                        <li><a href="{{ route('buyer.data.verify') }}"
                                class="to-content-btn text-primary">18+ Inhalt Verifizierung</a></li>
                        <li><a href="{{ route('buyer.address') }}"
                                class="to-content-btn">Adresse</a></li>
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

    <a rel="sponsored" class="no-before" target="_blank" href="https://www.awin1.com/cread.php?s=2183640&v=11661&q=339671&r=1519089">
        <img class="img-fluid" style="width:100%; max-width:100%;" src="https://www.awin1.com/cshow.php?s=2183640&v=11661&q=339671&r=1519089" border="0">
    </a>

    <!-- END ADVERTISER: Orion DE from awin.com -->



</aside>

