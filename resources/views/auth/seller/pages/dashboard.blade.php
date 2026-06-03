    <x-dashboard type='seller' title="" :bread="[
        'Startseite' => route('home'),
        'Profil' => route('seller.dashboard'),
    ]">
        @if (Auth()->user()->status == false || Auth()->user()->visibiliti_status == false)
            <h5 class="small text-primary">
                <details data-popover="up">
                    <summary>?</summary>
                    <div class="popoverBody">
                        Mögliche Gründe dafür: <br>
                        1. Dein Verkäufer-Profil noch nicht von dem Admin verifiziert wurde (dies könnte in der Regel
                        1-3 Tage dauern). <br>
                        2. Du hast dein Profil in den Profileinstellungen deaktiviert.
                    </div>
                </details>Deine Produkte werden (noch) nicht gelistet
            </h5>
        @endif
     
        @if (!auth()->user()->vat)
            @php
                $now = \Carbon\Carbon::parse('2024-10-18');
                $targetDate = $now->copy()->addDays(30);
                $daysLeft = $now->diffInDays($targetDate);
            @endphp

            <div class="alert bg-primary text-white">
                <p class="mb-0">
                Bitte trage deine <a href="{{ route('seller.user_data') }}"
                class="text-secondary fw-bold">Steuernummer</a> ein, um verkaufen zu können. Du besitzt noch keine Steuernummer? Diese kannst du, auch online, bei deinem Finanzamt beantragen.
                </p>
            </div>
        @endif
        <div class="profile-content__profile-meta">
            @if (Auth()->user()->profile)
                <img src="{{ Auth()->user()->profile->profile_img ? media_url(auth()->user()->profile->profile_img) : asset('assets/img/user.png') }}"
                    alt="{{ Auth()->user()->name }}" height="50px">
            @else
                <img src="{{ asset('assets/img/user.png') }}">
            @endif

            <h3>Hallo, {{ auth()->user()->username ? auth()->user()->username : auth()->user()->name }}</h3>
        </div>
        <p class="profile-content__message mb-5">
            Willkommen in deinem Benutzerkonto! <br>
            Hier kannst du deine Bestellungen, Rücksendungen und Kontoinformationen direkt verwalten.
        </p>

        <b>Deine 5 Gebote als Verkäufer*in: </b>
        <ol class="mt-2">
            <li>Ware, die Düfte enthält, wird ausschließlich vakuumiert verschickt.</li>
            <li>Es werden keine personenbezogenen Daten wie Mailadresses, SocialMedia Accounts, PayPal, Adressen etc. weitergegeben oder öffentlich gemacht.</li>
            <li>Nehme Kontakt mit dem Support unter hilfe@fraukruner.de auf, wenn du absehen kannst, nicht innerhalb von 7 Tagen versenden zu können.</li>
            <li>Die Haftung liegt bei dir, wenn du ohne Sendungsnummer verschickst.</li>
            <li>Prüfe dein Mailfach regelmäßig.</li>
        </ol>





    </x-dashboard>

