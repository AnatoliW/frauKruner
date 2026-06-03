<x-dashboard type='seller' title="Nutzerdaten" :bread="[
    'Startseite' => route('home'),
    'Profil' => route('seller.dashboard'),
    'Nutzerdaten' => route('seller.user_data'),
]">
    <x-user.info :data="$profile" />

</x-dashboard>
