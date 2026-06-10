@php
    $menus = menu('main', '_json');
    $paymentMethods = Cache::get('payment_methods');

    if (
        !($paymentMethods instanceof \Illuminate\Support\Collection) ||
        $paymentMethods->contains(fn($item) => !is_object($item))
    ) {
        Cache::forget('payment_methods');
        $paymentMethods = App\PaymentIcon::all();
        Cache::put('payment_methods', $paymentMethods, 60);
    }

@endphp

<!DOCTYPE html>
<html lang="de">
<!-- {{ str_replace('_', '-', app()->getLocale()) }}-->

<head>
    <meta charset="UTF-8">

    <!-- Robots Header Development-->
    @if (env('APP_ENV') == 'development')
        <meta name="robots" content="noindex, nofollow">
        <meta name="googlebot" content="noindex, nofollow">
    @endif

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="juicyads-site-verification" content="83484dbf95302c7bffa89b656baf5a2f">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', setting('site.description'))">
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="@yield('title', setting('site.title'))" />
    <meta property="og:description" content="@yield('description', setting('site.description'))" />
    <meta property="og:image" content="{{ media_url(setting('site.facebook_image')) }}" />
    @yield('head')
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <link rel="canonical" href="@yield('canonical', request()->url())">

    <style>
        :root {
            --unterline-secondary: url("{{ asset('/assets/img/icons/unterstreichung-secondary.svg') }}");
            --unterline-primary: url("{{ asset('/assets/img/icons/unterstreichung-primary.svg') }}");
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/style.min.css?v=14') }}" defer>
    <link rel="stylesheet" href="{{ asset('css/print.css') }}" media="print" />
    <link rel="stylesheet" href="{{ asset('css/custom/star-rating.css') }}" media="all" type="text/css" />
    {{-- <link rel="stylesheet" href="{{asset('fonts/font-awesome/fontawesome.css')}}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/css/font.min.css') }}">
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('assets/img/favicons/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('assets/img/favicons/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('assets/img/favicons/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/favicons/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('assets/img/favicons/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/img/favicons/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('assets/img/favicons/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/img/favicons/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192"
        href="{{ asset('assets/img/favicons/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/img/favicons/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicons/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/files/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ asset('assets/img/favicons/ms-icon-144x144.png') }}">
    <meta name="theme-color" content="#ffffff">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>


    {{-- Toastr  --}}
    <link rel="stylesheet" href="{{ asset('assets/css/toastr.css') }}" />
    <link rel="stylesheet" href="{{ asset('icon/css/font-awesome.css') }}" />
    @stack('css')
    <style>
        .navbar-header-secondary__list__item .search-field {
            background-image: url("{{ asset('assets/img/icons/search-icon.svg') }}");
        }
    </style>

    <script defer type="application/javascript" src="{{ asset('assets/js/klaro-config.js') }}"></script>
    <script defer data-config="klaroConfig" type="application/javascript" src="{{ asset('assets/js/klaro.js') }}"></script>

    <!-- Google Tag Manager -->

    @if (env('APP_ENV') == 'production')
        <script
        async
        data-type="application/javascript"
        type="text/plain"
        data-src="https://www.googletagmanager.com/gtag/js?id=G-Q53ZP4B0WM"
        data-name="googleAnalytics">
    </script>

        <script defer type="text/plain" data-type="application/javascript" data-name="googleAnalytics">
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-Q53ZP4B0WM');
    </script>


        <script
        data-type="application/javascript"
        type="text/plain"
        data-name="google-tag-manager">
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-WJ2JJ8L');
    </script>
    @endif

    <!-- Google tag (gtag.js)
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-Q53ZP4B0WM"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-Q53ZP4B0WM');
    </script>
    End Google Tag Manager -->


    <!-- Page loading styles -->

    <style>
        .page-loading {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            -webkit-transition: all .4s .2s ease-in-out;
            transition: all .4s .2s ease-in-out;
            background-color: #fff;
            opacity: 0;
            visibility: hidden;
            z-index: 9999;
        }

        .page-loading.active {
            opacity: 1;
            visibility: visible;
        }

        .page-loading-inner {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            text-align: center;
            -webkit-transform: translateY(-50%);
            transform: translateY(-50%);
            -webkit-transition: opacity .2s ease-in-out;
            transition: opacity .2s ease-in-out;
            opacity: 0;
        }

        .page-loading.active>.page-loading-inner {
            opacity: 1;
        }

        .page-loading-inner>span {
            display: block;
            font-size: 1rem;
            font-weight: normal;
            color: #7E7E7E;
        }

        @-webkit-keyframes spinner {
            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @keyframes spinner {
            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }
    </style>



    <!-- Page loading scripts -->

    <script>
        function headerMenuButtonFunction() {
            var navBarLock = document.getElementById("navBarHeader");
            document.body.classList.toggle('lock-scroll');
            navBarLock.classList.toggle("overflowAuto");

        };

        function headerSearchFunctionHidden() {
            var searchAppers = document.getElementById("searchSecondaryHidden");
            searchAppers.classList.toggle("activeSearch");
        };


        window.onscroll = function() {
            removeActiveSearch()
        };

        function removeActiveSearch() {
            if (document.body.scrollTop > 1 || document.documentElement.scrollTop > 1) {
                var searchAppersScrolls = document.getElementById("searchSecondaryHiddenForm");
                searchAppersScrolls.style.top = "0px";
            } else {
                var searchAppersScrolls = document.getElementById("searchSecondaryHiddenForm");
                searchAppersScrolls.style.top = "4.3rem";
            }
        }

        document.addEventListener("DOMContentLoaded", (event) => {
            const preloader = document.querySelector('.page-loading');
            setTimeout(function() {
                preloader.classList.remove('active');
                preloader.remove();
            }, 500);

        });
    </script>
    <style type="text/css">
        {
            ! ! setting('code.style') ! !
        }
    </style>
    <style>
        .radio+label {
            position: relative;
            cursor: pointer;

        }

        .radio {
            position: relative;
            left: 1.25rem;
            bottom: 0;
            z-index: 0;
            -webkit-appearance: none;
            border: none !important;
            border-radius: 0 !important;
            padding: 0px !important;
            width: 0px !important;
        }

        .radio+label::before {
            width: 1.25rem;
            height: 1.25rem;
            bottom: 0;
            margin-top: 3px;
            border-radius: 50%;
            border: 1px solid #122253;
            background-color: #fff;
            display: block;
            content: "";
            float: left;
            margin-right: 0.6rem;
            z-index: 5;
            position: relative;
            transition: 0.2s ease-in-out;
            cursor: pointer;
        }

        .radio:checked+label::before {
            box-shadow: inset 0 0 0 6px #E74A3F;
            background-color: #fff;
            border: 1px solid #E74A3F;
            border-radius: 50%;
        }

        .radio:checked+label {
            color: #E74A3F;
        }
    </style>
    <title>@yield('title', 'Frau Kruner - lebe deinen Traum')</title>

    <!-- Page loading spinner -->
    <div class="page-loading active">
        <div class="page-loading-inner">
            <img src="https://fraukruner.de/assets/img/icons/lade-anim-farbe.gif" height="70px" width="70px"
                alt="Seite lädt">
            <span class="mt-3">Laden...</span>
        </div>
    </div>

</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe data-name="google-tag-manager"
            data-src="https://www.googletagmanager.com/ns.html?id=GTM-WJ2JJ8L" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <header>
        @if (env('APP_ENV') == 'development')
            <div class="d-flex justify-content-center align-items-center"
                style="background-color: red; color: #fff; font-size: 1.2rem; font-weight: 700; height: 3rem;">
                <span class="text-center">ES IST EINE TESTSEITE BITTE NICHT BESTELLEN!!!!!!!!</span>
            </div>
        @endif

        <!--Desktop Information-->
        <div class="container-before-header">
            <div class="container-xxl d-flex justify-content-center align-items-center">
                {{ setting('site.top_title') }}

            </div>
        </div>
        <!--Desktop Information-->

        <!--Navigation-->
        <nav class="navbar navbar-expand-lg " id="navBarHeader">
            <div class="container-xxl">
                <a class="navbar-brand" href="/"><img src="{{ asset('assets/img/icons/FxxK-Logo.svg') }}"
                        class="navbar-logo" height="36px" width="123px" alt="Frau Kruner Logo"></a>
                <!--Secondary Menu with Search for Mobile devices-->
                <span class="navbar-header-secondary-before">
                    <ul class="navbar-header-secondary-before__list">

                        <li class="navbar-header-secondary-before__list__item hidden-search"
                            id="searchSecondaryHidden">

                            <button class="search-button-secondary" onclick="headerSearchFunctionHidden()"><img
                                    data-src="{{ asset('assets/img/icons/search-icon.svg') }}" height="25px"
                                    width="25px" class="lazy" alt="Suche Icon"></button>

                            <form role="search" method="get" class="search-form-hidden"
                                id="searchSecondaryHiddenForm" action="{{ route('shop') }}">
                                <label class="d-flex">
                                    <input type="search" class="search-field-hidden" placeholder="Suche…"
                                        value="" name="search" title="Suchen" />

                                    <span class="close-button" onclick="headerSearchFunctionHidden()"><img
                                            data-src="{{ asset('assets/img/icons/close-icon.svg') }}" class="lazy"
                                            alt="Suche Icon"></span>

                                </label>
                                <input type="submit" class="search-submit-hidden" value="Suchen" />
                            </form>

                        </li>

                        <li class="navbar-header-secondary-before__list__item visible-search">
                            <form role="search" method="get" class="search-form" action="{{ route('shop') }}">
                                <label>
                                    <input type="search" class="search-field" placeholder="Suche…" value=""
                                        name="search" title="Suchen" />
                                </label>
                                <input type="submit" class="search-submit" value="Suchen" />
                            </form>
                        </li>

                        <li class="navbar-header-secondary-before__list__item">
                            <a href="{{ route('login') }}"
                                @if (Auth::check()) title="Zum Profil"
                                @else
                                title="Zum Login/ Zur Registrierung" @endif>
                                <svg xmlns="http://www.w3.org/2000/svg" height="23px" width="24px"
                                    viewBox="0 0 22.314 24.5">
                                    <g id="Gruppe_1098" data-name="Gruppe 1098"
                                        transform="translate(-1220.906 -84.836)">
                                        <g id="Gruppe_1652" data-name="Gruppe 1652"
                                            transform="translate(1221.406 85.336)">
                                            <path id="Pfad_1219" data-name="Pfad 1219"
                                                d="M2.347,6.244A5.774,5.774,0,1,0,8.12.471,5.774,5.774,0,0,0,2.347,6.244Z"
                                                transform="translate(2.536 -0.471)" fill="none"
                                                stroke-miterlimit="10" stroke-width="1" />
                                            <path id="Pfad_1220" data-name="Pfad 1220"
                                                d="M.411,16.418a10.657,10.657,0,0,1,21.314,0"
                                                transform="translate(-0.411 7.582)" fill="none"
                                                stroke-miterlimit="10" stroke-width="1" />
                                        </g>
                                    </g>
                                </svg>
                                @if (Auth::check())
                                    <svg xmlns="http://www.w3.org/2000/svg" id="iconLoggedIn" width="8.987"
                                        height="6.413" viewBox="0 0 8.987 6.413">
                                        <path data-name="iconcheck" d="M13.573,9,8.366,14.206,6,11.84"
                                            transform="translate(-5.293 -8.293)" fill="none" stroke="#db5743"
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                                    </svg>
                                @endif

                            </a>
                        </li>
                        @if (Auth::check())
                            @if (auth()->user()?->role_id !== 3 && auth()->user()?->role_id !== 1)
                                <li class="navbar-header-secondary__list__item favorit">
                                    <span class="counter-cart counter-fav"
                                        id="">{{ auth()->user()?->favorites->count() ?? '0' }}</span>
                                    <a href="/buyer/dashboard/favoriten">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="20"
                                            viewBox="0 0 27.182 25">
                                            <path id="icon-hearth"
                                                d="M29.182,10.3A6.687,6.687,0,0,0,22.363,3.75a6.83,6.83,0,0,0-6.272,3.975A6.831,6.831,0,0,0,9.817,3.75,6.687,6.687,0,0,0,3,10.3C3,20.8,16.091,27.75,16.091,27.75S29.182,20.8,29.182,10.3Z"
                                                transform="translate(-2.5 -3.25)" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                                        </svg>

                                    </a>
                                </li>
                            @endif
                        @endif
                        <li class="navbar-header-secondary-before__list__item">
                            <span class="counter-cart ">{{ Cart::getContent()->count() }}</span>
                            <a href="{{ route('cart') }}" title="Zum Warenkorb">
                                <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 30.388 25">
                                    <g id="Gruppe_1100" data-name="Gruppe 1100" transform="translate(0 -0.052)">
                                        <g id="Gruppe_1653" data-name="Gruppe 1653" transform="translate(0 0.552)">
                                            <path id="Pfad_1221" data-name="Pfad 1221"
                                                d="M12.677,3.907H29.625L25.9,12.439H7.76L4.776.218H0"
                                                transform="translate(0 -0.218)" fill="none" stroke-miterlimit="10"
                                                stroke-width="1" />
                                            <path id="Pfad_1222" data-name="Pfad 1222"
                                                d="M6.516,8.111A1.974,1.974,0,1,1,4.541,6.136,1.975,1.975,0,0,1,6.516,8.111Z"
                                                transform="translate(6.027 13.914)" fill="none"
                                                stroke-miterlimit="10" stroke-width="1" />
                                            <path id="Pfad_1223" data-name="Pfad 1223"
                                                d="M10.311,8.111A1.974,1.974,0,1,1,8.336,6.136,1.975,1.975,0,0,1,10.311,8.111Z"
                                                transform="translate(16.007 13.914)" fill="none"
                                                stroke-miterlimit="10" stroke-width="1" />
                                        </g>
                                    </g>
                                </svg>
                            </a>
                        </li>
                    </ul>
                </span>
                <!--Secondary Menu with Search for Mobile devices-->

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"
                    aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation"
                    id="menuHeaderNavButton" onclick="headerMenuButtonFunction()">
                    <div class="bar1"></div>
                    <div class="bar2"></div>
                    <div class="bar3"></div>
                </button>

                </button>

                <div class="collapse navbar-collapse" id="navbarMain">
                    <!--The Menu-->
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('shop') }}">Shop</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('vendors') }}">Verkäufer*innen</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('page/safe-zone') }}">Safe Zone</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('about') }}">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('blog') }}">Neuigkeiten</a>
                        </li>
                        <!-- <li class="nav-item nav-item__item-primary">
                            <a class="nav-link" href="{{ route('seller.registration') }}">Unterwäsche verkaufen</a>
                        </li> -->
                    </ul>
                    <!--The Menu-->

                    <!--Secondary Menu with Search for Desktop & Small Mobile devices-->
                    <span class="navbar-header-secondary mx-0">
                        <ul class="navbar-header-secondary__list px-0">
                            <li class="navbar-header-secondary__list__item search-form">

                                <form role="search" method="get" class="search-form"
                                    action="{{ route('shop') }}">
                                    <label>
                                        <input type="search" class="search-field" placeholder="Suche…"
                                            value="" name="search" title="Suchen" />
                                    </label>
                                    <input type="submit" class="search-submit" value="Suchen" />
                                </form>

                            </li>



                            <li class="navbar-header-secondary__list__item">
                                <a href="{{ url('login') }}"
                                    @if (Auth::check()) title="Zum Profil"
                                    @else
                                    title="Zum Login/Registrierung" @endif>
                                    <svg xmlns="http://www.w3.org/2000/svg" height="23px" width="24px"
                                        viewBox="0 0 22.314 24.5">
                                        <g id="Gruppe_1098" data-name="Gruppe 1098"
                                            transform="translate(-1220.906 -84.836)">
                                            <g id="Gruppe_1652" data-name="Gruppe 1652"
                                                transform="translate(1221.406 85.336)">
                                                <path id="Pfad_1219" data-name="Pfad 1219"
                                                    d="M2.347,6.244A5.774,5.774,0,1,0,8.12.471,5.774,5.774,0,0,0,2.347,6.244Z"
                                                    transform="translate(2.536 -0.471)" fill="none"
                                                    stroke-miterlimit="10" stroke-width="1" />
                                                <path id="Pfad_1220" data-name="Pfad 1220"
                                                    d="M.411,16.418a10.657,10.657,0,0,1,21.314,0"
                                                    transform="translate(-0.411 7.582)" fill="none"
                                                    stroke-miterlimit="10" stroke-width="1" />
                                            </g>
                                        </g>
                                    </svg>
                                    @if (Auth::check())
                                        <svg xmlns="http://www.w3.org/2000/svg" id="iconLoggedIn" width="8.987"
                                            height="6.413" viewBox="0 0 8.987 6.413">
                                            <path data-name="iconcheck" d="M13.573,9,8.366,14.206,6,11.84"
                                                transform="translate(-5.293 -8.293)" fill="none" stroke="#db5743"
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                                        </svg>
                                    @endif
                                </a>
                            </li>
                            @if (Auth::check())
                                @if (auth()->user()?->role_id !== 3 && auth()->user()?->role_id !== 1)
                                    <li class="navbar-header-secondary__list__item">
                                        <span class="counter-cart counter-fav"
                                            id="">{{ auth()->user()?->favorites?->count() ?? '0' }}</span>

                                        <a href="/buyer/dashboard/favoriten">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20"
                                                viewBox="0 0 27.182 25">
                                                <path id="icon-hearth"
                                                    d="M29.182,10.3A6.687,6.687,0,0,0,22.363,3.75a6.83,6.83,0,0,0-6.272,3.975A6.831,6.831,0,0,0,9.817,3.75,6.687,6.687,0,0,0,3,10.3C3,20.8,16.091,27.75,16.091,27.75S29.182,20.8,29.182,10.3Z"
                                                    transform="translate(-2.5 -3.25)" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1" />
                                            </svg>
                                        </a>
                                    </li>
                                @endif
                            @endif

                            <li class="navbar-header-secondary__list__item">
                                <span class="counter-cart">{{ Cart::getContent()->count() }}</span>
                                <a href="{{ url('cart') }}" title="Zum Warenkorb">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 30.388 25">
                                        <g id="Gruppe_1100" data-name="Gruppe 1100" transform="translate(0 -0.052)">
                                            <g id="Gruppe_1653" data-name="Gruppe 1653"
                                                transform="translate(0 0.552)">
                                                <path id="Pfad_1221" data-name="Pfad 1221"
                                                    d="M12.677,3.907H29.625L25.9,12.439H7.76L4.776.218H0"
                                                    transform="translate(0 -0.218)" fill="none"
                                                    stroke-miterlimit="10" stroke-width="1" />
                                                <path id="Pfad_1222" data-name="Pfad 1222"
                                                    d="M6.516,8.111A1.974,1.974,0,1,1,4.541,6.136,1.975,1.975,0,0,1,6.516,8.111Z"
                                                    transform="translate(6.027 13.914)" fill="none"
                                                    stroke-miterlimit="10" stroke-width="1" />
                                                <path id="Pfad_1223" data-name="Pfad 1223"
                                                    d="M10.311,8.111A1.974,1.974,0,1,1,8.336,6.136,1.975,1.975,0,0,1,10.311,8.111Z"
                                                    transform="translate(16.007 13.914)" fill="none"
                                                    stroke-miterlimit="10" stroke-width="1" />
                                            </g>
                                        </g>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </span>
                    <!--Secondary Menu with Search for Desktop & Small Mobile devices-->
                </div>
            </div>
        </nav>
        <!--Navigation-->

        <!--Mobile Information-->
        <div class="container-before-header__mobile">
            <div class="container-xxl d-flex justify-content-center align-items-center">
                {{ setting('site.top_title') }}
            </div>
        </div>
        <!--Mobile Information-->

    </header>

    @php
        $hideAgeGate =
            ($type ?? null) === 'dashboard' ||
            request()->is(
                'buyer/dashboard',
                'buyer/dashboard/*',
                'seller/dashboard',
                'seller/dashboard/*',
                'dashboard',
                'orders',
                'invoice',
                'invoice/*',
            );
    @endphp
    @if (!$hideAgeGate)
        <div class="modal fade" id="ageGateModal" tabindex="-1" aria-labelledby="ageGateModalLabel"
            aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content py-5">

                    <div
                        class="modal-body pt-2 text-center d-flex align-items-center justify-content-center flex-column">
                        <img class="lazy mb-4 img-fluid" alt="Kinder- und Jugendschutz" style="max-width:110px;"
                            data-src="/assets/img/safe-zone/18plus.svg">
                        <h2 class="modal-title h4 mb-3" id="ageGateModalLabel">Willkommen im Duftparadies</h2>
                        <p class="mb-0">Diese Welt ist für neugierige Erwachsene gedacht. Bitte bestätige, dass du
                            mindestens 18 Jahre alt bist!</p>
                    </div>
                    <div
                        class="modal-footer d-flex align-items-center justify-content-center flex-wrap gap-2 border-0 pt-0">
                        <button type="button" class="btn btn-primary mb-2" id="ageGateAccept">Ich bin 18+</button>
                        <a href="{{ route('age.restricted') }}" class="btn btn-primary-outline mb-0">Ich bin noch
                            nicht 18</a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            (function() {
                var STORAGE_KEY = 'fraukruner_age_verified_at';
                var TTL_MS = 7 * 24 * 60 * 60 * 1000;

                function shouldSkip() {
                    try {
                        var raw = localStorage.getItem(STORAGE_KEY);
                        if (!raw) return false;
                        var ts = parseInt(raw, 10);
                        if (isNaN(ts)) return false;
                        return (Date.now() - ts) < TTL_MS;
                    } catch (e) {
                        return false;
                    }
                }

                window.addEventListener('load', function() {
                    if (typeof bootstrap === 'undefined' || !bootstrap.Modal) return;
                    var el = document.getElementById('ageGateModal');
                    if (!el) return;
                    if (shouldSkip()) return;
                    var modal = bootstrap.Modal.getOrCreateInstance(el, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    modal.show();
                    var acceptBtn = document.getElementById('ageGateAccept');
                    if (acceptBtn) {
                        acceptBtn.addEventListener('click', function() {
                            try {
                                localStorage.setItem(STORAGE_KEY, String(Date.now()));
                            } catch (e) {}
                            modal.hide();
                        });
                    }
                });
            })();
        </script>
    @endif

    <main>
        @yield('content')
    </main>

    <footer>
        @if (!@$type == 'dashboard')
            <div class="container-xxl">
                <p class="h6 text-center text-primary">Zahlungsmethoden</p>

                <div class="payment-methods">

                    @foreach ($paymentMethods as $data)
                        @if (!is_object($data))
                            @continue
                        @endif
                        <img data-src="{{ media_url(data_get($data, 'logo')) }}" class="lazy"
                            alt="Bezahlmethode Logo">
                    @endforeach
                    <span>VORKASSE</span>
                </div>

                <div class="text-center">
                    <img data-src="/assets/img/icons/ssl-secure-200.png" class="lazy m-3" height="80px"
                        width="80px" titel="Moderne Sicherheitsstandarts für die Datenübertragung"
                        alt="Unsere Webseite ist nach den neuesten Webstandarts Verschlüsselt.">
                </div>
            </div>
        @endif

        <div class="bg-secondary">
            <div class="container-xxl">
                <div class="footer-headline">
                    <img data-src="{{ asset('assets/img/icons/FxxK-Logo.svg') }}" height="36px" width="123px"
                        class="footer-logo lazy" alt="Frau Kruner Logo"> liebt es!
                </div>
                <div class="row">
                    <div class="col-12 col-md-4 social-media-footer">
                        <p>Folge mir auf</p>
                        <a href="https://www.instagram.com/fraukruner/" target="_blank"><img
                                data-src="{{ asset('assets/img/icons/Icon-instagram-primary.svg') }}"
                                class="lazy svg-up" alt="Instagram"></a>
                        <a href="https://www.tiktok.com/@fraukruner" target="_blank"><img
                                data-src="{{ asset('assets/img/icons/tiktor.svg') }}" class="lazy svg-up"
                                alt="TikTok"></a>
                    </div>

                    <div class="col-12 col-md-4 help-footer">
                        <p>Ich helfe gerne</p>
                        <a href="tel:{{ setting('site.phone') }}" class="d-flex-link-footer">
                            <img data-src="{{ asset('assets/img/icons/telefon-footer.svg') }}" class="lazy svg-up"
                                alt="Telefon">
                            <p>{{ setting('site.phone') }}</p>
                        </a>

                        <a href="mailto:hilfe@fraukruner.de" class="d-flex-link-footer">
                            <img data-src="{{ asset('assets/img/icons/mail-footer.svg') }}" class="lazy svg-up"
                                alt="Telefon">
                            <p>hilfe@fraukruner.de</p>
                        </a>
                    </div>

                    <div class="col-12 col-md-4">
                        <p>Frau Kruner hilft dir dabei, einfach vom heimischen Sofa aus, gutes Geld zu verdienen.</p>
                        <a href="/page/verkauferin-werden" class="btn btn-primary">Verkäuferin werden</a>
                    </div>
                </div>

                <div class="collapsing-container-footer">
                    <div class="row">

                        <div class="col-12 col-md-6 col-xl-3 collapsing-container-footer__col">
                            <button class="footer-collapse-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseFrauKruner" aria-expanded="false"
                                aria-controls="collapseFrauKruner">
                                Frau Kruner <span class="arrow"><span></span><span></span></span>
                            </button>

                            <div class="collapse collapse__footer" id="collapseFrauKruner">
                                <ul>
                                    <li><a href="{{ route('home') }}">Home</a></li>
                                    <li><a href="{{ route('shop') }}"> Shop</a></li>
                                    <li><a href="{{ route('about') }}">Über Frau Kruner</a></li>
                                    <li><a href="{{ route('shop', ['orderBy' => 'verkaeufe']) }}">Bestseller</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3 collapsing-container-footer__col">
                            <button class="footer-collapse-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseVerkauf" aria-expanded="false"
                                aria-controls="collapseVerkauf">
                                Verkauf <span class="arrow"><span></span><span></span></span>
                            </button>

                            <div class="collapse collapse__footer" id="collapseVerkauf">
                                <ul>
                                    <li><a href="/page/verkauferin-werden">Wie werde ich Verkäuferin?</a></li>
                                    <li><a href="/page/werbematerial">Werbematerial (Download)</a></li>
                                    <li><a href="/Neuigkeiten/verkaeuferinnen-gesucht-nebenjob-frauen">Verkäuferinnen
                                            gesucht</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3 collapsing-container-footer__col">
                            <button class="footer-collapse-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseHilfeundInfos" aria-expanded="false"
                                aria-controls="collapseHilfeundInfos">
                                Hilfe & Info <span class="arrow"><span></span><span></span></span>
                            </button>

                            <div class="collapse collapse__footer" id="collapseHilfeundInfos">
                                <ul>
                                    <li><a href="{{ route('faq') }}">FAQ - Hilfe</a></li>
                                    <li><a href="mailto:k@fraukruner.de" target="_blank">Werbung/ Media Kit
                                            anfordern</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3 collapsing-container-footer__col">
                            <button class="footer-collapse-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseRechtliches" aria-expanded="false"
                                aria-controls="collapseRechtliches">
                                Rechtliches <span class="arrow"><span></span><span></span></span>
                            </button>

                            <div class="collapse collapse__footer" id="collapseRechtliches">
                                <ul>
                                    @foreach ($menus as $menu)
                                        <li><a href="{{ $menu->url ? $menu->url : '#' }}"
                                                target="{{ $menu->target }}">{{ $menu->title }}</a></li>
                                    @endforeach




                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="bewertungModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <p class="h5 modal-title" id="bewertungModalLabel">Meine Bewertungen</p>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Schließen"></button>
                        </div>
                        <div class="modal-body">
                            <div class="container-review" id="review-container">
                            </div>
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Schließen</button>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Login Modal --}}
            <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel"
                aria-hidden="true">
                <div class="modal-dialog  modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <p class="h5 modal-title text-dark">Einloggen</p>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Schließen"></button>
                        </div>
                        <div class="modal-body">
                            <form class="login-form" id="ajaxLoginForm" method="POST"
                                action="{{ route('login') }}">
                                @csrf
                                <label for="e-mail">E-Mail</label>
                                <input type="email" placeholder="E-Mail-Adresse"
                                    class="@error('email') is-invalid @enderror" id="email" name="email"
                                    value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <label for="password">Password</label>
                                <input type="password" class="form-control @error('email') is-invalid @enderror"
                                    placeholder="Passwort" id="password" name="password" minlength="8" required>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <div class="form-group mt-3">
                                    <div class="cf-turnstile" data-sitekey="{{ env('TURNSTILE_SITE_KEY') }}">
                                    </div>
                                    @if ($errors->has('cf-turnstile-response'))
                                        <div class="text-danger">{{ $errors->first('cf-turnstile-response') }}
                                        </div>
                                    @endif
                                </div>
                                <input type="hidden" name="same" value="same">
                                <button type="submit" class="btn btn-primary rounded-pill w-100">Anmelden</button>
                            </form>
                            <a href="{{ route('password.request') }}" class="text-grey mb-5">Passwort
                                vergessen</a>

                            <div class="register-section mt-5">
                                <p class="h3 small text-secondary">Ich bin neu hier und möchte alle Vorteile nutzen:
                                </p>
                                <div class="d-flex flex-wrap ">
                                    <a href="{{ route('register') }}"
                                        class="btn btn-outline-secondary mx-0 my-2 rounded-pill">Jetzt registrieren</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Login modal --}}


                <script src="{{ asset('assets/js/jquery.min.js') }}"></script>

                {{-- Toastr  --}}
                <script src="{{ asset('assets/js/toastr.min.js') }}"></script>

                <script src="{{ asset('assets/js/app.min.js?v=9.5') }}" async></script>
                @stack('scripts')
                @if ($errors->any())
                    <script>
                        @foreach ($errors->all() as $error)
                            toastr.error(@json($error));
                        @endforeach
                    </script>
                @endif
                @if (session()->has('success'))
                    <script>
                        toastr.success("{{ session('success') }}")
                    </script>
                @endif
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
                <script>
                    function reviews(id) {
                        console.log(id)
                        $.ajax({
                            url: '/reviews',
                            method: 'GET',
                            data: {
                                vendor_id: id
                            },
                            success: function(response) {
                                $('#reviewModal').modal('show')
                                $('#review-container').html(response)
                            },

                        });
                    }

                    $(document).ready(function() {

                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });

                        $('#ajaxLoginForm').on('submit', function(e) {
                            e.preventDefault();
                            $('.invalid-feedback').text('');
                            $('.is-invalid').removeClass('is-invalid');


                            $.ajax({
                                url: $(this).attr('action'),
                                type: 'POST',
                                data: $(this).serialize(),
                                success: function(response) {
                                    if (response.errors) {
                                        toastr.error(response.error);

                                    } else {

                                        $('#loginModal').modal('hide');
                                        location.reload();

                                    }
                                },
                                error: function(xhr) {
                                    console.error(xhr.responseText);
                                    const response = JSON.parse(xhr.responseText);
                                    toastr.error(
                                        'Diese Anmeldeinformationen stimmen nicht mit unseren Unterlagen überein.'
                                    );
                                }
                            });
                        });
                    });
                </script>
                {{-- <script>
                    document.querySelectorAll('.favoriteForm').forEach(function(form) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            var formData = new FormData(this);
                            var url = this.action;
                            var button = this.querySelector('.favoriteButton');

                            fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: formData
                                })
                                .then(response => {
                                    const contentType = response.headers.get("content-type");
                                    if (contentType && contentType.includes("application/json")) {
                                        return response.json();
                                    }
                                    if (response.redirected) {
                                        window.location.href = response.url;
                                    }
                                    throw new Error("Unexpected response");
                                })
                                .then(data => {
                                    if (data.message === "Unauthenticated.") {
                                        window.location.href = '/login';
                                    } else if (data.success) {

                                        button.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z"/>
                            </svg>`;
                                        button.setAttribute('disabled', 'true');
                                        const cartCounters = document.querySelectorAll('.counter-fav');
                                        cartCounters.forEach(cartCounter => {
                                            let currentCount = parseInt(cartCounter.innerText) || 0;
                                            cartCounter.innerText = currentCount + 1;

                                        });
                                    } else if (data.error) {
                                        alert(data.message);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                });
                        });
                    });
                </script> --}}

                <script>
                    document.querySelectorAll('.favoriteForm').forEach(function(form) {
                        form.addEventListener('submit', function(e) {

                            e.preventDefault();
                            var formData = new FormData(this);
                            var url = this.action;
                            var button = this.querySelector('.favoriteButton');
                            var id = this.querySelector('.favoriteId');
                            const cartCounters = document.querySelectorAll('.counter-fav');

                            fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: formData
                                })
                                .then(response => {
                                    const contentType = response.headers.get("content-type");
                                    if (contentType && contentType.includes("application/json")) {
                                        return response.json();
                                    }
                                    if (response.redirected) {
                                        window.location.href = response.url;
                                    }
                                    throw new Error("Unexpected response");
                                })
                                .then(data => {
                                    if (data.success) {
                                        if (data.delete) {

                                            button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                                    viewBox="0 0 24 24" stroke-width="1.5"
                                                                                    stroke="currentColor" class="size-6">
                                                                                    <path stroke-linecap="round"
                                                                                        stroke-linejoin="round"
                                                                                        d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                                                                </svg>`;
                                            id.value = "";

                                            cartCounters.forEach(cartCounter => {
                                                let currentCount = parseInt(cartCounter.innerText) || 0;
                                                cartCounter.innerText = currentCount - 1;

                                            });
                                            toastr.success('Lieblingsartikel erfolgreich entfernt');
                                        } else {

                                            button.innerHTML = ` <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                                fill="currentColor" class="size-6">
                                                                                <path
                                                                                    d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
                                                                            </svg>`;
                                            id.value = data.favorite?.id;
                                            cartCounters.forEach(cartCounter => {
                                                let currentCount = parseInt(cartCounter.innerText) || 0;
                                                cartCounter.innerText = currentCount + 1;

                                            });
                                            toastr.success(data.message);
                                        }

                                    } else if (data.error) {

                                        toastr.error(data.message);
                                    }
                                })
                                .catch(error => {
                                    toastr.error(error)
                                });
                        });
                    });
                </script>

                <script>
                    document.querySelectorAll('.deleteFavoriteForm').forEach(function(form) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();


                            const button = this.querySelector('button[type="submit"]');
                            const deleteFormfavCreate = this.querySelector('.deleteFormfavCreate');
                            const favoriteId = form.querySelector('input[name="favorite_id"]').value;

                            const url = `{{ route('buyer.favorite.delete', ['favorite' => '__FAVORITE__']) }}`.replace(
                                '__FAVORITE__', favoriteId);


                            fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    },
                                    body: new URLSearchParams(new FormData(this))
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Network response was not ok');
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    if (data.success) {
                                        if (data.create == true) {

                                            button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                                stroke="currentColor" class="size-6">
                                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                                                            </svg>`;
                                            deleteFormfavCreate.value = true;



                                        } else {

                                            form.querySelector('input[name="favorite_id"]').value = data.favorite
                                                ?.id
                                            button.innerHTML = ` <svg xmlns="http://www.w3.org/2000/svg"
                                                                                viewBox="0 0 24 24" fill="currentColor"
                                                                                class="size-6">
                                                                                <path
                                                                                    d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
                                                                            </svg>`;
                                            deleteFormfavCreate.value = '';
                                        }
                                        toastr.success('Lieblingsartikel erfolgreich entfernt');
                                        // this.remove(); 
                                    } else if (data.error) {
                                        toastr.error(data.message);
                                    }
                                })
                                .catch(error => {
                                    toastr.error(
                                        'Fehler beim Entfernen des Lieblingsartikels'); // General error message
                                    console.error('Error:', error);
                                });
                        });
                    });
                </script>



                @yield('js')
                @stack('js')
                @stack('scripts')


    </footer>
</body>

</html>
