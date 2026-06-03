<x-front_app>
<div class="container-xxl profile-container__welcome_section">
<aside class="profile-aside panel">
    <span id="profile-menu-scroll-point"></span>
    <a href="/" class="btn btn-primary-icon"><img data-src="{{asset('assets/img/icons/plus-btn.svg')}}" class="lazy">Jetzt
        verkaufen</a>
        
    <ul class="profile-menu">
        <li class="profile-menu__head-item">
            <a href="{{ route('seller.sales') }}"
                class="to-content-btn"><span>2</span>Verkäufe</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('seller.products') }}"
                class="to-content-btn"><span>2</span>Produkte</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('seller.news') }}"
                class="to-content-btn"><span>2</span>Nachrichten</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('seller.payments') }}"
                class="to-content-btn"><span>0</span>Zahlungen</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('seller.reviews') }}"
                class="to-content-btn"><span>1</span>Bewertungen</a>
        </li>
        <li class="profile-menu__head-item">
            <a href="{{ route('seller.reviews') }}"
                class="to-content-btn"><span>1</span>Charges</a>
        </li>
        <li class="profile-menu__head-item">

            <div class="menu-item-collapsing">
                <button class="menu-item-collapsing-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseProfil" aria-expanded="false" aria-controls="collapseProfil">
                    Profileinstellungen<span class="arrow"><span></span><span></span></span>
                </button>

                <div class="collapse collapse__userprofile" id="collapseProfil">
                    <ul>
                        <li><a href="{{ route('seller.user_data') }}"
                                class="to-content-btn">Nutzerdaten</a></li>
                        <li><a href="{{ route('seller.address') }}"
                                class="to-content-btn">Adresse</a></li>
                    </ul>
                </div>
            </div>

        </li>
    </ul>

</aside>
{{ $slot }}
</div>
</x-front_app>

