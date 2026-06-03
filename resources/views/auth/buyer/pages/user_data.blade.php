<x-dashboard type='buyer' title="Nutzerdaten" :bread="[
    'Startseite' => route('buyer.dashboard'),
    'Profil' => route('buyer.dashboard'),
    'Nutzerdaten' => route('buyer.user.data'),
]">
<x-user.info :data="$profile" />


</x-dashboard>
