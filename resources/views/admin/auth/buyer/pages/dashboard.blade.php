<x-dashboard type='buyer' title="Hallo,  {{ auth()->user()->username ? auth()->user()->username : auth()->user()->name }}" :bread="
[
    'Startseite' => route('home'),
    'Profil' => route('buyer.dashboard'),

]
">
<div class="d-flex flex-column align-items-start justify-content-start">
<img src="{{Auth()->user()->profile ? media_url(Auth()->user()->profile->profile_img) :asset('assets/img/user.png') }}" alt="{{ Auth()->user()->name }}" height="50px" style="margin-right:10px;">

    <p class="profile-content__message mb-5">
        Willkommen in deinem Benutzerkonto! <br>
        Hier kannst du deine Bestellungen, Nachrichten und Kontoinformationen direkt verwalten.
    </p>


</div>


</x-dashboard>

