<x-dashboard type='seller' title="Adresse" :bread="
[
    'Startseite' => route('home'),
    'Profil' => route('seller.dashboard'),
    'Adresse' => route('seller.address'),
]
">

   <x-user.address :data="$address" />

</x-dashboard>
